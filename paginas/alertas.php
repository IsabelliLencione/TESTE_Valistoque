<?php
// Ative a exibição de erros temporariamente se precisar debugar em ambiente de desenvolvimento:
// ini_set('display_errors', 1); error_reporting(E_ALL);

require_once __DIR__ . "/../php/config.php";


$mensagem_feedback = null;
$tipo_feedback = ""; // Guardará a classe CSS (sucesso ou erro)

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recebe e limpa os valores removendo espaços em branco
        $dias_validade = isset($_POST["dias_antes_validade"]) ? trim($_POST["dias_antes_validade"]) : null;
        $unidades_central = isset($_POST["unidades_minimas_central"]) ? trim($_POST["unidades_minimas_central"]) : null;
        $unidades_prateleira = isset($_POST["unidades_minimas_prateleira"]) ? trim($_POST["unidades_minimas_prateleira"]) : null;
        $intervalo = isset($_POST["intervalo_minutos"]) ? trim($_POST["intervalo_minutos"]) : null;
        
    // Se algum campo estiver estritamente vazio ou nulo
    if ($dias_validade === "" || $unidades_central === "" || $unidades_prateleira === "" || $intervalo === "") {
        header("Location: alertas.php?erro=preenchimento");
        exit;
    }

    // Força a conversão para inteiros
    $dias_validade = (int)$dias_validade;
    $unidades_central = (int)$unidades_central;
    $unidades_prateleira = (int)$unidades_prateleira;
    $intervalo = (int)$intervalo;

    // Valida se os números fazem sentido
    if ($dias_validade < 1 || $unidades_central < 1 || $unidades_prateleira < 1 || $intervalo < 1) {
        header("Location: alertas.php?erro=valores_invalidos");
        exit;
    }

    try {
        // Verifica se o registro de ID 1 existe (Usando a tabela 'alertas')
        $sql = "SELECT id FROM alertas WHERE id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $configuracao = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($configuracao) {
            // Executa o UPDATE assegurando que as colunas batem com seu banco de dados
            $sql = "UPDATE alertas 
                    SET dias_antes_validade = ?, 
                        unidades_minimas_central = ?, 
                        unidades_minimas_prateleira = ?, 
                        intervalo_minutos = ? 
                    WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias_validade, $unidades_central, $unidades_prateleira, $intervalo]);
        } else {
            // Se for a primeira vez inserindo dados no sistema
            $sql = "INSERT INTO alertas (id, dias_antes_validade, unidades_minimas_central, unidades_minimas_prateleira, intervalo_minutos) 
                    VALUES (1, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias_validade, $unidades_central, $unidades_prateleira, $intervalo]);
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
    // Corrigido para buscar sempre da tabela 'alertas'
    $stmt = $pdo->query("SELECT * FROM alertas WHERE id = 1");
    $dados_atuais = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dados_atuais = null;
}


// Captura mensagens vindas da URL para exibir alertas na tela
if (isset($_GET['sucesso'])) {
    $tipo_feedback = "feedback-sucesso";
    $mensagem_feedback = "Configurações gravadas com sucesso no banco!";
} elseif (isset($_GET['erro'])) {
    $tipo_feedback = "feedback-erro";
    $detalhe = isset($_GET['detalhe']) ? "<br><small>Motivo: " . htmlspecialchars($_GET['detalhe']) . "</small>" : "";
    $mensagem_feedback = "Erro ao salvar: verifique os dados preenchidos." . $detalhe;
}




