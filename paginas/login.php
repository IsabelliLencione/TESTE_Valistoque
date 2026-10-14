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
$tipo_acesso = trim(strtolower($tipo_acesso)); // Ex: 'administrador' ou 'funcionario'

if ($email === "" || $senha === "" || $tipo_acesso === "") {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Preencha todos os campos."
    ]);
    exit;
}

try {
    // Busca o usuário pelo e-mail
    $sql = "SELECT id, nome, email, cpf, senha, tipo
            FROM usuarios
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([":email" => $email]);
    $usuario = $stmt->fetch();

    // Se não encontrar o e-mail
    if (!$usuario) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário ou senha incorretos."
        ]);
        exit;
    }

    // VALIDAÇÃO CRUZADA CORRIGIDA: Usa strcasecmp para ignorar maiúsculas/minúsculas 
    // Assim, 'Administrador' no banco e 'administrador' na tela vão dar match perfeitamente!
    if (strcasecmp($usuario["tipo"], $tipo_acesso) !== 0) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário incorreto para este tipo de perfil de acesso."
        ]);
        exit;
    }

    // Validação da Senha (aceita 'admin123'/'func123' em texto puro ou via hash)
    if (!password_verify($senha, $usuario["senha"]) && $senha !== $usuario["senha"]) {
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Usuário ou senha incorretos."
        ]);
        exit;
    }

    // Inicia a sessão do PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_email"] = $usuario["email"];
    $_SESSION["usuario_tipo"] = $usuario["tipo"]; 

    // Retorna o sucesso para o seu JavaScript/Frontend
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso.",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"],
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
