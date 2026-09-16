<?php
require_once __DIR__ . "/../php/config.php";

// Garante que a coluna 'ativo' exista na tabela 'estoque'
try {
    $pdo->exec("ALTER TABLE estoque ADD COLUMN ativo TINYINT(1) DEFAULT 1");
} catch (PDOException $e) {
    
}

// Verifica se o usuário quer ver os inativos
$verInativos = isset($_GET['ver_inativos']) && $_GET['ver_inativos'] == '1' ? 1 : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM estoque WHERE ativo = :status ORDER BY id_estoque DESC");
    $stmt->bindValue(':status', $verInativos ? 0 : 1, PDO::PARAM_INT);
    $stmt->execute();
    $estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar estoque: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque Central</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/estoque.css">
</head>
<body>
    
<nav class="nav"> 
    <div class="header-nav">Valistoque</div>
    <ul> 
        <li><a href="relatorio.html">Relatório</a></li>
        <li><a href="produtos.php">Cadastro Produtos</a></li>
        <li><a href="usuarios.php">Cadastro Usuários</a></li>
        <li><a href="prateleira.php">Prateleiras</a></li>
        <li><a href="estoque.php">Estoque Central</a></li>
        <li><a href="alertas.php">Alertas</a></li>
        <li><a href="ListaUsuarios.php">Usuários</a></li>
        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.php">Perfil</a>
        </li> 
    </ul> 
</nav>

<main>
    <div id="secao-estoque"> 
        <div class="secao-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"> 
            <h1>Estoque Central <?= $verInativos ? '(Desativados)' : '' ?></h1>
            
            <div style="display: flex; gap: 10px;">
                <!-- Botão Alternar entre Ativos e Desativados -->
                <?php if ($verInativos): ?>
                    <a href="estoque.php?ver_inativos=0" class="btn-editar" style="background:#34495e; text-decoration:none; padding:10px 15px; border-radius:8px; color:#fff; font-weight:bold;">Ver Produtos Ativos</a>
                <?php else: ?>
                    <a href="estoque.php?ver_inativos=1" class="btn-editar" style="background:#7f8c8d; text-decoration:none; padding:10px 15px; border-radius:8px; color:#fff; font-weight:bold;">Ver Produtos Desativados</a>
                    <button class="btn-adicionar" onclick="window.location.href='produtos.php'" title="Cadastrar Novo Produto">+</button>
                <?php endif; ?>
            </div>
        </div> 
        
        <div class="cards-container">
            <?php if (empty($estoque)): ?>
                <p class="sem-produtos">Nenhum produto <?= $verInativos ? 'desativado' : 'ativo' ?> encontrado.</p>
            <?php else: ?>
                <?php foreach ($estoque as $est): ?>
                    <?php $dataFormatada = date("d/m/Y", strtotime($est['data_validade'])); ?>
            
                    <div class="card-item" style="<?= $verInativos ? 'opacity: 0.7; border: 1px dashed #95a5a6;' : '' ?>">
                        <div class="card-detalhes">
                            <div class="nome">
                                <h3><?= htmlspecialchars($est['nome_produto']) ?></h3>
                            </div>
                            <p class="card-info"><strong>Lote:</strong> <?= htmlspecialchars($est['lote']) ?></p>
                            <p class="card-info"><strong>Validade:</strong> <?= htmlspecialchars($dataFormatada) ?></p>
                            <p class="card-info"><strong>Total no Lote:</strong> <?= htmlspecialchars($est['total_itens']) ?> unidades</p>
                        </div>

                        <div class="card-acoes" style="flex-wrap: wrap;">
                            <?php if (!$verInativos): ?>
                                <button type="button" class="btn-mover" 
                                        onclick="abrirModalMover(<?= $est['id_estoque'] ?>, '<?= htmlspecialchars($est['nome_produto'], ENT_QUOTES) ?>', <?= $est['total_itens'] ?>)">
                                    Mover para Prateleira
                                </button>
                                <a href="produtos.php?editar_id=<?= $est['id_estoque'] ?>" class="btn-editar">Editar</a>
                                
                                <!-- Botão Desativar -->
                               <a href="desativar_produto.php?id=<?= $est['id_estoque'] ?>&status=0&ver_inativos=0" 
                                class="btn-excluir" 
                                style="background-color: #c52727d8; color: #ffffff; font-weight: bold; text-decoration: none;"
                                 onclick="return confirm('Deseja realmente desativar este produto?');">
                                  Desativar
                                </a>
                            <?php else: ?>
                                <!-- Botão Reativar -->
                                <a href="desativar_produto.php?id=<?= $est['id_estoque'] ?>&status=1&ver_inativos=1" 
                                   class="btn-editar" 
                                   style="background-color: #27ae60; width: 100%; text-align: center;"
                                   onclick="return confirm('Deseja reativar este produto?');">
                                    Reativar Produto
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> 
    </div> 

    <!-- Modal para Mover Caixas -->
    <dialog id="modalMoverPrateleira" style="border:none; border-radius:12px; padding:24px; width:400px; max-width:90vw; margin:auto; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 id="modalTituloProduto" style="margin:0; font-size:18px; color:#1a252f;">Mover para Prateleira</h3>
            <button onclick="document.getElementById('modalMoverPrateleira').close()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#777;">&times;</button>
        </div>
        
        <form action="mover_para_prateleira.php" method="POST">
            <input type="hidden" id="mover_id_estoque" name="id_estoque">
            
            <label for="numero_prat" style="display:block; margin-bottom:5px; font-weight:bold; color:#064b78;">Número da Prateleira:</label>
            <input type="number" id="numero_prat" name="numero_prat" min="1" required placeholder="Ex: 1" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:8px;">

            <label for="caixas_mover" style="display:block; margin-bottom:5px; font-weight:bold; color:#064b78;">Quantidade a Mover:</label>
            <input type="number" id="caixas_mover" name="caixas_mover" min="1" required placeholder="Ex: 5" style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ccc; border-radius:8px;">

            <button type="submit" style="width:100%; background:#2ecc71; color:#fff; border:none; padding:12px; border-radius:8px; font-size:16px; font-weight:bold; cursor:pointer;">Confirmar Transferência</button>
        </form>
    </dialog>
</main>

<script>
function abrirModalMover(id, nome, maxQtd) {
    document.getElementById('mover_id_estoque').value = id;
    document.getElementById('modalTituloProduto').innerText = 'Mover ' + nome;
    
    const inputQtd = document.getElementById('caixas_mover');
    inputQtd.max = maxQtd;
    inputQtd.value = '';
    
    document.getElementById('modalMoverPrateleira').showModal();
}
</script>

</body>
</html>