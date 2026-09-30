<?php

header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "valistoque_testes";

$conexao = new mysqli(
    $host,
    $usuario,
    $senha,
    $banco
);

if ($conexao->connect_error) {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao conectar ao banco de dados"
    ]);

    exit;
}

$conexao->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Método inválido"
    ]);

    exit;
}

$peso = isset($_POST["peso"])
    ? floatval($_POST["peso"])
    : 0;

$prateleira = isset($_POST["prateleira"])
    ? intval($_POST["prateleira"])
    : 0;

if ($peso < 0) {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Peso inválido"
    ]);

    exit;
}

if ($prateleira <= 0) {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Prateleira inválida"
    ]);

    exit;
}

/*
    O Arduino envia em KG.

    Exemplo:

    2.350 kg

    será salvo como:

    2350 gramas
*/

$peso_gramas = round($peso * 1000);

$sql = "
    UPDATE prateleiras
    SET peso_prat = ?
    WHERE numero_prat = ?
";

$stmt = $conexao->prepare($sql);

if (!$stmt) {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao preparar consulta"
    ]);

    exit;
}

$stmt->bind_param(
    "ii",
    $peso_gramas,
    $prateleira
);

if ($stmt->execute()) {

    if ($stmt->affected_rows >= 0) {

        echo json_encode([
            "sucesso" => true,
            "prateleira" => $prateleira,
            "peso_kg" => $peso,
            "peso_gramas" => $peso_gramas
        ]);

    } else {

        echo json_encode([
            "sucesso" => false,
            "erro" => "Prateleira não encontrada"
        ]);
    }

} else {

    echo json_encode([
        "sucesso" => false,
        "erro" => "Erro ao atualizar peso"
    ]);
}

$stmt->close();
$conexao->close();

?>