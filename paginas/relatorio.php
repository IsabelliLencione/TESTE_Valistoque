<?php
require_once __DIR__ . '/../php/config.php';

// Filtros recebidos via GET
$busca = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$tipo_filtro = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$data_inicio = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$data_fim = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

// Construção dinâmica do SQL
$conditions = [];
$params = [];

if (!empty($busca)) {
    $conditions[] = "(e.nome_produto LIKE :busca OR e.lote LIKE :busca)";
    $params[':busca'] = "%{$busca}%";
}

if (!empty($tipo_filtro)) {
    $conditions[] = "h.tipo = :tipo";
    $params[':tipo'] = $tipo_filtro;
}

if (!empty($data_inicio)) {
    $conditions[] = "DATE(h.data_movimentacao) >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
}

if (!empty($data_fim)) {
    $conditions[] = "DATE(h.data_fim) <= :data_fim";
    $params[':data_fim'] = $data_fim;
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

try {
    // Busca do histórico do Estoque Central
    $sqlHist = "SELECT h.*, e.nome_produto, e.lote 
                FROM historico_estoque h
                LEFT JOIN estoque e ON e.id_estoque = h.id_estoque
                {$whereClause}
                ORDER BY h.id_historico DESC LIMIT 100";
    
    $stmtHist = $pdo->prepare($sqlHist);
    foreach ($params as $key => $val) {
        $stmtHist->bindValue($key, $val);
    }
    $stmtHist->execute();
    $historico = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

    // Totais de Entradas e Saídas
    $sqlTotais = "SELECT 
                    SUM(CASE WHEN LOWER(tipo) IN ('entrada', 'cadastro', 'adicao') THEN quantidade ELSE 0 END) AS total_entradas,
                    SUM(CASE WHEN LOWER(tipo) IN ('saida', 'transferencia', 'mover', 'desativacao') THEN quantidade ELSE 0 END) AS total_saidas
                  FROM historico_estoque";
    $totais = $pdo->query($sqlTotais)->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $historico = [];
    $totais = ['total_entradas' => 0, 'total_saidas' => 0];
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
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        Relatório
      </a>
    </li> 
    <li>
      <a href="produtos.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
        Cadastro Produtos
      </a>
    </li> 
    <li>
      <a href="prateleira.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 11h5V5H4v6zm0 8h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-8h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>
        Prateleiras
      </a>
    </li> 
    <li>
      <a href="estoque.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/></svg>
        Estoque Central
      </a>
    </li> 
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
                        <option value="">Todos os Tipos</option>
                        <option value="entrada" <?= $tipo_filtro === 'entrada' ? 'selected' : '' ?>>Entrada / Cadastro</option>
                        <option value="saida" <?= $tipo_filtro === 'saida' ? 'selected' : '' ?>>Saída / Transferência</option>
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
                                $tipoRaw = strtolower($h['tipo'] ?? 'entrada');
                                $isEntrada = in_array($tipoRaw, ['entrada', 'cadastro', 'adicao', 'novo']);
                            ?>
                            <tr>
                                <td><?= date("d/m/Y H:i", strtotime($h['data_movimentacao'] ?? $h['created_at'] ?? 'now')) ?></td>
                                <td>
                                    <span class="tag-mov <?= $isEntrada ? 'tag-entrada' : 'tag-saida' ?>">
                                        <?= $isEntrada ? '↓ ENTRADA' : '↑ SAÍDA' ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($h['nome_produto'] ?? 'Produto #' . $h['id_estoque']) ?></strong></td>
                                <td><span class="badge-lote"><?= htmlspecialchars($h['lote'] ?? '-') ?></span></td>
                                <td><strong><?= (int)($h['quantidade'] ?? 0) ?> un</strong></td>
                                <td><?= htmlspecialchars($h['observacao'] ?? ($isEntrada ? 'Entrada no estoque central' : 'Movido para prateleira')) ?></td>
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