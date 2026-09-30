<?php
require_once __DIR__ . '/../php/config.php';

try {
    // Busca as prateleiras cadastradas trazendo o nome do produto e lote do estoque
    $sql = "SELECT p.id_prat, p.numero_prat, p.quantidade_atual, e.nome_produto, e.lote, e.data_validade 
            FROM prateleiras p 
            INNER JOIN estoque e ON p.id_estoque = e.id_estoque 
            ORDER BY p.numero_prat ASC";
    $stmt = $pdo->query($sql);
    $prateleiras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar prateleiras: " . $e->getMessage());
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

        <div class="header-nav">
            Valistoque
        </div>

        <ul>

            <li>
                <a href="relatorio.html">Relatório</a>
            </li>

            <li>
                <a href="produtos.php">Cadastro Produtos</a>
            </li>

            <li>
                <a href="usuarios.php">Cadastro Usuários</a>
            </li>

            <li>
                <a href="prateleira.php">Prateleiras</a>
            </li>

            <li>
                <a href="estoque.php">Estoque Central</a>
            </li>

            <li>
                <a href="alertas.php">Alertas</a>
            </li>

            <li>
                <a href="ListaUsuarios.php">Usuários</a>
            </li>

            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.php">Perfil</a>
            </li>

        </ul>

    </nav>


    <!-- Conteúdo principal -->
    <main>

        <!-- Área das prateleiras -->
        <div id="secao-prateleira">

            <div class="secao-header">

                <h1>Prateleiras</h1>

                <button class="btn-adicionar btn-mais"
                    onclick="document.getElementById('cadastrarPrateleira').showModal()" title="Adicionar Prateleira">
                    +
                </button>

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


          <div class="prateleiras-container">
    <?php if (empty($prateleiras)): ?>
        <div class="sem-resultados">Nenhuma prateleira cadastrada.</div>
    <?php else: ?>
        <?php foreach ($prateleiras as $prat): ?>
            <div class="prateleira-card-horizontal">
                <div class="prat-numero">Prateleira <?= htmlspecialchars($prat['numero_prat']) ?></div>
                <div class="prat-detalhes">
                    <h4><?= htmlspecialchars($prat['nome_produto']) ?></h4>
                    <p><strong>Alocado:</strong> <?= htmlspecialchars($prat['quantidade_atual']) ?> caixas/unidades</p>
                    <p><strong>Validade:</strong> <?= date('d/m/Y', strtotime($prat['data_validade'])) ?></p>
                    <p><strong>Lote:</strong> <?= htmlspecialchars($prat['lote']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


            <!-- Janela para cadastrar uma prateleira -->
            <dialog id="cadastrarPrateleira">

                <div class="btnEtitulo">

                    <h3>
                        Adicionar Prateleira
                    </h3>

                    <button onclick="cadastrarPrateleira.close()" class="btn-fechar" aria-label="Fechar">
                        &times;
                    </button>

                </div>


                <div class="modal-footer">

                    <form id="form-prateleira" onsubmit="return false;">

                        <!-- Escolher prateleira -->
                        <div>

                            <label for="num-prateleira">
                                Selecione a prateleira
                            </label>

                            <select id="num-prateleira" required></select>

                        </div>


                        <!-- Escolher lote -->
                        <div>

                            <label for="lote-prat">
                                Lote do produto
                            </label>

                            <select id="lote-prat" required></select>

                        </div>


                        <!-- Quantidade -->
                        <div>

                            <label for="caixas-prat">
                                Quantidade de caixas
                            </label>

                            <input type="number" id="caixas-prat" placeholder="Ex: 5" min="1" required>

                        </div>


                        <!-- Cadastrar -->
                        <button id="closeModalBtn" class="btn-close">
                            Cadastrar
                        </button>

                    </form>

                </div>

            </dialog>

        </div>

    </main>


    <!-- JavaScript -->
    <script type="module" src="../js/prateleira.js"></script>


</body>

</html>