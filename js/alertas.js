
let intervaloMonitoramento = null;
let intervaloAtual = null;

let filtroAtual = "todos";

let filaAlertas = [];
let exibindoAlerta = false;


/* =========================================================
   SWEETALERT2
========================================================= */

const Toast = Swal.mixin({

    toast: true,

    position: "top-end",

    showConfirmButton: false,

    timer: 6000,

    timerProgressBar: true,

    didOpen: (toast) => {

        toast.addEventListener(
            "mouseenter",
            Swal.stopTimer
        );

        toast.addEventListener(
            "mouseleave",
            Swal.resumeTimer
        );
    }

});


/* =========================================================
   NORMALIZAR TEXTO
========================================================= */

function normalizarTexto(texto) {

    return String(texto || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase();

}


/* =========================================================
   DEFINIR ALERTA CRÍTICO
========================================================= */

function ehCritico(tipo) {

    const texto =
        normalizarTexto(tipo);


    return (
        texto === "produto vencido" ||
        texto === "estoque central baixo"
    );

}


/* =========================================================
   DEFINIR CATEGORIA
========================================================= */

function categoriaAlerta(tipo) {

    const texto =
        normalizarTexto(tipo);


    /*
     * Alertas de validade
     */
    if (
        texto.includes("validade") ||
        texto.includes("vencido")
    ) {

        return "validade";

    }


    /*
     * Alertas de estoque
     */
    if (
        texto.includes("estoque")
    ) {

        return "estoque";

    }


    return "todos";

}


/* =========================================================
   DEFINIR CLASSE DE COR
========================================================= */

function classeCorAlerta(tipo) {

    /*
     * Crítico tem prioridade.
     *
     * Exemplos:
     * Produto Vencido
     * Estoque Central Baixo
     */

    if (ehCritico(tipo)) {

        return "critico";

    }


    /*
     * Validade
     *
     * Exemplo:
     * Validade Próxima
     */

    if (
        categoriaAlerta(tipo) === "validade"
    ) {

        return "validade";

    }


    /*
     * Estoque
     *
     * Exemplo:
     * Estoque Baixo Prateleira
     */

    if (
        categoriaAlerta(tipo) === "estoque"
    ) {

        return "estoque";

    }


    return "aviso";

}


/* =========================================================
   ÍCONE DO SWEETALERT2
========================================================= */

function iconeAlerta(tipo) {

    /*
     * Crítico
     */
    if (ehCritico(tipo)) {

        return "error";

    }


    /*
     * Validade
     */
    if (
        categoriaAlerta(tipo) === "validade"
    ) {

        return "warning";

    }


    /*
     * Estoque
     */
    if (
        categoriaAlerta(tipo) === "estoque"
    ) {

        return "info";

    }


    return "info";

}


/* =========================================================
   ADICIONAR ALERTAS À FILA
========================================================= */

function adicionarNaFila(alertas) {

    if (!Array.isArray(alertas)) {

        return;

    }


    if (alertas.length === 0) {

        return;

    }


    filaAlertas.push(
        ...alertas
    );


    processarFilaAlertas();

}


/* =========================================================
   PROCESSAR FILA DE ALERTAS
========================================================= */

async function processarFilaAlertas() {

    if (exibindoAlerta) {

        return;

    }


    if (filaAlertas.length === 0) {

        return;

    }


    exibindoAlerta = true;


    const alerta =
        filaAlertas.shift();


    await Toast.fire({

        icon:
            iconeAlerta(
                alerta.tipo_alerta
            ),

        titleText:
            alerta.tipo_alerta,

        text:
            alerta.mensagem

    });


    exibindoAlerta = false;


    processarFilaAlertas();

}


/* =========================================================
   VERIFICAR ALERTAS NO SERVIDOR
========================================================= */

async function verificarAlertas(
    mostrarPopups = true
) {

    try {

        const resposta =
            await fetch(
                "../php/verificar_alertas.php",
                {
                    method: "GET",

                    cache: "no-store",

                    headers: {
                        "Accept":
                            "application/json"
                    }
                }
            );


        const textoResposta =
            await resposta.text();


        let dados;


        try {

            dados =
                JSON.parse(
                    textoResposta
                );

        } catch (erroJSON) {

            console.error(
                "Resposta recebida do servidor:",
                textoResposta
            );

            throw new Error(
                "O servidor não retornou JSON válido."
            );

        }


        if (!resposta.ok) {

            throw new Error(
                dados.erro ||
                "Falha ao executar o servidor."
            );

        }


        if (!dados.sucesso) {

            throw new Error(
                dados.erro ||
                "Não foi possível verificar os alertas."
            );

        }


        /* ===============================================
           MOSTRAR POPUPS
        =============================================== */

        if (
            mostrarPopups &&
            Number(dados.exibir_popups) === 1
        ) {

            adicionarNaFila(
                dados.novos
            );

        }


        /* ===============================================
           HISTÓRICO
        =============================================== */

        renderizarHistorico(
            dados.historico || []
        );


        /* ===============================================
           RESUMO
        =============================================== */

        atualizarResumo(
            dados.resumo || {}
        );


        /* ===============================================
           MONITORAMENTO
        =============================================== */

        configurarMonitoramento(
            dados.intervalo_minutos
        );


    } catch (erro) {

        console.error(
            "Falha ao se comunicar com o servidor:",
            erro
        );

    }

}


/* =========================================================
   CONFIGURAR MONITORAMENTO AUTOMÁTICO
========================================================= */

function configurarMonitoramento(
    minutos
) {

    const valor =
        Number(minutos);


    if (
        !Number.isFinite(valor) ||
        valor < 1
    ) {

        return;

    }


    if (
        intervaloAtual === valor &&
        intervaloMonitoramento !== null
    ) {

        return;

    }


    if (
        intervaloMonitoramento !== null
    ) {

        clearInterval(
            intervaloMonitoramento
        );

    }


    intervaloAtual = valor;


    intervaloMonitoramento =
        setInterval(
            () => {

                verificarAlertas(true);

            },
            valor * 60 * 1000
        );

}


/* =========================================================
   RENDERIZAR HISTÓRICO
========================================================= */

function renderizarHistorico(
    historico
) {

    const lista =
        document.getElementById(
            "lista-alertas-completa"
        );


    if (!lista) {

        return;

    }


    lista.innerHTML = "";


    if (
        !Array.isArray(historico) ||
        historico.length === 0
    ) {

        const vazio =
            document.createElement(
                "div"
            );


        vazio.className =
            "alerta-vazio";


        vazio.textContent =
            "Nenhum alerta foi emitido ainda.";


        lista.appendChild(
            vazio
        );


        return;

    }


    historico.forEach(
        (alerta) => {

            const item =
                document.createElement(
                    "div"
                );


            /*
             * Descobre a cor do alerta.
             *
             * validade → amarelo
             * estoque  → azul
             * critico  → vermelho
             */

            const classeCor =
                classeCorAlerta(
                    alerta.tipo_alerta
                );


            /*
             * Classe principal do card.
             */

            item.className =
                `alerta-item ${classeCor}`;


            /*
             * Guarda informações para os filtros.
             */

            item.dataset.categoria =
                categoriaAlerta(
                    alerta.tipo_alerta
                );


            item.dataset.nivel =
                ehCritico(
                    alerta.tipo_alerta
                )
                    ? "critico"
                    : "aviso";


            item.dataset.idAlerta =
                alerta.id_alerta;


            /* =========================================
               TÍTULO
            ========================================= */

            const titulo =
                document.createElement(
                    "strong"
                );


            titulo.className =
                "alerta-item-titulo";


            titulo.textContent =
                alerta.tipo_alerta;


            /* =========================================
               MENSAGEM
            ========================================= */

            const mensagem =
                document.createElement(
                    "p"
                );


            mensagem.textContent =
                alerta.mensagem;


            /* =========================================
               DATA
            ========================================= */

            const data =
                document.createElement(
                    "small"
                );


            data.textContent =
                alerta.data_alerta;


            /* =========================================
               MONTAR CARD
            ========================================= */

            item.appendChild(
                titulo
            );


            item.appendChild(
                mensagem
            );


            item.appendChild(
                data
            );


            lista.appendChild(
                item
            );

        }
    );


    aplicarFiltro();

}


/* =========================================================
   ATUALIZAR RESUMO
========================================================= */

function atualizarResumo(
    resumo
) {

    const total =
        Number(
            resumo.total || 0
        );


    const criticos =
        Number(
            resumo.criticos || 0
        );


    const avisos =
        Number(
            resumo.avisos || 0
        );


    const elementoTotal =
        document.getElementById(
            "resumo-total-alertas"
        );


    const elementoCriticos =
        document.getElementById(
            "resumo-alertas-criticos"
        );


    const elementoAvisos =
        document.getElementById(
            "resumo-alertas-aviso"
        );


    if (elementoTotal) {

        elementoTotal.textContent =
            total;

    }


    if (elementoCriticos) {

        elementoCriticos.textContent =
            criticos;

    }


    if (elementoAvisos) {

        elementoAvisos.textContent =
            avisos;

    }

}


/* =========================================================
   FILTRAR ALERTAS
========================================================= */

function filtrarAlertas(
    filtro
) {

    filtroAtual =
        filtro;


    document
        .querySelectorAll(
            ".filtro-btn"
        )
        .forEach(
            (botao) => {

                botao.classList.toggle(
                    "ativo",

                    botao.dataset.filtro ===
                    filtro
                );

            }
        );


    aplicarFiltro();

}


/* =========================================================
   APLICAR FILTRO
========================================================= */

function aplicarFiltro() {

    const itens =
        document.querySelectorAll(
            ".alerta-item"
        );


    itens.forEach(
        (item) => {

            /*
             * Mostrar todos
             */

            if (
                filtroAtual === "todos"
            ) {

                item.style.display =
                    "";

                return;

            }


            /*
             * Mostrar apenas críticos
             */

            if (
                filtroAtual === "critico"
            ) {

                item.style.display =
                    item.dataset.nivel ===
                    "critico"
                        ? ""
                        : "none";

                return;

            }


            /*
             * Mostrar validade ou estoque
             */

            item.style.display =
                item.dataset.categoria ===
                filtroAtual
                    ? ""
                    : "none";

        }
    );

}


/* =========================================================
   DISPONIBILIZAR FILTRO PARA onclick DO HTML
========================================================= */

window.filtrarAlertas =
    filtrarAlertas;


/* =========================================================
   LIMPAR TODO O HISTÓRICO
========================================================= */

async function confirmarLimpezaAlertas() {

    const resultado =
        await Swal.fire({

            title:
                "Limpar histórico?",

            text:
                "Todos os alertas registrados serão excluídos.",

            icon:
                "warning",

            showCancelButton:
                true,

            confirmButtonText:
                "Sim, limpar",

            cancelButtonText:
                "Cancelar",

            reverseButtons:
                true

        });


    if (
        !resultado.isConfirmed
    ) {

        return;

    }


    try {

        const dadosFormulario =
            new URLSearchParams();


        dadosFormulario.append(
            "action",
            "limpar_historico"
        );


        const resposta =
            await fetch(
                "../php/verificar_alertas.php",
                {
                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/x-www-form-urlencoded",

                        "Accept":
                            "application/json"

                    },

                    body:
                        dadosFormulario.toString()

                }
            );


        const textoResposta =
            await resposta.text();


        let dados;


        try {

            dados =
                JSON.parse(
                    textoResposta
                );

        } catch (erroJSON) {

            console.error(
                "Resposta do servidor:",
                textoResposta
            );

            throw new Error(
                "O servidor não retornou uma resposta JSON válida."
            );

        }


        if (
            !resposta.ok ||
            !dados.sucesso
        ) {

            throw new Error(
                dados.erro ||
                "Não foi possível limpar o histórico."
            );

        }


        /* ===============================================
           LIMPAR TELA
        =============================================== */

        const lista =
            document.getElementById(
                "lista-alertas-completa"
            );


        if (lista) {

            lista.innerHTML = "";


            const vazio =
                document.createElement(
                    "div"
                );


            vazio.className =
                "alerta-vazio";


            vazio.textContent =
                "Nenhum alerta foi emitido ainda.";


            lista.appendChild(
                vazio
            );

        }


        atualizarResumo({

            total: 0,

            criticos: 0,

            avisos: 0

        });


        /* ===============================================
           MENSAGEM DE SUCESSO
        =============================================== */

        await Swal.fire({

            icon:
                "success",

            title:
                "Histórico limpo!",

            text:
                "Os alertas anteriores foram removidos.",

            timer:
                2000,

            showConfirmButton:
                false

        });


    } catch (erro) {

        console.error(
            erro
        );


        Swal.fire({

            icon:
                "error",

            title:
                "Erro",

            text:
                erro.message ||
                "Não foi possível limpar o histórico."

        });

    }

}


