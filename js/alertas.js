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
   DEFINIR NÍVEL DO ALERTA
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
   DEFINIR CATEGORIA DO ALERTA
========================================================= */

function categoriaAlerta(tipo) {

    const texto =
        normalizarTexto(tipo);


    if (
        texto.includes("validade") ||
        texto.includes("vencido")
    ) {

        return "validade";

    }


    if (
        texto.includes("estoque")
    ) {

        return "estoque";

    }


    return "todos";

}


/* =========================================================
   ÍCONE DO SWEETALERT2
========================================================= */

function iconeAlerta(tipo) {

    if (ehCritico(tipo)) {

        return "error";

    }


    if (
        categoriaAlerta(tipo) === "validade"
    ) {

        return "warning";

    }


    return "warning";

}


/* =========================================================
   COLOCAR ALERTAS NA FILA
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
   EXIBIR FILA DE ALERTAS
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
            alerta.nivel === "critico"
                ? "error"
                : iconeAlerta(
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
           POPUPS
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
   CONFIGURAR INTERVALO AUTOMÁTICO
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


            const critico =
                ehCritico(
                    alerta.tipo_alerta
                );


            item.className =
                critico
                    ? "alerta-item critico"
                    : "alerta-item aviso";


            item.dataset.categoria =
                categoriaAlerta(
                    alerta.tipo_alerta
                );


            item.dataset.nivel =
                critico
                    ? "critico"
                    : "aviso";


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
        Number(resumo.total || 0);

    const criticos =
        Number(resumo.criticos || 0);

    const avisos =
        Number(resumo.avisos || 0);


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
                    botao.dataset.filtro === filtro
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

            if (
                filtroAtual === "todos"
            ) {

                item.style.display =
                    "";

                return;

            }


            if (
                filtroAtual === "critico"
            ) {

                item.style.display =
                    item.dataset.nivel === "critico"
                        ? ""
                        : "none";

                return;

            }


            item.style.display =
                item.dataset.categoria === filtroAtual
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
   LIMPAR HISTÓRICO
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


    if (!resultado.isConfirmed) {
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
                            "application/x-www-form-urlencoded"
                    },

                    body:
                        dadosFormulario.toString()
                }
            );


        const dados =
            await resposta.json();


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
   FEEDBACK DO SALVAMENTO DAS CONFIGURAÇÕES
========================================================= */

function verificarFeedback() {

    const parametros =
        new URLSearchParams(
            window.location.search
        );


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
   INICIALIZAÇÃO DA PÁGINA
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        verificarFeedback();

        /*
         * Faz a primeira verificação imediatamente.
         */
        verificarAlertas(true);

    }
);