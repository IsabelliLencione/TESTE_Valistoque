<?php
require_once __DIR__ . "/../php/config.php";

try {
    $stmt = $pdo->query("SELECT * FROM produto ORDER BY id DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar produtos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque central</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estoque.css">
    <link href="https://googleapis.com" rel="stylesheet">
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

        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.html">Perfil</a>
        </li> 
    </ul> 
</nav>

<main>
    <div id="secao-estoque"> 
        <div class="secao-header"> 
            <h1>Estoque Central</h1> 
            <button class="btn-adicionar" onclick="window.location.href='produtos.php'" title="Cadastrar Novo Produto">+</button> 
        </div> 
        
        <div class="cards-container">
            <?php if (empty($produtos)): ?>
                <p class="sem-produtos">Nenhum produto cadastrado no estoque.</p>
            <?php else: ?>
                <?php foreach ($produtos as $prod): ?>
                    <?php 
                        $dataFormatada = date("d/m/Y", strtotime($prod['data_validade']));
                        $totalItens = $prod['qtd_caixas'] * $prod['produtos_por_caixa'];
                    ?>
            
                    <div class="card-item">
                        <div class="card-detalhes">
                            <div class="nome">
                                <h3><?= htmlspecialchars($prod['nome']) ?></h3>
                            </div>
                            <p class="card-info"><strong>Lote:</strong> <?= htmlspecialchars($prod['lote']) ?></p>
                            <p class="card-info"><strong>Validade:</strong> <?= htmlspecialchars($dataFormatada) ?></p>
                            <p class="card-info"><strong>Caixas:</strong> <?= htmlspecialchars($prod['qtd_caixas']) ?> cx</p>
                            <p class="card-info"><strong>Total no Lote:</strong> <?= htmlspecialchars($totalItens) ?> unidades</p>
                        </div>

                        <div class="card-acoes">
                            <a href="produtos.php?editar_id=<?= $prod['id'] ?>" class="btn-editar">Editar</a>
                            <a href="../php/excluir_produto.php?id=<?= $prod['id'] ?>" 
                               class="btn-excluir" 
                               onclick="return confirm('Tem certeza que deseja excluir o produto: <?= htmlspecialchars($prod['nome'], ENT_QUOTES) ?>?');">
                                Excluir
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> 
    </div> 
</main>


</body>
</html>
