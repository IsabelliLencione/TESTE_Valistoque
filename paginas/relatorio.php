<?php

require_once '../php/config.php';

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

if ($mes < 1 || $mes > 12) {
    $mes = (int)date('m');
}

$nomesMeses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

$saidaEstoque = [];
$entradaEstoque = [];
$saidaPrateleira = [];
$entradaPrateleira = [];
$alertas = [];

try {

    $sql = "
        SELECT
            m.id_movimentacao,
            m.quantidade,
            m.numero_prat,
            m.data_movimentacao,
            e.nome_produto,
            e.lote
        FROM movimentacoes m
        INNER JOIN estoque e
            ON e.id_estoque = m.id_estoque
        WHERE m.tipo = :tipo
        AND m.local_movimentacao = :local
        AND MONTH(m.data_movimentacao) = :mes
        AND YEAR(m.data_movimentacao) = :ano
        ORDER BY m.data_movimentacao DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tipo' => 'saida',
        ':local' => 'estoque',
        ':mes' => $mes,
        ':ano' => $ano
    ]);

    $saidaEstoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tipo' => 'entrada',
        ':local' => 'estoque',
        ':mes' => $mes,
        ':ano' => $ano
    ]);

    $entradaEstoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tipo' => 'saida',
        ':local' => 'prateleira',
        ':mes' => $mes,
        ':ano' => $ano
    ]);

    $saidaPrateleira = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':tipo' => 'entrada',
        ':local' => 'prateleira',
        ':mes' => $mes,
        ':ano' => $ano
    ]);

    $entradaPrateleira = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlAlertas = "
        SELECT
            a.id_alerta,
            a.tipo_alerta,
            a.mensagem,
            a.data_alerta,
            e.nome_produto,
            e.lote
        FROM alertas a
        LEFT JOIN estoque e
            ON e.id_estoque = a.id_estoque
        WHERE MONTH(a.data_alerta) = :mes
        AND YEAR(a.data_alerta) = :ano
        ORDER BY a.data_alerta DESC
    ";

    $stmt = $pdo->prepare($sqlAlertas);

    $stmt->execute([
        ':mes' => $mes,
        ':ano' => $ano
    ]);

    $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $erro) {

    $erroBanco = $erro->getMessage();

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Relatório</title>

    <link rel="stylesheet" href="../css/style.css">

    <link rel="stylesheet" href="../css/relatorio.css">

</head>

<body>

<nav class="nav">

    <div class="header-nav">Valistoque</div>

    <ul>

        <li>

            <a href="relatorio.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                </svg>

                Relatório

            </a>

        </li>

        <li>

            <a href="produtos.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
                </svg>

                Cadastro Produtos

            </a>

        </li>

        <li>

            <a href="usuarios.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>

                Cadastro Usuários

            </a>

        </li>

        <li>

            <a href="prateleira.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M4 11h5V5H4v6zm0 8h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-8h5V5h-5v6zm6-6v6h5V5h-5z"/>
                </svg>

                Prateleiras

            </a>

        </li>

        <li>

            <a href="estoque.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/>
                </svg>

                Estoque Central

            </a>

        </li>

        <li>

            <a href="alertas.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5S10 3.17 10 4v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>

                Alertas

            </a>

        </li>

        <li>

            <a class="profile-item" href="ListaUsuarios.php">

                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>

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

<main>

    <div id="secao-relatorio">

        <div class="relatorio-container">

            <div class="content-header">

                <div class="month-selector">

                    <span class="seta-mes" onclick="mudarMes(-1)">❮</span>

                    <span id="txt-mes">
                        <?= htmlspecialchars($nomesMeses[$mes]) ?>
                    </span>

                    <span class="seta-mes" onclick="mudarMes(1)">❯</span>

                </div>

                <h1 id="txt-ano">
                    <?= $ano ?>
                </h1>

            </div>

            <div class="cards-grid">

                <div class="card">

                    <h3>Saída - Estoque central</h3>

                    <div class="card-list-scroll" id="saida-estoque">

                        <?php if (empty($saidaEstoque)): ?>

                            <div class="item-vazio">
                                Nenhuma saída registrada.
                            </div>

                        <?php else: ?>

                            <?php foreach ($saidaEstoque as $item): ?>

                                <div class="relatorio-item">

                                    <strong>
                                        <?= htmlspecialchars($item['nome_produto']) ?>
                                    </strong>

                                    <span>
                                        Lote:
                                        <?= htmlspecialchars($item['lote']) ?>
                                        |
                                        Qtd:
                                        <?= htmlspecialchars($item['quantidade']) ?>
                                    </span>

                                    <small>
                                        Saída do estoque
                                    </small>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="card">

                    <h3>Entrada - Estoque central</h3>

                    <div class="card-list-scroll" id="entrada-estoque">

                        <?php if (empty($entradaEstoque)): ?>

                            <div class="item-vazio">
                                Nenhuma entrada registrada.
                            </div>

                        <?php else: ?>

                            <?php foreach ($entradaEstoque as $item): ?>

                                <div class="relatorio-item">

                                    <strong>
                                        <?= htmlspecialchars($item['nome_produto']) ?>
                                    </strong>

                                    <span>
                                        Lote:
                                        <?= htmlspecialchars($item['lote']) ?>
                                        |
                                        Qtd:
                                        <?= htmlspecialchars($item['quantidade']) ?>
                                    </span>

                                    <small>
                                        Entrada no estoque
                                    </small>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="card">

                    <h3>Saída - Prateleira</h3>

                    <div class="card-list-scroll" id="saida-prateleira">

                        <?php if (empty($saidaPrateleira)): ?>

                            <div class="item-vazio">
                                Sem saídas de prateleira no período.
                            </div>

                        <?php else: ?>

                            <?php foreach ($saidaPrateleira as $item): ?>

                                <div class="relatorio-item">

                                    <strong>
                                        <?= htmlspecialchars($item['nome_produto']) ?>
                                    </strong>

                                    <span>
                                        Prateleira:
                                        <?= htmlspecialchars($item['numero_prat'] ?? '-') ?>
                                    </span>

                                    <small>
                                        Lote:
                                        <?= htmlspecialchars($item['lote']) ?>
                                        |
                                        Qtd:
                                        <?= htmlspecialchars($item['quantidade']) ?>
                                    </small>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="card">

                    <h3>Entrada - Prateleira</h3>

                    <div class="card-list-scroll" id="entrada-prateleira">

                        <?php if (empty($entradaPrateleira)): ?>

                            <div class="item-vazio">
                                Nenhuma entrada na prateleira.
                            </div>

                        <?php else: ?>

                            <?php foreach ($entradaPrateleira as $item): ?>

                                <div class="relatorio-item">

                                    <strong>
                                        Prateleira
                                        <?= htmlspecialchars($item['numero_prat'] ?? '-') ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars($item['nome_produto']) ?>
                                        (Lote:
                                        <?= htmlspecialchars($item['lote']) ?>)
                                    </span>

                                    <small>
                                        Unidades:
                                        <?= htmlspecialchars($item['quantidade']) ?>
                                    </small>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div class="alert-card">

                <h3>Alertas emitidos:</h3>

                <div class="alert-content">

                    <div class="alert-list-scroll" id="lista-alertas">

                        <?php if (empty($alertas)): ?>

                            <div class="item-vazio">
                                Nenhum alerta registrado.
                            </div>

                        <?php else: ?>

                            <?php foreach ($alertas as $alerta): ?>

                                <div class="alerta-linha">

                                    <span class="tag-status">
                                        <?= htmlspecialchars($alerta['tipo_alerta']) ?>
                                    </span>

                                    <p>
                                        <?= htmlspecialchars($alerta['mensagem']) ?>
                                    </p>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<script>
    window.mesRelatorio = <?= $mes ?>;
    window.anoRelatorio = <?= $ano ?>;
</script>

<script type="module" src="../js/relatorio.js"></script>

</body>

</html>