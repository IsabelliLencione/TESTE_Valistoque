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
$tipo_acesso = $_POST["tipo_acesso"] ?? ""; 

$email = trim(strtolower($email));
$senha = trim($senha);
$tipo_acesso = trim(strtolower($tipo_acesso));

if ($email === "" || $senha === "" || $tipo_acesso === "") {
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
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário ou senha incorretos."
        ]);
        exit;
    }

    // VALIDAÇÃO CRUZADA: Garante que o tipo do banco é idêntico ao selecionado na tela
    if (strtolower($usuario["tipo"]) !== $tipo_acesso) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário incorreto para este tipo de perfil de acesso."
        ]);
        exit;
    }

    // Validação da Senha (suporta texto limpo ou hash)
    if (!password_verify($senha, $usuario["senha"]) && $senha !== $usuario["senha"]) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário ou senha incorretos."
        ]);
        exit;
    }

    // ATENÇÃO: Inicia a sessão do PHP para que a página perfil.php possa ler o tipo correto
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_email"] = $usuario["email"];
    $_SESSION["usuario_tipo"] = $usuario["tipo"]; // Salva 'administrador' ou 'funcionario' vindo do banco

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso.",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"],
            "tipo" => $usuario["tipo"] // Envia o valor real ('administrador') para o localStorage do HTML
        ]
    ]);

} catch (PDOException $erro) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro no banco de dados: " . $erro->getMessage()
    ]);
}
?>
