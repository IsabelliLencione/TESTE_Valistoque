<?php
require_once __DIR__ . '/../php/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT);

if ($id !== false && $status !== null) {
    try {
        $stmt = $pdo->prepare("UPDATE estoque SET ativo = :status WHERE id_estoque = :id");
        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        die("Erro ao alterar status do produto: " . $e->getMessage());
    }
}

// Retorna para a lista de estoque mantendo a visualização
$verInativos = isset($_GET['ver_inativos']) ? $_GET['ver_inativos'] : 0;
header("Location: estoque.php?ver_inativos=" . $verInativos);
exit;