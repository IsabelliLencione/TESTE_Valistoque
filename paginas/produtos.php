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
            <!-- Relatório -->
    <li>
      <a href="relatorio.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        Relatório
      </a>
    </li> 

    <!-- Cadastro Produtos -->
    <li>
      <a href="produtos.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
        Cadastro Produtos
      </a>
    </li> 

   

    <!-- Prateleiras -->
    <li>
      <a href="prateleira.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 11h5V5H4v6zm0 8h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-8h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>
        Prateleiras
      </a>
    </li> 

    <!-- Estoque Central -->
    <li>
      <a href="estoque.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/></svg>
        Estoque Central
      </a>
    </li> 

    <!-- Alertas -->
    <li>
      <a href="alertas.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
        Alertas
      </a>
    </li> 

   
    
    <!-- Perfil -->
    <li style="margin-top: auto; border-top: 1px solid #34495e;">
      <a href="perfil.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3-1.07-3-3s1.07-3 3-3 3 1.07 3 3-1.07 3-3 3z"/></svg>
        Perfil
      </a>
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
            <label for="peso">Peso do Produto (gramas):</label>
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
