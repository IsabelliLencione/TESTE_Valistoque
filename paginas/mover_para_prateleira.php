<?php
require_once __DIR__ . '/../php/config.php';

/*
 * Move unidades do estoque central para uma prateleira existente.
 *
 * Regras principais:
 * - a quantidade movimentada é sempre em unidades;
 * - a prateleira nunca é criada aqui, apenas selecionada;
 * - prateleira vazia recebe o lote;
 * - mesmo lote: soma a quantidade;
 * - lote diferente: exige confirmação e, se confirmado, substitui o lote;
 * - a transferência manual não altera ultima_leitura, pois esse campo
 *   representa somente uma leitura real do sensor.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: estoque.php');
    exit;
}

$id_estoque = filter_input(INPUT_POST, 'id_estoque', FILTER_VALIDATE_INT);
$id_prat = filter_input(INPUT_POST, 'id_prat', FILTER_VALIDATE_INT);
$quantidade = filter_input(INPUT_POST, 'quantidade_mover', FILTER_VALIDATE_INT);
$confirmar_substituicao = isset($_POST['confirmar_substituicao']) && $_POST['confirmar_substituicao'] === '1';

if (!$id_estoque || !$id_prat || !$quantidade || $quantidade <= 0) {
    die('Dados da transferência inválidos.');
}

try {
    $pdo->beginTransaction();

    // Bloqueia o lote durante a operação para evitar movimentações simultâneas conflitantes.
    $stmt = $pdo->prepare(
        "SELECT * FROM estoque WHERE id_estoque = :id AND ativo = 1 FOR UPDATE"
    );
    $stmt->bindValue(':id', $id_estoque, PDO::PARAM_INT);
    $stmt->execute();
    $estoque = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estoque) {
        throw new Exception('Lote não encontrado ou está desativado.');
    }

    if ((int)$estoque['total_itens'] < $quantidade) {
        throw new Exception(
            "Quantidade solicitada ({$quantidade}) é maior que o saldo disponível ({$estoque['total_itens']})."
        );
    }

    $pesoUnidade = (float)$estoque['peso_un'];
    if ($pesoUnidade <= 0) {
        throw new Exception('O peso unitário deste lote é inválido.');
    }

    // A prateleira precisa existir. Também a bloqueamos durante a transferência.
    $stmt = $pdo->prepare(
        "SELECT * FROM prateleiras WHERE id_prat = :id_prat FOR UPDATE"
    );
    $stmt->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
    $stmt->execute();
    $prateleira = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prateleira) {
        throw new Exception('Prateleira não encontrada.');
    }

    $loteAtual = $prateleira['id_estoque'];
    $estoqueAtual = null;

    // Se já existe outro lote na prateleira, buscamos seus dados para
    // mostrar claramente ao usuário o que será substituído.
    if ($loteAtual !== null && (int)$loteAtual !== (int)$id_estoque) {
        $stmt = $pdo->prepare("SELECT lote, nome_produto FROM estoque WHERE id_estoque = :id");
        $stmt->bindValue(':id', $loteAtual, PDO::PARAM_INT);
        $stmt->execute();
        $estoqueAtual = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Se já existe outro lote na prateleira, precisamos confirmar a substituição.
    if ($loteAtual !== null && (int)$loteAtual !== (int)$id_estoque && !$confirmar_substituicao) {
        $pdo->rollBack();
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmar substituição</title>
            <link rel="stylesheet" href="../css/style.css">
        </head>
        <body>
            <main style="max-width:600px; margin:60px auto; padding:24px;">
                <div style="background:#fff; padding:28px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,.12);">
                    <h2>Prateleira já ocupada</h2>
                    <p>
                        A prateleira <strong><?= (int)$id_prat ?></strong> já está vinculada a outro lote.
                    </p>
                    <p>
                        A prateleira está atualmente vinculada ao produto
                        <strong><?= htmlspecialchars($estoqueAtual['nome_produto'] ?? 'Produto não encontrado') ?></strong>,
                        lote <strong><?= htmlspecialchars($estoqueAtual['lote'] ?? (string)$loteAtual) ?></strong>.
                    </p>
                    <p>
                        Se continuar, esse lote será substituído pelo produto
                        <strong><?= htmlspecialchars($estoque['nome_produto']) ?></strong>,
                        lote <strong><?= htmlspecialchars($estoque['lote']) ?></strong>.
                    </p>
                    <p>O lote anterior não será apagado do banco.</p>

                    <form method="POST" style="display:flex; gap:10px; margin-top:24px;">
                        <input type="hidden" name="id_estoque" value="<?= (int)$id_estoque ?>">
                        <input type="hidden" name="id_prat" value="<?= (int)$id_prat ?>">
                        <input type="hidden" name="quantidade_mover" value="<?= (int)$quantidade ?>">
                        <button type="submit" name="confirmar_substituicao" value="1"
                                style="background:#2ecc71; color:#fff; border:0; padding:12px 18px; border-radius:8px; cursor:pointer;">
                            Continuar
                        </button>
                        <a href="estoque.php"
                           style="background:#95a5a6; color:#fff; text-decoration:none; padding:12px 18px; border-radius:8px;">
                            Cancelar
                        </a>
                    </form>
                </div>
            </main>
        </body>
        </html>
        <?php
        exit;
    }

    $pesoCalculado = $pesoUnidade * $quantidade;

    // Retira as unidades do estoque central.
    $stmt = $pdo->prepare(
        "UPDATE estoque SET total_itens = total_itens - :quantidade WHERE id_estoque = :id"
    );
    $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindValue(':id', $id_estoque, PDO::PARAM_INT);
    $stmt->execute();

    if ($loteAtual === null) {
        // Prateleira vazia: cria a associação com o lote e a quantidade transferida.
        $stmt = $pdo->prepare(
            "UPDATE prateleiras
             SET id_estoque = :id_estoque,
                 peso_prat = :peso,
                 qte = :qte,
                 ultima_leitura = NULL
             WHERE id_prat = :id_prat"
        );
        $stmt->bindValue(':id_estoque', $id_estoque, PDO::PARAM_INT);
        $stmt->bindValue(':peso', $pesoCalculado);
        $stmt->bindValue(':qte', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
        $stmt->execute();
    } elseif ((int)$loteAtual === (int)$id_estoque) {
        // Mesmo lote: soma as unidades e recalcula o peso inicial da prateleira.
        $novaQuantidade = (int)$prateleira['qte'] + $quantidade;
        $novoPeso = $pesoUnidade * $novaQuantidade;

        $stmt = $pdo->prepare(
            "UPDATE prateleiras
             SET qte = :qte,
                 peso_prat = :peso
             WHERE id_prat = :id_prat"
        );
        $stmt->bindValue(':qte', $novaQuantidade, PDO::PARAM_INT);
        $stmt->bindValue(':peso', $novoPeso);
        $stmt->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Lote diferente com confirmação: substitui a associação atual.
        $stmt = $pdo->prepare(
            "UPDATE prateleiras
             SET id_estoque = :id_estoque,
                 peso_prat = :peso,
                 qte = :qte,
                 ultima_leitura = NULL
             WHERE id_prat = :id_prat"
        );
        $stmt->bindValue(':id_estoque', $id_estoque, PDO::PARAM_INT);
        $stmt->bindValue(':peso', $pesoCalculado);
        $stmt->bindValue(':qte', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
        $stmt->execute();
    }

    $pdo->commit();

    header('Location: estoque.php?sucesso=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Erro ao mover produto para a prateleira: ' . htmlspecialchars($e->getMessage()));
}
