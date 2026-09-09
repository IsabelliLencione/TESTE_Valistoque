<?php
require_once __DIR__ . '/config.php';

// 1. Recebe e valida o ID recebido pela URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            // 2. Executa a exclusão no MySQL com instrução preparada
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            die("Erro ao excluir usuário: " . $e->getMessage());
        }
    }
}

// 3. Redireciona de volta para a lista de usuários
header("Location: ../paginas/ListaUsuarios.php");
exit;