window.confirmarLimpezaAlertas =
    confirmarLimpezaAlertas;


/* =========================================================
   FEEDBACK DO SALVAMENTO
========================================================= */

function verificarFeedback() {

    const parametros =
        new URLSearchParams(
            window.location.search
        );


    /* ===============================================
       SUCESSO
    =============================================== */

    if (
        parametros.has("sucesso")
    ) {

        Toast.fire({

            icon:
                "success",

            title:
                "Configurações salvas!",

            text:
                "As condições dos alertas foram atualizadas."

        });


        limparURL();

    }


    /* ===============================================
       ERRO
    =============================================== */

    if (
        parametros.has("erro")
    ) {

        let mensagem =
            "Verifique os dados das configurações.";


        const erro =
            parametros.get("erro");


        if (
            erro === "preenchimento"
        ) {

            mensagem =
                "Preencha todos os campos.";

        }


        else if (
            erro === "valores_invalidos"
        ) {

            mensagem =
                "Os valores informados são inválidos.";

        }


        else if (
            erro === "banco"
        ) {

            mensagem =
                "Não foi possível salvar as configurações no banco.";

        }


        Toast.fire({

            icon:
                "error",

            title:
                "Erro ao salvar",

            text:
                mensagem

        });


        limparURL();

    }

}


/* =========================================================
   LIMPAR PARÂMETROS DA URL
========================================================= */

function limparURL() {

    const url =
        new URL(
            window.location.href
        );


    url.search = "";


    window.history.replaceState(
        {},
        document.title,
        url.toString()
    );

}


/* =========================================================
   INICIALIZAÇÃO
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        verificarFeedback();

        /*
         * Verifica os alertas imediatamente
         * ao abrir a página.
         */

        verificarAlertas(true);

    }
);

