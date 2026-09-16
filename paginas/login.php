<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../php/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Método não permitido."
    ]);

    exit;
}

$email = $_POST["email"] ?? "";
$senha = $_POST["senha"] ?? "";
$perfil = $_POST["perfil"] ?? "";

$email = trim(strtolower($email));
$senha = trim($senha);
$perfil = trim($perfil);

if ($email === "" || $senha === "" || $perfil === "") {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos."
    ]);

    exit;
}

try {

    $sql = "SELECT id, nome, email, cpf, senha, tipo
            FROM usuarios
            WHERE email = :email
            AND tipo = :tipo
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":email" => $email,
        ":tipo" => $perfil
    ]);

    $usuario = $stmt->fetch();

    if (!$usuario) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "E-mail ou perfil incorreto."
        ]);

        exit;
    }

    if ($senha !== $usuario["senha"]) {

        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Senha incorreta."
        ]);

        exit;
    }

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso.",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"],
            "cpf" => $usuario["cpf"],
            "tipo" => $usuario["tipo"]
        ]
    ]);

} catch (PDOException $erro) {

    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro no banco de dados: " . $erro->getMessage()
    ]);

}

?>