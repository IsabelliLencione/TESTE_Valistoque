<?php
require_once __DIR__ . "/../php/config.php";

// 1. Verifica se o ID foi passado na URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_estoque = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id_estoque) {
        try {
            // 2. Prepara e executa a exclusão no MySQL
            $stmt = $pdo->prepare("DELETE FROM estoque WHERE id_estoque = :id");
            $stmt->bindValue(':id', $id_estoque, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            die("Erro ao excluir produto: " . $e->getMessage());
        }
    }
}

// 3. Redireciona de volta para a página do estoque
header("Location: ../paginas/estoque.php"); // Ajuste o caminho se a sua pasta HTML/PHP for diferente
exit;