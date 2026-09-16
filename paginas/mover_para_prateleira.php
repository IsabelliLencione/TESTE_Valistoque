<?php
// Habilita exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../php/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_estoque = filter_input(INPUT_POST, 'id_estoque', FILTER_VALIDATE_INT);
    $numero_prat = filter_input(INPUT_POST, 'numero_prat', FILTER_VALIDATE_INT);
    $caixas_mover = filter_input(INPUT_POST, 'caixas_mover', FILTER_VALIDATE_INT);

    if (!$id_estoque || !$numero_prat || !$caixas_mover || $caixas_mover <= 0) {
        die("Dados do formulário inválidos.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Busca o produto e seu peso unitário no estoque central
        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE id_estoque = :id FOR UPDATE");
        $stmt->bindValue(':id', $id_estoque, PDO::PARAM_INT);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            $pdo->rollBack();
            die("Produto não encontrado no estoque central.");
        }

        if ((int)$produto['total_itens'] < $caixas_mover) {
            $pdo->rollBack();
            die("Quantidade solicitada ({$caixas_mover}) é maior do que o saldo em estoque ({$produto['total_itens']}).");
        }

        // 2. Calcula o peso correspondente (se existir coluna peso_un, usa ela; senão usa 0)
        $pesoUnidade = isset($produto['peso_un']) ? (float)$produto['peso_un'] : 0.0;
        $pesoCalculado = $pesoUnidade * $caixas_mover;

        // 3. Subtrai as caixas do estoque central
        $stmtUpdate = $pdo->prepare("UPDATE estoque SET total_itens = total_itens - :qtd WHERE id_estoque = :id");
        $stmtUpdate->bindValue(':qtd', $caixas_mover, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':id', $id_estoque, PDO::PARAM_INT);
        $stmtUpdate->execute();

        // 4. Verifica se a prateleira já possui este produto cadastrado
        $stmtCheck = $pdo->prepare("SELECT * FROM prateleiras WHERE id_estoque = :id_est AND numero_prat = :num_prat");
        $stmtCheck->bindValue(':id_est', $id_estoque, PDO::PARAM_INT);
        $stmtCheck->bindValue(':num_prat', $numero_prat, PDO::PARAM_INT);
        $stmtCheck->execute();
        $prateleiraExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($prateleiraExistente) {
            // Atualiza a quantidade e o peso na prateleira existente
            $stmtPrat = $pdo->prepare("UPDATE prateleiras 
                                       SET quantidade_atual = quantidade_atual + :qtd, 
                                           peso_prat = peso_prat + :peso 
                                       WHERE id_prat = :id_prat");
            $stmtPrat->bindValue(':qtd', $caixas_mover, PDO::PARAM_INT);
            $stmtPrat->bindValue(':peso', $pesoCalculado);
            $stmtPrat->bindValue(':id_prat', $prateleiraExistente['id_prat'], PDO::PARAM_INT);
            $stmtPrat->execute();
        } else {
            // Insere preenchendo a coluna peso_prat obrigatória
            $stmtPrat = $pdo->prepare("INSERT INTO prateleiras (id_estoque, numero_prat, quantidade_atual, peso_prat) 
                                       VALUES (:id_est, :num_prat, :qtd, :peso)");
            $stmtPrat->bindValue(':id_est', $id_estoque, PDO::PARAM_INT);
            $stmtPrat->bindValue(':num_prat', $numero_prat, PDO::PARAM_INT);
            $stmtPrat->bindValue(':qtd', $caixas_mover, PDO::PARAM_INT);
            $stmtPrat->bindValue(':peso', $pesoCalculado);
            $stmtPrat->execute();
        }

        $pdo->commit();

        // Redireciona para a tela de prateleiras para ver o item movido
        header("Location: prateleira.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro no Banco de Dados ao mover produto: " . $e->getMessage());
    }
} else {
    header("Location: estoque.php");
    exit;
}