// PROCESSAMENTO DOS ALERTAS AUTOMÁTICOS
try {
   // 1. BUSCAR CONFIGURAÇÕES DOS ALERTAS (Tabela corrigida para 'alertas')
    $stmtConfig = $pdo->query("
        SELECT *
        FROM alertas
        WHERE id = 1
        LIMIT 1
    ");

    $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        // Evita travar a página completamente caso o usuário acabe de instalar o sistema
        $dias_limite_validade = 0;
        $minimo_unidades_central = 0;
        $minimo_unidades_prateleira = 0;
        $intervalo_minutos = 0;
    } else {
        $dias_limite_validade       = (int) $config['dias_antes_validade'];
        $minimo_unidades_central    = (int) $config['unidades_minimas_central'];
        $minimo_unidades_prateleira = (int) $config['unidades_minimas_prateleira'];
        $intervalo_minutos          = (int) $config['intervalo_minutos'];
    }
    // =====================================================

    // 2. BUSCAR PRODUTOS DO ESTOQUE
    $sqlEstoque = "
        SELECT 
            e.*,
            IFNULL(p.quantidade_atual, 0) AS qtd_prateleira,
            IFNULL(p.id_prat, 0) AS id_prat
        FROM estoque e
        LEFT JOIN prateleiras p
            ON e.id_estoque = p.id_estoque
    ";

    $stmtEstoque = $pdo->query($sqlEstoque);
    $produtos = $stmtEstoque->fetchAll(PDO::FETCH_ASSOC);

    // =====================================================
    // 3. VERIFICAR CADA PRODUTO
   foreach ($produtos as $prod) {

        $id_estoque = $prod['id_estoque'];
        $nome       = $prod['nome_produto'];


       // =================================================
        // REGRA 1 - VALIDADE
        // =================================================
        if (!empty($prod['data_validade'])) {

            $data_validade = new DateTime($prod['data_validade']);
            $data_validade->setTime(0, 0);
            
            $hoje = new DateTime();
            $hoje->setTime(0, 0);

            $diferenca = $hoje->diff($data_validade);
            $dias_para_vencer = (int) $diferenca->format("%r%a");

            if ($dias_para_vencer <= $dias_limite_validade) {

                if ($dias_para_vencer < 0) {
                    $situacao = "está vencido há " . abs($dias_para_vencer) . " dias";
                } elseif ($dias_para_vencer == 0) {
                    $situacao = "vence hoje";
                } else {
                    $situacao = "vence em " . $dias_para_vencer . " dias";
                }

                $mensagem = "O produto '$nome' (Lote: {$prod['lote']}) $situacao. Data de validade: {$prod['data_validade']}.";

                verificarEDispararAlerta(
                    $pdo,
                    $id_estoque,
                    'Validade Próxima',
                    $mensagem,
                    $intervalo_minutos
                );
            }
        }

        // =================================================
        // REGRA 2 - ESTOQUE DA PRATELEIRA
        // =================================================

         $qtd_prateleira = (int) $prod['qtd_prateleira'];

        // Corrigido de $minimo_itens_prateleira para $minimo_unidades_prateleira
        if ($qtd_prateleira <= $minimo_unidades_prateleira) {

            $mensagem = "Atenção: a prateleira do produto '$nome' está com estoque baixo ({$qtd_prateleira} unidades). Mínimo exigido: {$minimo_unidades_prateleira} unidades.";

            verificarEDispararAlerta(
                $pdo,
                $id_estoque,
                'Estoque Baixo Prateleira',
                $mensagem,
                $intervalo_minutos
            );
        }


        // =================================================
        // REGRA 3 - ESTOQUE CENTRAL
        // =================================================
      $total_itens = (int) $prod['total_itens'];

        if ($total_itens <= $minimo_unidades_central) {

            $mensagem = "Atenção: o estoque central de '$nome' atingiu o limite crítico de {$total_itens} caixas. Mínimo exigido: {$minimo_unidades_central} caixas.";

            verificarEDispararAlerta(
                $pdo,
                $id_estoque,
                'Estoque Central Baixo',
                $mensagem,
                $intervalo_minutos
            );
        }
    }

} catch (PDOException $e) {
    // Trata erros de leitura do estoque silenciosamente ou grava em log se necessário
    // error_log($e->getMessage());
}

