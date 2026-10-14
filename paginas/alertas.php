

<?php

require_once __DIR__ . "/../php/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set("America/Sao_Paulo");

/*
 * Identifica o perfil do usuário logado.
 * A sessão deve ser definida pelo arquivo de login.
 */
$tipoUsuario = strtolower(
    trim($_SESSION["tipo"] ?? "")
);

$ehAdministrador = in_array(
    $tipoUsuario,
    ["admin", "administrador"],
    true
);

/*
 * Mantém o MySQL no horário de São Paulo.
 */
try {
    $pdo->exec("SET time_zone = '-03:00'");
} catch (Throwable $e) {
    // Não interrompe a página.
}

$mensagem_feedback = null;
$tipo_feedback = "";

/* =========================================================
   SALVAR CONFIGURAÇÕES DOS ALERTAS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Somente administradores podem alterar as configurações.
    if (!$ehAdministrador) {
        http_response_code(403);
        exit(
            "Acesso negado. Apenas administradores podem alterar as configurações."
        );
    }

    $dias_validade = isset($_POST["dias_antes_validade"])
        ? trim($_POST["dias_antes_validade"])
        : "";

    $unidades_central = isset($_POST["unidades_minimas_central"])
        ? trim($_POST["unidades_minimas_central"])
        : "";

    $unidades_prateleira = isset($_POST["unidades_minimas_prateleira"])
        ? trim($_POST["unidades_minimas_prateleira"])
        : "";

    $intervalo = isset($_POST["intervalo_minutos"])
        ? trim($_POST["intervalo_minutos"])
        : "";

    $exibir_popups = isset($_POST["exibir_popups"]) ? 1 : 0;

    /* VALIDAR PREENCHIMENTO */

    if (
        $dias_validade === "" ||
        $unidades_central === "" ||
        $unidades_prateleira === "" ||
        $intervalo === ""
    ) {
        header("Location: alertas.php?erro=preenchimento");
        exit;
    }

    /* VALIDAR SE SÃO NÚMEROS INTEIROS */

    $valores = [
        $dias_validade,
        $unidades_central,
        $unidades_prateleira,
        $intervalo
    ];

    foreach ($valores as $valor) {
        if (!ctype_digit($valor)) {
            header("Location: alertas.php?erro=valores_invalidos");
            exit;
        }
    }

    $dias_validade = (int) $dias_validade;
    $unidades_central = (int) $unidades_central;
    $unidades_prateleira = (int) $unidades_prateleira;
    $intervalo = (int) $intervalo;

    /* VALIDAR VALORES */

    if (
        $dias_validade < 1 ||
        $unidades_central < 1 ||
        $unidades_prateleira < 1 ||
        $intervalo < 1 ||
        !in_array($intervalo, [5, 10, 15, 30, 60], true)
    ) {
        header("Location: alertas.php?erro=valores_invalidos");
        exit;
    }

    try {

        /* VERIFICAR SE A CONFIGURAÇÃO EXISTE */

        $stmt = $pdo->prepare("
            SELECT id
            FROM config_alertas
            WHERE id = 1
            LIMIT 1
        ");

        $stmt->execute();

        $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);

        /* ATUALIZAR */

        if ($configuracao) {

            $stmt = $pdo->prepare("
                UPDATE config_alertas
                SET
                    dias_antes_validade = ?,
                    unidades_minimas_central = ?,
                    unidades_minimas_prateleira = ?,
                    intervalo_minutos = ?,
                    exibir_popups = ?
                WHERE id = 1
            ");

            $stmt->execute([
                $dias_validade,
                $unidades_central,
                $unidades_prateleira,
                $intervalo,
                $exibir_popups
            ]);

        } else {

            /* INSERIR */

            $stmt = $pdo->prepare("
                INSERT INTO config_alertas (
                    id,
                    dias_antes_validade,
                    unidades_minimas_central,
                    unidades_minimas_prateleira,
                    intervalo_minutos,
                    exibir_popups
                )
                VALUES (1, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $dias_validade,
                $unidades_central,
                $unidades_prateleira,
                $intervalo,
                $exibir_popups
            ]);
        }

        header("Location: alertas.php?sucesso=1");
        exit;

    } catch (PDOException $e) {

        error_log(
            "Erro ao salvar configurações de alertas: "
            . $e->getMessage()
        );

        header("Location: alertas.php?erro=banco");
        exit;
    }
}

