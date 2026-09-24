<?php

/**
 * Endpoint para receber uma leitura de peso de uma prateleira.
 *
 * Entrada esperada via POST (JSON):
 * {
 *     "prateleira": 1,
 *     "peso": 400
 * }
 *
 * O peso recebido deve estar em gramas.
 *
 * A API:
 * 1. Localiza a prateleira;
 * 2. Verifica qual lote está vinculado a ela;
 * 3. Busca o peso unitário desse lote;
 * 4. Calcula a quantidade de produtos;
 * 5. Atualiza o estado atual da prateleira;
 * 6. Registra a leitura no histórico.
 */

// Como esta página é uma API, a resposta será sempre JSON.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';

/**
 * Função auxiliar para encerrar a requisição com uma resposta JSON.
 * Mantemos as respostas simples para facilitar os testes com o ESP futuramente.
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

// A API foi criada especificamente para receber POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método não permitido. Use POST.', [], 405);
}

// Lê o corpo bruto da requisição e tenta interpretá-lo como JSON.
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
    $pdo->beginTransaction();

    // -------------------------------------------------------------------------
    // 2. Busca a prateleira e bloqueia seu registro durante esta operação.
    //
    // O FOR UPDATE evita que duas leituras simultâneas alterem a mesma
    // prateleira ao mesmo tempo enquanto estamos processando a leitura.
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 3. Uma prateleira pode existir vazia.
    //
    // Nesse caso podemos até receber e validar uma leitura, mas não temos um
    // produto/lote para descobrir o peso unitário e calcular a quantidade.
    // -------------------------------------------------------------------------
    if ($prateleira['id_estoque'] === null) {
        $pdo->rollBack();
        responder(false, 'A prateleira ainda não possui um produto/lote vinculado.', [], 409);
    }

    $id_estoque = (int) $prateleira['id_estoque'];

    // -------------------------------------------------------------------------
    // 4. Busca o peso unitário do lote vinculado.
    //
    // Não verificamos se o estoque está ativo aqui. Uma prateleira pode
    // continuar apontando para um lote que foi inativado no estoque central.
    // -------------------------------------------------------------------------
    $stmt = $pdo->prepare(
        "SELECT id_estoque, nome_produto, lote, peso_un
         FROM estoque
         WHERE id_estoque = :id_estoque"
    );

    $stmt->execute([':id_estoque' => $id_estoque]);
    $estoque = $stmt->fetch();

    if (!$estoque) {
        $pdo->rollBack();
        responder(false, 'O lote vinculado à prateleira não foi encontrado no estoque.', [], 409);
    }

    // IMPORTANTE:
    // A partir da decisão tomada para a API, peso_un será armazenado em GRAMAS.
    // Exemplo: um produto de 250 g deve possuir peso_un = 250.00.
    $peso_unitario = (float) $estoque['peso_un'];

    if ($peso_unitario <= 0) {
        $pdo->rollBack();
        responder(false, 'O peso unitário do produto é inválido ou não foi cadastrado.', [], 409);
    }

    // -------------------------------------------------------------------------
    // 5. Calcula a quantidade de unidades presentes na prateleira.
    //
    // Como a quantidade precisa ser inteira, arredondamos o resultado para o
    // inteiro mais próximo. A tolerância desse arredondamento será avaliada
    // depois dos testes reais com a balança.
    // -------------------------------------------------------------------------
    $quantidade = (int) round($peso / $peso_unitario);

    // -------------------------------------------------------------------------
    // 6. Atualiza o estado atual da prateleira.
    //
    // peso_prat guarda a leitura real recebida da balança.
    // qte guarda a quantidade calculada a partir dessa leitura.
    // ultima_leitura registra somente leituras vindas do sensor.
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 7. Guarda a leitura no histórico.
    //
    // O id_estoque é salvo junto porque representa o lote que estava vinculado
    // à prateleira NO MOMENTO da leitura. Assim, se no futuro a prateleira
    // receber outro lote, o histórico antigo continuará identificável.
    // -------------------------------------------------------------------------
    $stmt = $pdo->prepare(
        "INSERT INTO leituras_prateleira
            (id_prat, id_estoque, peso, quantidade_calculada)
         VALUES
            (:id_prat, :id_estoque, :peso, :qte)"
    );

    $stmt->execute([
        ':id_prat' => $id_prat,
        ':id_estoque' => $id_estoque,
        ':peso' => $peso,
        ':qte' => $quantidade
    ]);

    // Só confirmamos a operação depois que a atualização da prateleira e o
    // registro histórico foram concluídos com sucesso.
    $pdo->commit();

    responder(
        true,
        'Leitura processada com sucesso.',
        [
            'prateleira' => $id_prat,
            'id_estoque' => $id_estoque,
            'produto' => $estoque['nome_produto'],
            'lote' => $estoque['lote'],
            'peso' => $peso,
            'peso_unitario' => $peso_unitario,
            'quantidade' => $quantidade
        ]
    );

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Durante o desenvolvimento, deixamos a mensagem do banco disponível para
    // facilitar o diagnóstico. Antes da versão final, podemos simplificar a
    // mensagem devolvida ao dispositivo.
    responder(
        false,
        'Erro ao processar a leitura no banco de dados.',
        ['erro' => $e->getMessage()],
        500
    );
}
