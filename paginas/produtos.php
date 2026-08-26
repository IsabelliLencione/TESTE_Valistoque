<?php
require_once __DIR__ . '/../php/config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_itens = $POST['caixa'] * $_POST['produto_caixa'];
    $sql = "INSERT INTO estoque (nome_produto, lote, data_validade, total_itens, peso_un) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['produto'],
        $_POST['lote'],  
        $_POST['validade'],
        $total_itens,
        $_POST['peso']
    ]);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/produtos.css">
</head>
<body>
    <nav class="nav">
        <div class="header-nav">Valistoque</div>
        <ul>
            <li><a href="relatorio.html">Relatório</a></li>
            <li><a href="produtos.html">Cadastro Produtos</a></li>
            <li><a href="usuarios.php">Cadastro Usuários</a></li>
            <li><a href="prateleira.html">Prateleiras</a></li>
            <li><a href="estoque.html">Estoque Central</a></li>
            <li><a href="alertas.html">Alertas</a></li>
            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.html">Perfil</a>
            </li>
        </ul>
    </nav>
    <main>
        <!-- CADASTRO DOS PRODUTOS -->
        <div id="secao-produto">
            <h1 id="titulo-cadastro-produto">Cadastro de Produto</h1>
            <form action="" method="POST" class="produto">
                <input type="hidden" id="editando-id" value="">
                <div>
                    <label for="produto">Nome do produto:</label>
                    <input type="text" id="produto" name="produto" required>
                </div>
                <div>
                    <label for="lote">Lote:</label>
                    <input type="number" id="lote" name="lote" required>
                </div>
                <div>
                    <label for="validade">Data de validade:</label>
                    <input type="date" id="validade" name="validade" required>
                </div>
                <div>
                    <label for="caixa">Quantidade de Caixas:</label>
                    <input type="number" id="caixa" name="caixa" required>
                </div>
                <div>
                    <label for="produto-caixa">Produtos por Caixa:</label>
                    <input type="number" id="produto-caixa" name="produto-caixa" required>
                </div>
                <div>
                    <label for="peso">Peso do Produto (kg):</label>
                    <input type="number" id="peso" name="peso" step="0.01" required>
                </div>
                <button type="submit" class="btn-cadastrar" title="Salvar Produto">Salvar Produto</button>
            </form>
        </div>
    </main>
</body>
</html>
