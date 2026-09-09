<<<<<<< HEAD
<!DOCTYPE html>

<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    ```
    <title>Alertas - Valistoque</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/alertas.css">
    ```

</head>

<body>

    ```
    <!-- Menu -->
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
                <a href="prateleira.html">Prateleiras</a>
            </li>

            <li>
                <a href="estoque.php">Estoque Central</a>
            </li>

            <li>
                <a href="alertas.html">Alertas</a>
            </li>

            <li>
                <a href="ListaUsuarios.html">Usuários</a>
            </li>

            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.html">Perfil</a>
            </li>
        </ul>

    </nav>

    <main>

        <!-- Área dos alertas -->
        <div id="secao-alertas">

            <div class="alertas-page">

                <div class="secao-header">
                    <h1>Central de Alertas</h1>
                </div>

                <div class="alertas-grid">

                    <!-- Configuração dos alertas -->
                    <div class="alertas-card">

                        <h2>Configuração dos avisos</h2>

                        <p>
                            Configure quando o sistema deve mostrar um aviso
                            sobre produtos próximos da validade ou com pouco estoque.
                        </p>

                        <form id="form-config-alertas" onsubmit="salvarConfiguracoesAlertas(event)">

                            <label for="dias-alerta-validade">
                                Dias antes da validade para avisar
                            </label>

                            <input type="number" id="dias-alerta-validade" min="1" value="30" required>

                            <label for="caixas-alerta-estoque">
                                Quantidade mínima de caixas
                            </label>

                            <input type="number" id="caixas-alerta-estoque" min="1" value="10" required>

                            <label for="intervalo-alerta">
                                Intervalo para verificar os alertas
                            </label>

                            <select id="intervalo-alerta" required>
                                <option value="5">5 minutos</option>
                                <option value="10">10 minutos</option>
                                <option value="15">15 minutos</option>
                                <option value="30">30 minutos</option>
                                <option value="60">60 minutos</option>
                            </select>

                            <div class="texto-auxiliar">
                                As configurações ficam salvas no navegador.
                            </div>

                            <button type="submit">
                                Salvar configuração
                            </button>

                        </form>

                    </div>


                    <!-- Resumo dos alertas -->
                    <div>

                        <div class="alertas-resumo">

                            <div class="resumo-box">
                                <span>Total de alertas</span>
                                <strong id="resumo-total-alertas">0</strong>
                            </div>

                            <div class="resumo-box alerta-critico">
                                <span>Alertas críticos</span>
                                <strong id="resumo-alertas-criticos">0</strong>
                            </div>

                            <div class="resumo-box alerta-aviso">
                                <span>Alertas de aviso</span>
                                <strong id="resumo-alertas-aviso">0</strong>
                            </div>

                        </div>


                        <!-- Histórico dos alertas -->
                        <div class="historico-alertas">

                            <div class="historico-header">

                                <h2>Histórico de alertas</h2>

                                <div class="filtros-alerta">

                                    <button type="button" class="filtro-btn ativo" data-filtro="todos"
                                        onclick="filtrarAlertas('todos')">
                                        Todos
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="validade"
                                        onclick="filtrarAlertas('validade')">
                                        Validade
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="estoque"
                                        onclick="filtrarAlertas('estoque')">
                                        Estoque
                                    </button>

                                    <button type="button" class="filtro-btn" data-filtro="critico"
                                        onclick="filtrarAlertas('critico')">
                                        Críticos
                                    </button>

                                </div>

                            </div>

                            <div class="lista-alertas-completa" id="lista-alertas-completa"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <script type="module" src="../js/alertas.js"></script>
    ```

</body>

</html>
=======
<?php
// Ative a exibição de erros temporariamente se precisar debugar em ambiente de desenvolvimento:
// ini_set('display_errors', 1); error_reporting(E_ALL);

require_once __DIR__ . "/../php/config.php";

