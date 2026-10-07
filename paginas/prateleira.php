<?php
require_once __DIR__ . '/../php/config.php';

// O botão "+" cria uma nova prateleira vazia.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_prateleira'])) {
    try {
        $stmtAdicionar = $pdo->prepare("
            INSERT INTO prateleiras (peso_prat, qte, ultima_leitura)
            VALUES (0, NULL, NULL)
        ");
        $stmtAdicionar->execute();

        header('Location: prateleira.php');
        exit;
    } catch (PDOException $e) {
        die('Erro ao adicionar prateleira: ' . $e->getMessage());
    }
}

// Esvazia uma prateleira ocupada sem apagar o histórico de leituras do sensor.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['esvaziar_prateleira'])) {
    $idPrateleira = (int) $_POST['esvaziar_prateleira'];

    try {
        $stmtEsvaziar = $pdo->prepare("
            UPDATE prateleiras
            SET id_estoque = NULL,
                peso_prat = 0,
                qte = NULL,
                ultima_leitura = NULL
            WHERE id_prat = ?
        ");
        $stmtEsvaziar->execute([$idPrateleira]);

        header('Location: prateleira.php');
        exit;
    } catch (PDOException $e) {
        die('Erro ao esvaziar prateleira: ' . $e->getMessage());
    }
}

try {
    // A prateleira pode estar vazia, por isso usamos LEFT JOIN.
    $sql = "
        SELECT
            p.id_prat,
            p.id_estoque,
            p.peso_prat,
            p.qte,
            p.ultima_leitura,
            e.nome_produto,
            e.lote,
            e.data_validade
        FROM prateleiras p
        LEFT JOIN estoque e ON p.id_estoque = e.id_estoque
        ORDER BY p.id_prat ASC
    ";

    $stmt = $pdo->query($sql);
    $prateleiras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro ao buscar prateleiras: ' . $e->getMessage());
}
?>


<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Prateleiras - Valistoque</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/prateleira.css">
  

</head>

<body>

    <!-- Menu lateral -->
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


    <!-- Conteúdo principal -->
    <main>

        <!-- Área das prateleiras -->
        <div id="secao-prateleira">

            <div class="secao-header">

                <h1>Prateleiras</h1>

                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="adicionar_prateleira" value="1">
                    <button type="submit" class="btn-adicionar btn-mais" title="Adicionar Prateleira">+</button>
                </form>

            </div>


            <!-- Busca -->
            <form action="/buscar" method="get" class="form-busca-prateleira" onsubmit="return false;">

                <div>

                    <label for="prateleiraBusca">
                        Buscar prateleira
                    </label>

                    <input type="text" id="prateleiraBusca" placeholder="Buscar prateleira, produto ou lote...">

                </div>

            </form>


            <!-- Lista de prateleiras -->
<div class="prateleiras-container">
    <?php if (empty($prateleiras)): ?>
        <p style="color: #555; text-align: center; padding: 20px; font-family: sans-serif;">
            Nenhuma prateleira cadastrada.
        </p>
    <?php else: ?>
        <?php foreach ($prateleiras as $prat): ?>
            <div class="prateleira-card-horizontal">
                <div class="prat-numero">
                    Prateleira #<?= (int)$prat['id_prat'] ?>
                </div>

                <div class="prat-detalhes">
                    <?php if ($prat['id_estoque'] !== null): ?>
                        <h4><?= htmlspecialchars($prat['nome_produto']) ?></h4>
                        <p><strong>Lote:</strong> <?= htmlspecialchars($prat['lote']) ?></p>
                        <p><strong>Quantidade:</strong> <?= (int)$prat['qte'] ?> unidades</p>
                        <p><strong>Peso Total:</strong> <?= number_format((float)$prat['peso_prat'] / 1000, 3, ',', '.') ?> kg</p>

                        <?php if (!empty($prat['ultima_leitura'])): ?>
                            <p><strong>Última leitura:</strong> <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($prat['ultima_leitura']))) ?></p>
                        <?php else: ?>
                            <p><strong>Última leitura:</strong> Ainda não realizada</p>
                        <?php endif; ?>

                        <form method="POST" style="margin-top: 12px;">
                            <input type="hidden" name="esvaziar_prateleira" value="<?= (int)$prat['id_prat'] ?>">
                            <button type="submit" style="background: #e74c3c; color: #fff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                Esvaziar prateleira
                            </button>
                        </form>
                    <?php else: ?>
                        <h4>Prateleira vazia</h4>
                        <p><strong>Produto:</strong> Nenhum lote associado</p>
                        <p><strong>Quantidade:</strong> —</p>
                        <p><strong>Peso Total:</strong> 0,00 kg</p>
                        <p><strong>Última leitura:</strong> —</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


        </div>

    </main>


</body>

</html>