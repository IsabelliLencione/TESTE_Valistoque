<?php
require_once __DIR__ . "/../php/config.php";

try {
    // Busca todas as entradas no estoque central
    $stmt = $pdo->query("SELECT * FROM estoque WHERE ativo = 1 ORDER BY id_estoque DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configuração para forçar download do CSV
    $filename = "relatorio_estoque_geral_" . date('Y-m-d_H-i') . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Suporte a caracteres com acentuação no Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabeçalho
    fputcsv($output, ['ID Estoque', 'Nome do Produto', 'Lote', 'Validade', 'Qtd no Estoque Central (Entradas Restantes)', 'Qtd Saída (Prateleiras)'], ';');

    foreach ($produtos as $p) {
        // Busca quanto deste produto foi movido para as prateleiras
        $stmtPrat = $pdo->prepare("SELECT SUM(qte) as total_prateleira FROM prateleiras WHERE id_estoque = :id");
        $stmtPrat->bindValue(':id', $p['id_estoque'], PDO::PARAM_INT);
        $stmtPrat->execute();
        $resPrat = $stmtPrat->fetch(PDO::FETCH_ASSOC);
        
        $qtdPrateleira = $resPrat['total_prateleira'] ?? 0;

        fputcsv($output, [
            $p['id_estoque'],
            $p['nome_produto'],
            $p['lote'],
            date("d/m/Y", strtotime($p['data_validade'])),
            $p['total_itens'],
            $qtdPrateleira
        ], ';');
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die("Erro ao gerar relatório geral: " . $e->getMessage());
}