/* =========================================================
   BUSCAR CONFIGURAÇÕES SALVAS
========================================================= */

try {

    $stmt = $pdo->query("
        SELECT
            dias_antes_validade,
            unidades_minimas_central,
            unidades_minimas_prateleira,
            intervalo_minutos,
            exibir_popups
        FROM config_alertas
        WHERE id = 1
        LIMIT 1
    ");

    $dados_atuais = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $dados_atuais = null;

    error_log(
        "Erro ao buscar configurações de alertas: "
        . $e->getMessage()
    );
}

/* =========================================================
   MENSAGENS DE FEEDBACK
========================================================= */

if (isset($_GET["sucesso"])) {

    $tipo_feedback = "feedback-sucesso";
    $mensagem_feedback = "Configurações gravadas com sucesso no banco!";

} elseif (isset($_GET["erro"])) {

    $tipo_feedback = "feedback-erro";

    switch ($_GET["erro"]) {

        case "preenchimento":
            $mensagem_feedback = "Preencha todos os campos.";
            break;

        case "valores_invalidos":
            $mensagem_feedback = "Os valores informados são inválidos.";
            break;

        case "banco":
            $mensagem_feedback = "Erro ao salvar as configurações.";
            break;

        default:
            $mensagem_feedback = "Ocorreu um erro.";
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Alertas</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <link rel="stylesheet"
          href="../css/alertas.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="<?php echo $ehAdministrador ? 'perfil-admin': 'perfil-funcionario'; ?>">

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

    <div id="secao-alertas">

        <div class="alertas-page">

            <div class="secao-header">

                <h1>
                    Central de Alertas
                </h1>

                <?php if ($mensagem_feedback): ?>

                    <div class="card-feedback <?php echo htmlspecialchars($tipo_feedback); ?>">

                        <div class="feedback-icone"></div>

                        <div class="feedback-conteudo">
                            <?php echo htmlspecialchars($mensagem_feedback); ?>
                        </div>

                    </div>

                <?php endif; ?>

            </div>


            <div class="alertas-grid">


                <!-- =================================================
                     CONFIGURAÇÕES
                ================================================== -->
                <?php if ($ehAdministrador): ?>

                    <div class="alertas-card">

                        <h2>
                            Configuração dos avisos
                        </h2>

                        <p>
                            Defina quando o sistema deve avisar sobre validade próxima e estoque acabando.
                        </p>


                        <form
                            method="POST"
                            action="alertas.php"
                            id="form-config-alertas"
                        >


                            <label for="dias-alerta-validade">
                                Dias antes da validade para alertar
                            </label>

                            <input
                                type="number"
                                id="dias-alerta-validade"
                                name="dias_antes_validade"
                                min="1"
                                required
                                value="<?php echo htmlspecialchars($dados_atuais["dias_antes_validade"] ?? 30); ?>"
                            >


                            <label for="unidades-alerta-central">
                                Mínimo de unidades (Estoque Central)
                            </label>

                            <input
                                type="number"
                                id="unidades-alerta-central"
                                name="unidades_minimas_central"
                                min="1"
                                required
                                value="<?php echo htmlspecialchars($dados_atuais["unidades_minimas_central"] ?? 10); ?>"
                            >


                            <label for="unidades-alerta-prateleira">
                                Mínimo de unidades (Prateleiras)
                            </label>

                            <input
                                type="number"
                                id="unidades-alerta-prateleira"
                                name="unidades_minimas_prateleira"
                                min="1"
                                required
                                value="<?php echo htmlspecialchars($dados_atuais["unidades_minimas_prateleira"] ?? 5); ?>"
                            >


                            <label for="intervalo-alerta">
                                Revisar alertas automaticamente a cada
                            </label>

                            <select
                                id="intervalo-alerta"
                                name="intervalo_minutos"
                                required
                            >

                                <?php

                                $opcoes_minutos = [5, 10, 15, 30, 60];

                                $intervalo_salvo =
                                    (int) (
                                        $dados_atuais["intervalo_minutos"]
                                        ?? 15
                                    );

                                foreach ($opcoes_minutos as $min):

                                    $selected =
                                        ($intervalo_salvo === $min)
                                        ? "selected"
                                        : "";

                                ?>

                                    <option
                                        value="<?php echo $min; ?>"
                                        <?php echo $selected; ?>
                                    >
                                        <?php echo $min; ?> minutos
                                    </option>

                                <?php endforeach; ?>

                            </select>


                            <div
                                style="
                                    margin: 20px 0 10px 0;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                "
                            >

                                <input
                                    type="checkbox"
                                    id="exibir_popups"
                                    name="exibir_popups"
                                    value="1"
                                    style="
                                        width: 18px;
                                        height: 18px;
                                        cursor: pointer;
                                    "
                                    <?php
                                    echo (
                                        !isset($dados_atuais["exibir_popups"]) ||
                                        (int)$dados_atuais["exibir_popups"] === 1
                                    )
                                    ? "checked"
                                    : "";
                                    ?>
                                >

                                <label
                                    for="exibir_popups"
                                    style="
                                        margin: 0;
                                        cursor: pointer;
                                        font-weight: 500;
                                    "
                                >
                                    Exibir pop-ups visuais na tela (SweetAlert2)
                                </label>

                            </div>


                            <div class="texto-auxiliar">
                                As configurações ficam salvas centralizadas no banco de dados do sistema.
                            </div>


                            <button type="submit">
                                Salvar configuração
                            </button>

                        </form>

                    </div>
                <?php endif; ?>


                <!-- =================================================
                     RESUMO + HISTÓRICO
                ================================================== -->

                <div>

                    <div class="alertas-resumo">
                        <!-- Box Total (Azul) -->
                        <div class="resumo-box alerta-total">
                            <div class="resumo-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                            </div>
                            <span>Total de alertas emitidos</span>
                            <strong id="resumo-total-alertas">0</strong>
                        </div>

                        <!-- Box Crítico (Vermelho) -->
                        <div class="resumo-box alerta-critico">
                            <div class="resumo-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                            </div>
                            <span>Alertas críticos</span>
                            <strong id="resumo-alertas-criticos">0</strong>
                        </div>

                        <!-- Box Aviso (Laranja) -->
                        <div class="resumo-box alerta-aviso">
                            <div class="resumo-icon">
                            <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                            </div>
                            <span>Alertas de aviso</span>
                            <strong id="resumo-alertas-aviso">0</strong>
                        </div>
                    </div>



                    <div class="historico-alertas">

                        <div
                            class="historico-header"
                            style="
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                flex-wrap: wrap;
                                gap: 10px;
                            "
                        >

                            <h2>
                                Alertas emitidos pelo site
                            </h2>


                            <button
                                type="button"
                                class="btn-limpar-alertas"
                                id="btn-limpar-historico"
                                onclick="confirmarLimpezaAlertas()"
                                style="
                                    background-color: #e74c3c;
                                    color: white;
                                    border: none;
                                    padding: 6px 12px;
                                    border-radius: 4px;
                                    cursor: pointer;
                                    font-size: 13px;
                                    font-weight: 600;
                                "
                            >
                                🗑 Limpar Histórico
                            </button>


                            <div
                                class="filtros-alerta"
                                style="width: 100%;"
                            >

                                <button
                                    type="button"
                                    class="filtro-btn ativo"
                                    data-filtro="todos"
                                    onclick="filtrarAlertas('todos')"
                                >
                                    Todos
                                </button>

                                <button
                                    type="button"
                                    class="filtro-btn"
                                    data-filtro="validade"
                                    onclick="filtrarAlertas('validade')"
                                >
                                    Validade
                                </button>

                                <button
                                    type="button"
                                    class="filtro-btn"
                                    data-filtro="estoque"
                                    onclick="filtrarAlertas('estoque')"
                                >
                                    Estoque
                                </button>

                                <button
                                    type="button"
                                    class="filtro-btn"
                                    data-filtro="critico"
                                    onclick="filtrarAlertas('critico')"
                                >
                                    Críticos
                                </button>

                            </div>

                        </div>


                        <div
                            class="lista-alertas-completa"
                            id="lista-alertas-completa"
                        ></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<!-- JS da página -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="module" src="../js/alertas.js"></script>

</body>
</html>