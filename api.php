<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$host = "localhost";
$db_name = "projeto_valistoque";
$username = "root";
$password = "12345678";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => "Erro de conexão: " . $e->getMessage()]);
    exit();
}

$dados = json_decode(file_get_contents("php://input"));

if (isset($dados->prateleira) && isset($dados->peso)) {
    $query = "INSERT INTO leituras (prateleira, peso) VALUES (:prateleira, :peso)";
    $stmt = $conn->prepare($query);

    $prateleira = intval($dados->prateleira);
    $peso = floatval($dados->peso); // Preserva o valor decimal exato em gramas (ex: 86.48)

    $stmt->bindParam(":prateleira", $prateleira);
    $stmt->bindParam(":peso", $peso);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["status" => "sucesso", "mensagem" => "Dados salvos com sucesso"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => "Falha ao salvar no banco de dados"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "JSON deve conter 'prateleira' e 'peso'"]);
}
?>