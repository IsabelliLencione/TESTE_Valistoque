<?php

require_once __DIR__ . "/../php/config.php";

date_default_timezone_set("America/Sao_Paulo");

/*
 * Mantém o MySQL exibindo timestamps em horário de São Paulo.
 * Isso ajuda a evitar diferença de 3 horas no histórico dos alertas.
 */
try {
    $pdo->exec("SET time_zone = '-03:00'");
} catch (Throwable $e) {
    // Não interrompe a página caso o servidor MySQL não permita alterar o time_zone.
}

$mensagem_feedback = null;
$tipo_feedback = "";

/* =========================================================
   SALVAR CONFIGURAÇÕES DOS ALERTAS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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


    /* =====================================================
       VALIDAR PREENCHIMENTO
    ===================================================== */

    if (
        $dias_validade === "" ||
        $unidades_central === "" ||
        $unidades_prateleira === "" ||
        $intervalo === ""
    ) {
        header("Location: alertas.php?erro=preenchimento");
        exit;
    }


    /* =====================================================
       CONVERTER PARA INTEIRO
    ===================================================== */

    $dias_validade = (int) $dias_validade;
    $unidades_central = (int) $unidades_central;
    $unidades_prateleira = (int) $unidades_prateleira;
    $intervalo = (int) $intervalo;


    /* =====================================================
       VALIDAR VALORES
    ===================================================== */

    if (
        $dias_validade < 1 ||
        $unidades_central < 1 ||
        $unidades_prateleira < 1 ||
        $intervalo < 1
    ) {
        header("Location: alertas.php?erro=valores_invalidos");
        exit;
    }


    try {

        /* =================================================
           VERIFICAR SE A CONFIGURAÇÃO EXISTE
        ================================================= */

        $stmt = $pdo->prepare("
            SELECT id
            FROM config_alertas
            WHERE id = 1
            LIMIT 1
        ");

        $stmt->execute();

        $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);


        /* =================================================
           ATUALIZAR
        ================================================= */

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

        }

        /* =================================================
           INSERIR
        ================================================= */

        else {

            $stmt = $pdo->prepare("
                INSERT INTO config_alertas
                (
                    id,
                    dias_antes_validade,
                    unidades_minimas_central,
                    unidades_minimas_prateleira,
                    intervalo_minutos,
                    exibir_popups
                )
                VALUES
                (
                    1, ?, ?, ?, ?, ?
                )
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

        error_log("Erro ao salvar configurações de alertas: " . $e->getMessage());

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

    error_log("Erro ao buscar configurações de alertas: " . $e->getMessage());
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


                <!-- =================================================
                     RESUMO + HISTÓRICO
                ================================================== -->

                <div>

                    <div class="alertas-resumo">
                        <!-- Box Total (Azul) -->
                        <div class="resumo-box alerta-total">
                            <div class="resumo-icon">🔔</div>
                            <span>Total de alertas emitidos</span>
                            <strong id="resumo-total-alertas">0</strong>
                        </div>

                        <!-- Box Crítico (Vermelho) -->
                        <div class="resumo-box alerta-critico">
                            <div class="resumo-icon">🛑</div>
                            <span>Alertas críticos</span>
                            <strong id="resumo-alertas-criticos">0</strong>
                        </div>

                        <!-- Box Aviso (Laranja) -->
                        <div class="resumo-box alerta-aviso">
                            <div class="resumo-icon">⚠️</div>
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
