<?php
require_once __DIR__ . "/../php/config.php";

// Verifica se o usuário quer ver os inativos
$verInativos = isset($_GET['ver_inativos']) && $_GET['ver_inativos'] == '1' ? 1 : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM estoque WHERE ativo = :status ORDER BY id_estoque DESC");
    $stmt->bindValue(':status', $verInativos ? 0 : 1, PDO::PARAM_INT);
    $stmt->execute();
    $estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // A movimentação utiliza somente prateleiras que já existem no banco.
    // A associação do lote com a prateleira acontece durante a transferência.
    $stmtPrateleiras = $pdo->query("SELECT p.id_prat, p.id_estoque, p.qte, e.lote, e.nome_produto FROM prateleiras p LEFT JOIN estoque e ON e.id_estoque = p.id_estoque ORDER BY p.id_prat");
    $prateleiras = $stmtPrateleiras->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../css/estoque.css?v=2.0">
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
    
   <li style="margin-top: auto; border-top: 1px solid #34495e;">
      <a href="principal.html">
      <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
      <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      Sair
      </a>
  </li>
  </ul> 
</nav>

<main>
    <div id="secao-estoque"> 
        <div class="secao-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;"> 
            <h1>Estoque Central <?= $verInativos ? '(Desativados)' : '' ?></h1>
            
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- BARRA DE PESQUISA -->
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="text" id="inputPesquisa" onkeyup="filtrarEstoque()" placeholder="Pesquisar produto ou lote..." 
                           style="padding: 10px 15px 10px 35px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; width: 240px; outline: none;">
                    <svg style="position: absolute; left: 10px; width: 16px; height: 16px; fill: #7f8c8d;" viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </div>

                <?php if ($verInativos): ?>
                    <a href="estoque.php?ver_inativos=0" class="btn-editar" style="background:#34495e; text-decoration:none; padding:10px 15px; border-radius:8px; color:#fff; font-weight:bold;">Ver Produtos Ativos</a>
                <?php else: ?>
                    <a href="estoque.php?ver_inativos=1" class="btn-editar" style="background:#7f8c8d; text-decoration:none; padding:10px 15px; border-radius:8px; color:#fff; font-weight:bold;">Ver Produtos Desativados</a>
                    
                    <!-- BOTÃO ADICIONAR COM ÍCONE SVG (CENTRALIZAÇÃO PERFEITA) -->
                    <button class="btn-adicionar" onclick="window.location.href='produtos.php'" title="Cadastrar Novo Produto">
                        <svg style="width: 22px; height: 22px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </button>
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
                            <p class="card-info"><strong>Lote:</strong> <span class="info-lote"><?= htmlspecialchars($est['lote']) ?></span></p>
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

    <!-- Modal para mover unidades do estoque para uma prateleira -->
    <dialog id="modalMoverPrateleira" style="border:none; border-radius:12px; padding:24px; width:400px; max-width:90vw; margin:auto; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 id="modalTituloProduto" style="margin:0; font-size:18px; color:#1a252f;">Mover para Prateleira</h3>
            <button onclick="document.getElementById('modalMoverPrateleira').close()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#777;">&times;</button>
        </div>
        
        <form action="mover_para_prateleira.php" method="POST">
            <input type="hidden" id="mover_id_estoque" name="id_estoque">

            <label for="id_prat" style="display:block; margin-bottom:5px; font-weight:bold; color:#064b78;">Prateleira:</label>
            <select id="id_prat" name="id_prat" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:8px;">
                <option value="">Selecione uma prateleira</option>
                <?php foreach ($prateleiras as $prat): ?>
                    <option value="<?= (int)$prat['id_prat'] ?>">
                        <?php if ($prat['id_estoque'] !== null): ?>
                            <?php
                                $nomePrat = trim((string)($prat['nome_produto'] ?? ''));
                                $nomePratExibicao = mb_strlen($nomePrat) > 32 ? mb_substr($nomePrat, 0, 32) . '...' : $nomePrat;
                            ?>
                            Prateleira <?= (int)$prat['id_prat'] ?> - Lote <?= htmlspecialchars($prat['lote']) ?> (<?= htmlspecialchars($nomePratExibicao) ?>)
                        <?php else: ?>
                            Prateleira <?= (int)$prat['id_prat'] ?> - Vazia
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="quantidade_mover" style="display:block; margin-bottom:5px; font-weight:bold; color:#064b78;">Quantidade de unidades a mover:</label>
            <input type="number" id="quantidade_mover" name="quantidade_mover" min="1" required placeholder="Ex: 5" style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ccc; border-radius:8px;">

            <button type="submit" style="width:100%; background:#2ecc71; color:#fff; border:none; padding:12px; border-radius:8px; font-size:16px; font-weight:bold; cursor:pointer;">Confirmar Transferência</button>
        </form>
    </dialog>
</main>

<script>
function abrirModalMover(id, nome, maxQtd) {
    document.getElementById('mover_id_estoque').value = id;
    document.getElementById('modalTituloProduto').innerText = 'Mover ' + nome;
    
    const inputQtd = document.getElementById('quantidade_mover');
    inputQtd.max = maxQtd;
    inputQtd.value = '';
    document.getElementById('modalMoverPrateleira').showModal();
}

// Filtra produtos/lotes exibindo apenas o que COMEÇA com o texto digitado
function filtrarEstoque() {
    const termo = document.getElementById('inputPesquisa').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.card-item');

    cards.forEach(card => {
        const nomeProduto = card.querySelector('h3') ? card.querySelector('h3').innerText.toLowerCase().trim() : '';
        const loteProduto = card.querySelector('.info-lote') ? card.querySelector('.info-lote').innerText.toLowerCase().trim() : '';

        const comecaComNome = nomeProduto.startsWith(termo);
        const comecaComLote = loteProduto.startsWith(termo);

        if (termo === '' || comecaComNome || comecaComLote) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}
</script>

</body>
</html>