$mensagem_feedback = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recebe e limpa os valores removendo espaços em branco
    $dias_validade = isset($_POST["dias_antes_validade"]) ? trim($_POST["dias_antes_validade"]) : null;
    $caixas_central = isset($_POST["caixas_minimas_central"]) ? trim($_POST["caixas_minimas_central"]) : null;
    $caixas_prateleira = isset($_POST["caixas_minimas_prateleira"]) ? trim($_POST["caixas_minimas_prateleira"]) : null;
    $intervalo = isset($_POST["intervalo_minutos"]) ? trim($_POST["intervalo_minutos"]) : null;

    // Se algum campo estiver estritamente vazio ou nulo
    if ($dias_validade === "" || $caixas_central === "" || $caixas_prateleira === "" || $intervalo === "") {
        header("Location: alertas.php?erro=preenchimento");
        exit;
    }

    // Força a conversão para inteiros
    $dias_validade = (int)$dias_validade;
    $caixas_central = (int)$caixas_central;
    $caixas_prateleira = (int)$caixas_prateleira;
    $intervalo = (int)$intervalo;

    // Valida se os números fazem sentido
    if ($dias_validade < 1 || $caixas_central < 1 || $caixas_prateleira < 1 || $intervalo < 1) {
        header("Location: alertas.php?erro=valores_invalidos");
        exit;
    }

    try {
        // Verifica se o registro de ID 1 existe
        $sql = "SELECT id FROM alertas WHERE id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($configuracao) {
            // Executa o UPDATE assegurando que as colunas batem com seu banco de dados
            $sql = "UPDATE alertas 
                    SET dias_antes_validade = ?, 
                        caixas_minimas_central = ?, 
                        caixas_minimas_prateleira = ?, 
                        intervalo_minutos = ? 
                    WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias_validade, $caixas_central, $caixas_prateleira, $intervalo]);
        } else {
            // Se for a primeira vez inserindo dados no sistema
            $sql = "INSERT INTO alertas (id, dias_antes_validade, caixas_minimas_central, caixas_minimas_prateleira, intervalo_minutos) 
                    VALUES (1, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias_validade, $caixas_central, $caixas_prateleira, $intervalo]);
        }

        header("Location: alertas.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        // Em caso de falha de coluna ou tabela inexistente, grava o erro na URL para diagnosticar
        $msg_erro = urlencode($e->getMessage());
        header("Location: alertas.php?erro=banco&detalhe=" . $msg_erro);
        exit;
    }
}

// Resgata os dados para exibir de volta no formulário
try {
    $stmt = $pdo->query("SELECT * FROM alertas WHERE id = 1");
    $dados_atuais = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dados_atuais = null;
}

