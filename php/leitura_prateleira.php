<?php

/**
 * API para receber uma leitura de peso de uma prateleira.
 *
 * Entrada esperada via POST (JSON):
 * {
 *     "prateleira": 1,
 *     "peso": 400
 * }
 *
 * O peso recebido deve estar em gramas.
 *
 * Regras principais:
 * - Toda leitura recebida de uma prateleira existente é registrada no histórico.
 * - Toda leitura também atualiza o peso atual e a data da última leitura da
 *   prateleira.
 * - Se a prateleira possuir um lote vinculado, o sistema calcula a quantidade
 *   de unidades e atualiza qte.
 * - Se a prateleira estiver vazia, sem lote vinculado, a leitura é registrada
 *   normalmente, mas não é possível calcular a quantidade de produtos.
 */

// Esta página funciona como uma API, portanto sua resposta será sempre JSON.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

/**
 * Encerra a requisição retornando uma resposta JSON.
 */
function responder($sucesso, $mensagem, $dados = [], $statusHttp = 200)
{
    http_response_code($statusHttp);

    echo json_encode(
        array_merge(
            [
                'sucesso' => $sucesso,
                'mensagem' => $mensagem
            ],
            $dados
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

// A API aceita somente requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método não permitido. Use POST.', [], 405);
}

// Lê o corpo da requisição e converte o JSON recebido para um array PHP.
$corpo = file_get_contents('php://input');
$dados = json_decode($corpo, true);

if (!is_array($dados)) {
    responder(false, 'O corpo da requisição deve ser um objeto JSON válido.', [], 400);
}

// -----------------------------------------------------------------------------
// 1. Validação dos dados recebidos
// -----------------------------------------------------------------------------

if (!isset($dados['prateleira']) || !filter_var($dados['prateleira'], FILTER_VALIDATE_INT)) {
    responder(false, 'O campo prateleira é obrigatório e deve ser um número inteiro.', [], 400);
}

if (!isset($dados['peso']) || !is_numeric($dados['peso'])) {
    responder(false, 'O campo peso é obrigatório e deve ser numérico.', [], 400);
}

$id_prat = (int) $dados['prateleira'];
$peso = (float) $dados['peso'];

if ($id_prat <= 0) {
    responder(false, 'O número da prateleira deve ser maior que zero.', [], 400);
}

if ($peso < 0) {
    responder(false, 'O peso não pode ser negativo.', [], 400);
}

try {
    // A atualização da prateleira e o registro histórico fazem parte da mesma
    // operação. Assim, se uma das duas etapas falhar, nenhuma delas é salva.
    $pdo->beginTransaction();

    // -------------------------------------------------------------------------
    // 2. Busca a prateleira
    // -------------------------------------------------------------------------
    // O FOR UPDATE impede que outra leitura altere a mesma prateleira enquanto
    // esta requisição ainda estiver sendo processada.
    $stmt = $pdo->prepare(
        "SELECT id_prat, id_estoque, peso_prat, qte
         FROM prateleiras
         WHERE id_prat = :id_prat
         FOR UPDATE"
    );

    $stmt->execute([':id_prat' => $id_prat]);
    $prateleira = $stmt->fetch();

    if (!$prateleira) {
        $pdo->rollBack();
        responder(false, 'Prateleira não encontrada.', [], 404);
    }

    $id_estoque = $prateleira['id_estoque'] !== null
        ? (int) $prateleira['id_estoque']
        : null;

    // Variáveis que serão preenchidas somente quando houver lote vinculado.
    $produto = null;
    $lote = null;
    $peso_unitario = null;
    $quantidade = null;

    // -------------------------------------------------------------------------
    // 3. Se houver lote, busca o peso unitário e calcula a quantidade
    // -------------------------------------------------------------------------
    if ($id_estoque !== null) {

        $stmt = $pdo->prepare(
            "SELECT id_estoque, nome_produto, lote, peso_un
             FROM estoque
             WHERE id_estoque = :id_estoque"
        );

        $stmt->execute([':id_estoque' => $id_estoque]);
        $estoque = $stmt->fetch();

        if (!$estoque) {
            $pdo->rollBack();
            responder(
                false,
                'O lote vinculado à prateleira não foi encontrado no estoque.',
                [],
                409
            );
        }

        // peso_un é armazenado em gramas.
        $peso_unitario = (float) $estoque['peso_un'];

        if ($peso_unitario <= 0) {
            $pdo->rollBack();
            responder(
                false,
                'O peso unitário do produto é inválido ou não foi cadastrado.',
                [],
                409
            );
        }

        $produto = $estoque['nome_produto'];
        $lote = $estoque['lote'];

        // Por enquanto usamos arredondamento simples. A tolerância real será
        // definida depois dos testes físicos com o sensor.
        $quantidade = (int) round($peso / $peso_unitario);
    }

    // -------------------------------------------------------------------------
    // 4. Atualiza o estado atual da prateleira
    // -------------------------------------------------------------------------
    // A leitura do sensor sempre atualiza peso_prat e ultima_leitura.
    // qte só é atualizada quando existe um lote associado.
    if ($id_estoque !== null) {
        $stmt = $pdo->prepare(
            "UPDATE prateleiras
             SET peso_prat = :peso,
                 qte = :qte,
                 ultima_leitura = CURRENT_TIMESTAMP
             WHERE id_prat = :id_prat"
        );

        $stmt->execute([
            ':peso' => $peso,
            ':qte' => $quantidade,
            ':id_prat' => $id_prat
        ]);
    } else {
        // Prateleira sem lote: guardamos a leitura física, mas não tentamos
        // transformá-la em quantidade de produtos.
        $stmt = $pdo->prepare(
            "UPDATE prateleiras
             SET peso_prat = :peso,
                 ultima_leitura = CURRENT_TIMESTAMP
             WHERE id_prat = :id_prat"
        );

        $stmt->execute([
            ':peso' => $peso,
            ':id_prat' => $id_prat
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. Registra a leitura no histórico
    // -------------------------------------------------------------------------
    // id_estoque pode ser NULL. Isso representa uma leitura feita enquanto a
    // prateleira estava sem lote associado.
    //
    // quantidade_calculada também pode ser NULL quando não havia lote para
    // determinar o peso unitário.
    $stmt = $pdo->prepare(
        "INSERT INTO leituras_prateleira
            (id_prat, id_estoque, peso, quantidade_calculada)
         VALUES
            (:id_prat, :id_estoque, :peso, :quantidade_calculada)"
    );

    $stmt->execute([
        ':id_prat' => $id_prat,
        ':id_estoque' => $id_estoque,
        ':peso' => $peso,
        ':quantidade_calculada' => $quantidade
    ]);

    // Só confirmamos depois de atualizar a prateleira e registrar o histórico.
    $pdo->commit();

    // -------------------------------------------------------------------------
    // 6. Resposta da API
    // -------------------------------------------------------------------------
    if ($id_estoque === null) {
        responder(
            true,
            'Leitura registrada. A prateleira não possui lote vinculado, portanto a quantidade não foi calculada.',
            [
                'prateleira' => $id_prat,
                'id_estoque' => null,
                'peso' => $peso,
                'quantidade' => null
            ]
        );
    }

    responder(
        true,
        'Leitura processada com sucesso.',
        [
            'prateleira' => $id_prat,
            'id_estoque' => $id_estoque,
            'produto' => $produto,
            'lote' => $lote,
            'peso' => $peso,
            'peso_unitario' => $peso_unitario,
            'quantidade' => $quantidade
        ]
    );

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Durante o desenvolvimento, mostramos o erro do banco para facilitar os
    // testes. Em uma versão final, podemos devolver uma mensagem mais simples.
    responder(
        false,
        'Erro ao processar a leitura no banco de dados.',
        ['erro' => $e->getMessage()],
        500
    );
}
