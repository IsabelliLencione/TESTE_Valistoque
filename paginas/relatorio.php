
<?php
require_once __DIR__ . '/../php/config.php';

// Filtros recebidos pela URL.
$busca = trim($_GET['busca'] ?? '');
$tipo_filtro = $_GET['tipo'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Tipos permitidos no filtro.
$tiposPermitidos = [
    'entrada',
    'saida',
    'ajuste_entrada',
    'ajuste_saida'
];

// Ignora tipos inválidos enviados pela URL.
if (!in_array($tipo_filtro, $tiposPermitidos, true)) {
    $tipo_filtro = '';
}

// Monta as condições da consulta.
$conditions = [];
$params = [];

if ($busca !== '') {
    $conditions[] = "(e.nome_produto LIKE :busca
                      OR e.lote LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

if ($tipo_filtro !== '') {
    $conditions[] = "h.tipo = :tipo";
    $params[':tipo'] = $tipo_filtro;
}

if ($data_inicio !== '') {
    $conditions[] = "h.data_movimentacao >= :data_inicio";
    $params[':data_inicio'] = $data_inicio . ' 00:00:00';
}

if ($data_fim !== '') {
    $conditions[] = "h.data_movimentacao <= :data_fim";
    $params[':data_fim'] = $data_fim . ' 23:59:59';
}

$whereClause = !empty($conditions)
    ? 'WHERE ' . implode(' AND ', $conditions)
    : '';

try {
    // Busca as movimentações dos lotes ativos.
    $sqlHist = "
        SELECT
            h.*,
            e.nome_produto,
            e.lote
        FROM historico_estoque h
        INNER JOIN estoque e
            ON e.id_estoque = h.id_estoque
            AND e.ativo = 1
        {$whereClause}
        ORDER BY
            h.data_movimentacao DESC,
            h.id_historico DESC
        LIMIT 1000
    ";

    $stmtHist = $pdo->prepare($sqlHist);
    $stmtHist->execute($params);

    $historico = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
 
    // Calcula os totais respeitando os mesmos filtros da tabela
    // e considerando somente lotes ativos.
    $sqlTotais = "
        SELECT
            COALESCE(
                SUM(
                    CASE
                        WHEN h.tipo IN ('entrada', 'ajuste_entrada')
                        THEN h.quantidade
                        ELSE 0
                    END
                ),
                0
            ) AS total_entradas,

            COALESCE(
                SUM(
                    CASE
                        WHEN h.tipo IN ('saida', 'ajuste_saida')
                        THEN h.quantidade
                        ELSE 0
                    END
                ),
                0
            ) AS total_saidas

        FROM historico_estoque h

        INNER JOIN estoque e
            ON e.id_estoque = h.id_estoque
            AND e.ativo = 1

        {$whereClause}
    ";

    $stmtTotais = $pdo->prepare($sqlTotais);
    $stmtTotais->execute($params);

    $totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    error_log("Erro ao consultar relatório: " . $e->getMessage());

    $historico = [];

    $totais = [
        'total_entradas' => 0,
        'total_saidas' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório do Estoque Central</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/relatorio.css?v=1.1">
    <!-- Biblioteca HTML2PDF para download em PDF sem complicações -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
    <div class="header-pagina">
        <div>
            <h1>Relatório de Movimentações do Estoque Central</h1>
            <p class="subtitulo">Acompanhe as entradas, cadastros e transferências em tempo real</p>
        </div>
        <!-- BOTÃO DE BAIXAR EM PDF -->
        <button type="button" onclick="gerarPDF()" class="btn-download">
            <svg viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
            Baixar PDF
        </button>
    </div>

    <!-- CONTEÚDO QUE SERÁ EXPORTADO PARA O PDF -->
    <div id="conteudo-relatorio">
        <!-- CARDS DE RESUMO ESTATÍSTICO (APENAS ENTRADAS E SAÍDAS) -->
        <div class="cards-resumo">
            <div class="card-resumo card-entrada">
                <div class="card-icon">↓</div>
                <div class="card-content">
                    <span class="card-titulo">Total Entradas</span>
                    <span class="card-valor"><?= number_format((int)($totais['total_entradas'] ?? 0), 0, ',', '.') ?> <small>un</small></span>
                </div>
            </div>

            <div class="card-resumo card-saida">
                <div class="card-icon">↑</div>
                <div class="card-content">
                    <span class="card-titulo">Total Saídas / Mover</span>
                    <span class="card-valor"><?= number_format((int)($totais['total_saidas'] ?? 0), 0, ',', '.') ?> <small>un</small></span>
                </div>
            </div>
        </div>
         <p class="aviso-limite" data-html2canvas-ignore="true">
            São exibidas no máximo as 1.000 movimentações mais recentes
            para os filtros selecionados. Os totais dos cards consideram
            todas as movimentações correspondentes aos filtros.
        </p>

        <!-- PAINEL DE FILTROS -->
        <div class="painel-filtros" data-html2canvas-ignore="true">
            <form method="GET" action="relatorio.php" class="form-filtros">
                <div class="campo-filtro">
                    <label for="busca">Pesquisar:</label>
                    <input type="text" id="busca" name="busca" placeholder="Produto ou Lote..." value="<?= htmlspecialchars($busca) ?>">
                </div>

                <div class="campo-filtro">
                    <label for="tipo">Tipo:</label>
                    <select id="tipo" name="tipo">
                        <option value="" <?= $tipo_filtro === '' ? 'selected' : '' ?>>
                            Todos os Tipos
                        </option>

                        <option value="entrada" <?= $tipo_filtro === 'entrada' ? 'selected' : '' ?>>
                            Entrada / Cadastro
                        </option>

                        <option value="saida" <?= $tipo_filtro === 'saida' ? 'selected' : '' ?>>
                            Saída / Transferência
                        </option>

                        <option value="ajuste_entrada" <?= $tipo_filtro === 'ajuste_entrada' ? 'selected' : '' ?>>
                            Ajuste de Entrada
                        </option>

                        <option value="ajuste_saida" <?= $tipo_filtro === 'ajuste_saida' ? 'selected' : '' ?>>
                            Ajuste de Saída
                        </option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="data_inicio">De:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
                </div>

                <div class="campo-filtro">
                    <label for="data_fim">Até:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
                </div>

                <div class="acoes-filtro">
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                    <a href="relatorio.php" class="btn-limpar">Limpar</a>
                </div>
            </form>
        </div>

        <!-- TABELA DE MOVIMENTAÇÕES -->
        <section class="tabela-container">
            <?php if (empty($historico)): ?>
                <div class="sem-registros">
                    <p>Nenhuma movimentação foi encontrada para os filtros selecionados.</p>
                </div>
            <?php else: ?>
                <table class="tabela-relatorio">
                    <thead>
                        <tr>
                            <th>Data e Hora</th>
                            <th>Tipo</th>
                            <th>Produto</th>
                            <th>Lote</th>
                            <th>Quantidade</th>
                            <th>Observação / Destino</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach ($historico as $h): ?>
                            <?php
                                $tipoRaw = $h['tipo'];

                                $tiposExibicao = [
                                    'entrada' => [
                                        'texto' => '↓ ENTRADA',
                                        'classe' => 'tag-entrada'
                                    ],
                                    'saida' => [
                                        'texto' => '↑ SAÍDA',
                                        'classe' => 'tag-saida'
                                    ],
                                    'ajuste_entrada' => [
                                        'texto' => '↑ AJUSTE DE ENTRADA',
                                        'classe' => 'tag-entrada'
                                    ],
                                    'ajuste_saida' => [
                                        'texto' => '↓ AJUSTE DE SAÍDA',
                                        'classe' => 'tag-saida'
                                    ]
                                ];

                                $tipoInfo = $tiposExibicao[$tipoRaw] ?? [
                                    'texto' => strtoupper($tipoRaw),
                                    'classe' => 'tag-saida'
                                ];
                            ?>

                            <tr>
                                <td>
                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime($h['data_movimentacao'])
                                    ) ?>
                                </td>

                                <td>
                                    <span class="tag-mov <?= $tipoInfo['classe'] ?>">
                                        <?= htmlspecialchars($tipoInfo['texto']) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($h['nome_produto']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="badge-lote">
                                        <?= htmlspecialchars($h['lote']) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        <?= (int) $h['quantidade'] ?> un
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $h['observacao'] ?? '-'
                                    ) ?>

                                    <?php if (!empty($h['id_prat'])): ?>
                                        — Prateleira <?= (int) $h['id_prat'] ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</main>

<script>
function gerarPDF() {
    const elemento = document.getElementById('conteudo-relatorio');
    const opcoes = {
        margin:       10,
        filename:     'Relatorio_Estoque_Central.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opcoes).from(elemento).save();
}
</script>

</body>
</html>