// Captura mensagens vindas da URL para exibir alertas na tela
if (isset($_GET['sucesso'])) {
    $mensagem_feedback = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;'>Configurações gravadas com sucesso no banco!</div>";
} elseif (isset($_GET['erro'])) {
    $detalhe = isset($_GET['detalhe']) ? "<br><small style='color:#721c24;'>Motivo: " . htmlspecialchars($_GET['detalhe']) . "</small>" : "";
    $mensagem_feedback = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;'>Erro ao salvar: verifique os dados preenchidos.{$detalhe}</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/alertas.css">
</head>
<body>
<nav class="nav"> 
    <div class="header-nav">Valistoque</div> 
    <ul> 
        <li><a href="relatorio.html">Relatório</a></li> 
        <li><a href="produtos.php">Cadastro Produtos</a></li> 
        <li><a href="usuarios.php">Cadastro Usuários</a></li> 
        <li><a href="prateleira.html">Prateleiras</a></li> 
        <li><a href="estoque.php">Estoque Central</a></li> 
        <li><a href="alertas.php">Alertas</a></li> 
        <li><a href="ListaUsuarios.php">Usuários</a></li>
        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.html">Perfil</a>
        </li> 
    </ul> 
</nav>

<main>
    <div id="secao-alertas">
        <div class="alertas-page">
            <div class="secao-header">
                <h1>Central de Alertas</h1>
                <?php echo $mensagem_feedback; ?>
            </div>

            <div class="alertas-grid">
                <div class="alertas-card">
                    <h2>Configuração dos avisos</h2>
                    <p>Defina quando o sistema deve avisar sobre validade próxima e estoque acabando.</p>
                    
                    <!-- O action aponta para si mesmo garantindo que o POST chegue ao topo deste arquivo -->
                    <form method="POST" action="alertas.php" id="form-config-alertas">
                        
                        <label for="dias-alerta-validade">Dias antes da validade para alertar</label>
                        <input type="number" id="dias-alerta-validade" name="dias_antes_validade" min="1" required 
                               value="<?php echo htmlspecialchars($dados_atuais['dias_antes_validade'] ?? '30'); ?>">

                        <label for="caixas-alerta-central">Mínimo de caixas (Estoque Central)</label>
                        <input type="number" id="caixas-alerta-central" name="caixas_minimas_central" min="1" required 
                               value="<?php echo htmlspecialchars($dados_atuais['caixas_minimas_central'] ?? '10'); ?>">

                        <label for="caixas-alerta-prateleira">Mínimo de caixas (Prateleiras)</label>
                        <input type="number" id="caixas-alerta-prateleira" name="caixas_minimas_prateleira" min="1" required 
                               value="<?php echo htmlspecialchars($dados_atuais['caixas_minimas_prateleira'] ?? '5'); ?>">

                        <label for="intervalo-alerta">Revisar alertas automaticamente a cada</label>
                        <select id="intervalo-alerta" name="intervalo_minutos" required>
                            <?php 
                            $opcoes_minutos = [5, 10, 15, 30, 60];
                            $intervalo_salvo = (int)($dados_atuais['intervalo_minutos'] ?? 15);
                            foreach ($opcoes_minutos as $min): 
                                $selected = ($intervalo_salvo === $min) ? 'selected' : '';
                                echo "<option value='$min' $selected>$min minutos</option>";
                            endforeach; 
                            ?>
                        </select>
                        
                        <div class="texto-auxiliar">As configurações ficam salvas centralizadas no banco de dados do sistema.</div>
                        <button type="submit">Salvar configuração</button>
                    </form>
                </div>

                <div>
                    <div class="alertas-resumo">
                        <div class="resumo-box">
                            <span>Total de alertas emitidos</span>
                            <strong id="resumo-total-alertas">0</strong>
                        </div>
                        <div class="resumo-box alerta-critico">
                            <span>Alertas críticos</span>
                            <strong id="resumo-alertas-criticos">0</strong>
                        </div>
                        <div class="resumo-box alerta-aviso">
                            <span>Alertas de aviso</span>
                            <strong id="resumo-alertas-aviso">0</strong>
                        </div>
                    </div>

                    <div class="historico-alertas">
                        <div class="historico-header">
                            <h2>Alertas emitidos pelo site</h2>
                            <div class="filtros-alerta">
                                <button type="button" class="filtro-btn ativo" data-filtro="todos" onclick="filtrarAlertas('todos')">Todos</button>
                                <button type="button" class="filtro-btn" data-filtro="validade" onclick="filtrarAlertas('validade')">Validade</button>
                                <button type="button" class="filtro-btn" data-filtro="estoque" onclick="filtrarAlertas('estoque')">Estoque</button>
                                <button type="button" class="filtro-btn" data-filtro="critico" onclick="filtrarAlertas('critico')">Críticos</button>
                            </div>
                        </div>
                        <div class="lista-alertas-completa" id="lista-alertas-completa"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="module" src="../js/alertas.js"></script>
</body>
</html>
>>>>>>> 3dbf752d741a8e6050294ff7096a859b2b33e3b2