// =========================================================
// FUNÇÃO PARA EVITAR ALERTAS REPETIDOS 
// =========================================================

function verificarEDispararAlerta(
    $pdo,
    $id_estoque,
    $tipo_alerta,
    $mensagem,
    $intervalo_minutos
) {
    // IMPORTANTE: Alterado o nome da tabela para 'historico_alertas' 
    // para não conflitar com a tabela de configurações globais do sistema.
    $sqlCheck = "
        SELECT data_alerta
        FROM historico_alertas
        WHERE id_estoque = ?
          AND tipo_alerta = ?
        ORDER BY data_alerta DESC
        LIMIT 1
    ";

    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([
        $id_estoque,
        $tipo_alerta
    ]);

    $ultimoAlerta = $stmtCheck->fetch(PDO::FETCH_ASSOC);


    // =====================================================
    // VERIFICAR INTERVALO
    // =====================================================

    if ($ultimoAlerta) {

        $dataUltimo = new DateTime($ultimoAlerta['data_alerta']);
        $agora = new DateTime();

        $diff = $agora->getTimestamp() - $dataUltimo->getTimestamp();
        $minutosPassados = $diff / 60;

        // Se o tempo passado for menor que o configurado, ignora o disparo
        if ($minutosPassados < $intervalo_minutos) {
            return;
        }
    }


    // =====================================================
    // GRAVAR NOVO ALERTA NO HISTÓRICO
    // =====================================================

    $sqlInsert = "
        INSERT INTO historico_alertas
        (
            id_estoque,
            tipo_alerta,
            mensagem,
            data_alerta
        )
        VALUES
        (
            ?,
            ?,
            ?,
            CURRENT_TIMESTAMP
        )
    ";

    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        $id_estoque,
        $tipo_alerta,
        $mensagem
    ]);
}










// CREATE TABLE IF NOT EXISTS `config_alertas` (
//     `id` INT NOT NULL DEFAULT 1,
//     `dias_antes_validade` INT NOT NULL,
//     `unidades_minimas_central` INT NOT NULL,
//     `unidades_minimas_prateleira` INT NOT NULL,
//     `intervalo_minutos` INT NOT NULL,
//     PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

// -- 2. Tabela para registrar o histórico de notificações de cada produto
// CREATE TABLE IF NOT EXISTS `historico_alertas` (
//     `id` INT AUTO_INCREMENT PRIMARY KEY,
//     `id_estoque` INT NOT NULL,
//     `tipo_alerta` VARCHAR(50) NOT NULL, -- Ex: 'Validade Próxima', 'Estoque Central Baixo'
//     `mensagem` TEXT NOT NULL,
//     `data_alerta` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     -- Substitua 'estoque' e 'id_estoque' pelos nomes exatos da sua tabela principal de produtos, se necessário:
//     FOREIGN KEY (`id_estoque`) REFERENCES `estoque`(`id_estoque`) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
   
                        <?php if ($mensagem_feedback): ?>
                            <div class="card-feedback <?php echo $tipo_feedback; ?>">
                                <div class="feedback-icone"></div>
                                <div class="feedback-conteudo">
                                    <?php echo $mensagem_feedback; ?>
                                </div>
                            </div>
                        <?php endif; ?>

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

                        <label for="unidades-alerta-central">Mínimo de unidade (Estoque Central)</label>
                        <input type="number" id="unidades-alerta-central" name="unidades_minimas_central" min="1" required 
                               value="<?php echo htmlspecialchars($dados_atuais['unidades_minimas_central'] ?? '10'); ?>">

                        <label for="unidades-alerta-prateleira">Mínimo de unidades (Prateleiras)</label>
                        <input type="number" id="unidades-alerta-prateleira" name="unidades_minimas_prateleira" min="1" required 
                               value="<?php echo htmlspecialchars($dados_atuais['unidades_minimas_prateleira'] ?? '5'); ?>">

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
