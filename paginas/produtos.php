<?php
require_once __DIR__ . '/../php/config.php';

$produtoParaEditar = null;

// 1. Busca os dados do produto caso venha um ID para edição na URL
if (isset($_GET['editar_id'])) {
    $id_editar = filter_input(INPUT_GET, 'editar_id', FILTER_VALIDATE_INT);
    if ($id_editar) {
        $stmt = $pdo->prepare("SELECT * FROM estoque WHERE id_estoque = :id");
        $stmt->bindValue(':id', $id_editar, PDO::PARAM_INT);
        $stmt->execute();
        $produtoParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 2. Processa o salvamento (Cadastro ou Edição)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_estoque = filter_input(INPUT_POST, 'id_estoque', FILTER_VALIDATE_INT);
    $nome = $_POST['produto'];
    $lote = $_POST['lote'];
    $validade = $_POST['validade'];
    $peso = $_POST['peso'];
    $total_itens = ($_POST['caixa'] * $_POST['produto-caixa']);

    try {
        if ($id_estoque) {
            // Se tem ID, atualiza o produto existente (UPDATE)
            $sql = "UPDATE estoque SET nome_produto = ?, lote = ?, data_validade = ?, total_itens = ?, peso_un = ? WHERE id_estoque = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $lote, $validade, $total_itens, $peso, $id_estoque]);
        } else {
            // Se não tem ID, cria um produto novo (INSERT)
            $sql = "INSERT INTO estoque (nome_produto, lote, data_validade, total_itens, peso_un) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $lote, $validade, $total_itens, $peso]);
        }

        header("Location: estoque.php");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar o produto: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
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
            <li><a href="produtos.php">Cadastro Produtos</a></li>
            <li><a href="usuarios.php">Cadastro Usuários</a></li>
            <li><a href="prateleira.html">Prateleiras</a></li>
            <li><a href="estoque.php">Estoque Central</a></li>
            <li><a href="alertas.html">Alertas</a></li>
            <li><a href="ListaUsuarios.php">Usuários</a></li>
            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.php">Perfil</a>
            </li>
        </ul>
    </nav>
    <main>
        <!-- CADASTRO DOS PRODUTOS -->
        <div id="secao-produto">
    <h1 id="titulo-cadastro-produto">
        <?= $produtoParaEditar ? 'Editar Produto' : 'Cadastro de Produto' ?>
    </h1>
    
    <form action="" method="POST" class="produto">
        <!-- Campo oculto para enviar o ID do produto quando estiver editando -->
        <input type="hidden" name="id_estoque" value="<?= $produtoParaEditar['id_estoque'] ?? '' ?>">

        <div>
            <label for="produto">Nome do produto:</label>
            <input type="text" id="produto" name="produto" 
                   value="<?= htmlspecialchars($produtoParaEditar['nome_produto'] ?? '') ?>" required>
        </div>
        <div>
            <label for="lote">Lote:</label>
            <input type="number" id="lote" name="lote" 
                   value="<?= htmlspecialchars($produtoParaEditar['lote'] ?? '') ?>" required>
        </div>
        <div>
            <label for="validade">Data de validade:</label>
            <input type="date" id="validade" name="validade" 
                   value="<?= htmlspecialchars($produtoParaEditar['data_validade'] ?? '') ?>" required>
        </div>
        <div>
            <label for="caixa">Quantidade de Caixas:</label>
            <input type="number" id="caixa" name="caixa" 
                   value="<?= htmlspecialchars($produtoParaEditar ? 1 : '') ?>" required>
        </div>
        <div>
            <label for="produto-caixa">Produtos por Caixa:</label>
            <input type="number" id="produto-caixa" name="produto-caixa" 
                   value="<?= htmlspecialchars($produtoParaEditar['total_itens'] ?? '') ?>" required>
        </div>
        <div>
            <label for="peso">Peso do Produto (kg):</label>
            <input type="number" id="peso" name="peso" step="0.01" 
                   value="<?= htmlspecialchars($produtoParaEditar['peso_un'] ?? '') ?>" required>
        </div>
        
        <button type="submit" class="btn-cadastrar">
            <?= $produtoParaEditar ? 'Atualizar Produto' : 'Salvar Produto' ?>
        </button>
    </form>
</div>
    </main>
</body>
</html>
