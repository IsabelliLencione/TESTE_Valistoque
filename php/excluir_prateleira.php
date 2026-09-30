<?php
// Habilita exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../php/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prat = filter_input(INPUT_POST, 'id_prat', FILTER_VALIDATE_INT);

    if (!$id_prat) {
        die("ID da prateleira inválido.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Busca os dados atuais da prateleira para saber o que devolver ao estoque
        $stmtCheck = $pdo->prepare("SELECT * FROM prateleiras WHERE id_prat = :id_prat FOR UPDATE");
        $stmtCheck->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
        $stmtCheck->execute();
        $prateleira = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$prateleira) {
            $pdo->rollBack();
            die("Item da prateleira não encontrado.");
        }

        $id_estoque = $prateleira['id_estoque'];
        $quantidade_devolver = (int)$prateleira['quantidade_atual'];

        // 2. Devolve a quantidade de caixas de volta para o estoque central
        $stmtUpdateEstoque = $pdo->prepare("UPDATE estoque SET total_itens = total_itens + :qtd WHERE id_estoque = :id_est");
        $stmtUpdateEstoque->bindValue(':qtd', $quantidade_devolver, PDO::PARAM_INT);
        $stmtUpdateEstoque->bindValue(':id_est', $id_estoque, PDO::PARAM_INT);
        $stmtUpdateEstoque->execute();

        // 3. Exclui o registro da prateleira
        $stmtDelete = $pdo->prepare("DELETE FROM prateleiras WHERE id_prat = :id_prat");
        $stmtDelete->bindValue(':id_prat', $id_prat, PDO::PARAM_INT);
        $stmtDelete->execute();

        $pdo->commit();

        // Redireciona de volta para a tela de prateleiras com uma variável de sucesso
        header("Location: ../paginas/prateleira.php");
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro no Banco de Dados ao remover da prateleira: " . $e->getMessage());
    }
 
} else {
    // Se tentarem acessar o arquivo diretamente sem ser por POST, joga de volta para a listagem
    header("Location: prateleira.php");
    exit;
}
