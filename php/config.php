<?php
// Configuração de acesso ao MySQL.
// Ajuste usuário e senha se o seu XAMPP estiver configurado de outra forma.
$host = "localhost";
$banco = "valistoque_testes";
$usuario = "root";
$senha = "12345678";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    die("Não foi possível conectar ao banco de dados: " . $erro->getMessage());
}

