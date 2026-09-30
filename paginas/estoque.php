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

    <!-- Cadastro Usuários -->
    <li>
      <a href="usuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 12 4 12zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Cadastro Usuários
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

    <!-- Usuários -->
    <li>
      <a class="profile-item" href="ListaUsuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Usuários
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
    <div id="secao-estoque"> 
        <div class="secao-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"> 
            <h1>Estoque Central <?= $verInativos ? '(Desativados)' : '' ?></h1>
            
            <div style="display: flex; gap: 10px;">
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
                                
                                <a href="desativar_produto.php?id=<?= $est['id_estoque'] ?>&status=0&ver_inativos=0" 
                                class="btn-excluir" 
                                style="background-color: #c52727d8; color: #ffffff; font-weight: bold; text-decoration: none;"
                                 onclick="return confirm('Deseja realmente desativar este produto?');">
                                  Desativar
                                </a>
                            <?php else: ?>
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
            <!-- O ID do estoque continua sendo enviado de forma oculta aqui -->
            <input type="hidden" id="mover_id_estoque" name="id_estoque">

            <!-- O campo "Número da Prateleira" foi removido pois o sistema agora automatiza o preenchimento -->

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
    document.getElementById('numero_prat').value = '';
    
    document.getElementById('modalMoverPrateleira').showModal();
}
</script>

</body>
</html>