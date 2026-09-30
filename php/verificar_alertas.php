<?php

/*
 * Endpoint responsável por:
 * 1. Ler as configurações.
 * 2. Verificar o estoque.
 * 3. Criar novos alertas.
 * 4. Retornar os novos alertas em JSON.
 * 5. Retornar o histórico e os contadores.
 * 6. Limpar o histórico quando receber POST com action=limpar_historico.
 */

ob_start();

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config.php";

date_default_timezone_set("America/Sao_Paulo");

try {

    /*
     * Faz o MySQL trabalhar em UTC-3 para que CURRENT_TIMESTAMP,
     * NOW() e a leitura da coluna TIMESTAMP usem o horário de São Paulo.
     */
    $pdo->exec("SET time_zone = '-03:00'");


    /* =====================================================
       LIMPAR HISTÓRICO
    ===================================================== */

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["action"]) &&
        $_POST["action"] === "limpar_historico"
    ) {

        $stmtDelete = $pdo->prepare("
            DELETE FROM alertas
        ");

        $stmtDelete->execute();

        ob_clean();

        echo json_encode(
            [
                "sucesso" => true,
                "mensagem" => "Histórico de alertas limpo com sucesso."
            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /* =====================================================
       1. BUSCAR CONFIGURAÇÕES
    ===================================================== */

    $stmtConfig = $pdo->query("
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

    $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);


    if (!$config) {

        $config = [
            "dias_antes_validade" => 30,
            "unidades_minimas_central" => 10,
            "unidades_minimas_prateleira" => 5,
            "intervalo_minutos" => 15,
            "exibir_popups" => 1
        ];
    }


    $diasLimite =
        (int) $config["dias_antes_validade"];

    $minimoCentral =
        (int) $config["unidades_minimas_central"];

    $minimoPrateleira =
        (int) $config["unidades_minimas_prateleira"];

    $intervaloMinutos =
        (int) $config["intervalo_minutos"];

    $exibirPopups =
        (int) $config["exibir_popups"];


    /* =====================================================
       2. BUSCAR ESTOQUE ATIVO
    ===================================================== */

    $sqlEstoque = "
        SELECT
            e.id_estoque,
            e.nome_produto,
            e.lote,
            e.data_validade,
            e.total_itens,
            e.peso_un,

            COALESCE(
                (
                    SELECT SUM(p.quantidade_atual)
                    FROM prateleiras p
                    WHERE p.id_estoque = e.id_estoque
                ),
                0
            ) AS qtd_prateleira

        FROM estoque e

        WHERE e.ativo = 1

        ORDER BY e.id_estoque DESC
    ";

    $stmtEstoque = $pdo->query($sqlEstoque);

    $produtos = $stmtEstoque->fetchAll(PDO::FETCH_ASSOC);


    /* =====================================================
       3. PREPARAR CONSULTA DO ÚLTIMO ALERTA
    ===================================================== */

    $stmtUltimoAlerta = $pdo->prepare("
        SELECT data_alerta
        FROM alertas
        WHERE id_estoque = ?
          AND tipo_alerta = ?
        ORDER BY data_alerta DESC
        LIMIT 1
    ");


    /* =====================================================
       4. PREPARAR INSERÇÃO DO ALERTA
    ===================================================== */

    $stmtInserirAlerta = $pdo->prepare("
        INSERT INTO alertas
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
    ");


    $novosAlertas = [];


    /* =====================================================
       FUNÇÃO PARA REGISTRAR ALERTA
    ===================================================== */

    function registrarAlerta(
        PDO $pdo,
        PDOStatement $stmtUltimoAlerta,
        PDOStatement $stmtInserirAlerta,
        array &$novosAlertas,
        int $idEstoque,
        string $tipo,
        string $mensagem,
        int $intervaloMinutos
    ): void {

        $stmtUltimoAlerta->execute([
            $idEstoque,
            $tipo
        ]);


        $ultimoAlerta =
            $stmtUltimoAlerta->fetch(PDO::FETCH_ASSOC);


        /*
         * Se existe um alerta anterior, verifica quanto tempo passou.
         */
        if ($ultimoAlerta) {

            $dataUltimo =
                new DateTime(
                    $ultimoAlerta["data_alerta"],
                    new DateTimeZone("America/Sao_Paulo")
                );

            $agora =
                new DateTime(
                    "now",
                    new DateTimeZone("America/Sao_Paulo")
                );

            $segundosPassados =
                $agora->getTimestamp() -
                $dataUltimo->getTimestamp();

            $minutosPassados =
                $segundosPassados / 60;


            if ($minutosPassados < $intervaloMinutos) {
                return;
            }
        }


        /*
         * Gravar novo alerta.
         */
        $stmtInserirAlerta->execute([
            $idEstoque,
            $tipo,
            $mensagem
        ]);


        /*
         * Define o nível que o frontend usará.
         *
         * CRÍTICO:
         * - Produto Vencido
         * - Estoque Central Baixo
         *
         * AVISO:
         * - Validade Próxima
         * - Estoque Baixo Prateleira
         */
        $nivel = "aviso";

        if (
            $tipo === "Produto Vencido" ||
            $tipo === "Estoque Central Baixo"
        ) {
            $nivel = "critico";
        }


        $novosAlertas[] = [

            "id_alerta" =>
                (int) $pdo->lastInsertId(),

            "id_estoque" =>
                $idEstoque,

            "tipo_alerta" =>
                $tipo,

            "mensagem" =>
                $mensagem,

            "data_alerta" =>
                date("d/m/Y H:i:s"),

            "nivel" =>
                $nivel
        ];
    }


    /* =====================================================
       5. VERIFICAR CADA PRODUTO
    ===================================================== */

    foreach ($produtos as $prod) {

        $idEstoque =
            (int) $prod["id_estoque"];

        $nome =
            $prod["nome_produto"];

        $lote =
            $prod["lote"];

        $totalItens =
            (int) $prod["total_itens"];

        $qtdPrateleira =
            (int) $prod["qtd_prateleira"];


        /* =================================================
           REGRA 1 - VALIDADE
        ================================================= */

        if (!empty($prod["data_validade"])) {

            try {

                $dataValidade =
                    new DateTime(
                        $prod["data_validade"],
                        new DateTimeZone("America/Sao_Paulo")
                    );

                $dataValidade->setTime(
                    0,
                    0,
                    0
                );


                $hoje =
                    new DateTime(
                        "now",
                        new DateTimeZone("America/Sao_Paulo")
                    );

                $hoje->setTime(
                    0,
                    0,
                    0
                );


                $diasParaVencer =
                    (int) $hoje
                        ->diff($dataValidade)
                        ->format("%r%a");


                /*
                 * PRODUTO VENCIDO
                 */
                if ($diasParaVencer < 0) {

                    $mensagem =
                        "O produto '$nome' "
                        . "(Lote: $lote) "
                        . "está vencido há "
                        . abs($diasParaVencer)
                        . " dias. "
                        . "Data de validade: "
                        . $prod["data_validade"]
                        . ".";


                    registrarAlerta(
                        $pdo,
                        $stmtUltimoAlerta,
                        $stmtInserirAlerta,
                        $novosAlertas,
                        $idEstoque,
                        "Produto Vencido",
                        $mensagem,
                        $intervaloMinutos
                    );
                }

                /*
                 * VALIDADE PRÓXIMA
                 */
                elseif ($diasParaVencer <= $diasLimite) {

                    if ($diasParaVencer === 0) {

                        $situacao =
                            "vence hoje";

                    } else {

                        $situacao =
                            "vence em "
                            . $diasParaVencer
                            . " dias";
                    }


                    $mensagem =
                        "O produto '$nome' "
                        . "(Lote: $lote) "
                        . "$situacao. "
                        . "Data de validade: "
                        . $prod["data_validade"]
                        . ".";


                    registrarAlerta(
                        $pdo,
                        $stmtUltimoAlerta,
                        $stmtInserirAlerta,
                        $novosAlertas,
                        $idEstoque,
                        "Validade Próxima",
                        $mensagem,
                        $intervaloMinutos
                    );
                }

            } catch (Exception $e) {

                error_log(
                    "Erro ao verificar validade do estoque "
                    . $idEstoque
                    . ": "
                    . $e->getMessage()
                );
            }
        }


        /* =================================================
           REGRA 2 - ESTOQUE DA PRATELEIRA
        ================================================= */

        if (
            $qtdPrateleira <=
            $minimoPrateleira
        ) {

            $mensagem =
                "Atenção: a prateleira "
                . "do produto '$nome' está com "
                . $qtdPrateleira
                . " unidades. "
                . "Mínimo configurado: "
                . $minimoPrateleira
                . " unidades.";


            registrarAlerta(
                $pdo,
                $stmtUltimoAlerta,
                $stmtInserirAlerta,
                $novosAlertas,
                $idEstoque,
                "Estoque Baixo Prateleira",
                $mensagem,
                $intervaloMinutos
            );
        }


        /* =================================================
           REGRA 3 - ESTOQUE CENTRAL
        ================================================= */

        if (
            $totalItens <=
            $minimoCentral
        ) {

            $mensagem =
                "Atenção: o estoque central "
                . "de '$nome' está com "
                . $totalItens
                . " unidades. "
                . "Mínimo configurado: "
                . $minimoCentral
                . " unidades.";


            registrarAlerta(
                $pdo,
                $stmtUltimoAlerta,
                $stmtInserirAlerta,
                $novosAlertas,
                $idEstoque,
                "Estoque Central Baixo",
                $mensagem,
                $intervaloMinutos
            );
        }
    }


    /* =====================================================
       6. BUSCAR HISTÓRICO
    ===================================================== */

    $stmtHistorico = $pdo->query("
        SELECT
            id_alerta,
            id_estoque,
            tipo_alerta,
            mensagem,
            DATE_FORMAT(
                data_alerta,
                '%d/%m/%Y %H:%i:%s'
            ) AS data_alerta
        FROM alertas
        ORDER BY data_alerta DESC
        LIMIT 100
    ");

    $historico =
        $stmtHistorico->fetchAll(PDO::FETCH_ASSOC);


    /* =====================================================
       7. RESUMO GERAL
    ===================================================== */

    $stmtResumo = $pdo->query("
        SELECT
            COUNT(*) AS total_alertas,

            COALESCE(
                SUM(
                    CASE
                        WHEN tipo_alerta IN (
                            'Produto Vencido',
                            'Estoque Central Baixo'
                        )
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS alertas_criticos,

            COALESCE(
                SUM(
                    CASE
                        WHEN tipo_alerta NOT IN (
                            'Produto Vencido',
                            'Estoque Central Baixo'
                        )
                        THEN 1
                        ELSE 0
                    END
                ),
                0
            ) AS alertas_aviso

        FROM alertas
    ");

    $resumo =
        $stmtResumo->fetch(PDO::FETCH_ASSOC);


    /* =====================================================
       8. RETORNAR JSON
    ===================================================== */

    ob_clean();

    echo json_encode(
        [
            "sucesso" => true,

            "exibir_popups" =>
                $exibirPopups,

            "intervalo_minutos" =>
                $intervaloMinutos,

            "novos" =>
                $novosAlertas,

            "historico" =>
                $historico,

            "resumo" => [
                "total" =>
                    (int) $resumo["total_alertas"],

                "criticos" =>
                    (int) $resumo["alertas_criticos"],

                "avisos" =>
                    (int) $resumo["alertas_aviso"]
            ]
        ],
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {

    error_log(
        "Erro em verificar_alertas.php: "
        . $e->getMessage()
    );

    http_response_code(500);

    ob_clean();

    echo json_encode(
        [
            "sucesso" => false,
            "erro" => $e->getMessage()
        ],
        JSON_UNESCAPED_UNICODE
    );
}
?>
