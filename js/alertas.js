// Importações de estado e persistência global (se usar o módulo state.js)
import { 
    configuracoesAlertas, 
    setConfiguracoesAlertas, 
    historicoAlertas, 
    produtosEstoque, 
    carregarDadosDoStorage 
} from './state.js';

let filtroAtualAlertas = 'todos';
let intervaloMonitoramentoAlertas = null;

// Inicialização da tela ao carregar
document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    preencherFormularioAlertas();
    processarAlertas();
    iniciarMonitoramentoAlertas();
    configurarEventos();
});

// Configuração de Event Listeners (substitui os atalhos onclick/onsubmit do HTML)
function configurarEventos() {
    const formConfig = document.getElementById('form-config-alertas');
    if (formConfig) {
        formConfig.addEventListener('submit', salvarConfiguracoesAlertas);
    }

    const botoesFiltro = document.querySelectorAll('.filtros-alerta .filtro-btn');
    botoesFiltro.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const filtro = e.target.getAttribute('data-filtro');
            filtrarAlertas(filtro);
        });
    });
}

export function salvarConfiguracoesAlertas(event) {
    // IMPORTANTE: O event.preventDefault() FOI REMOVIDO daqui.
    // Isso permite que o formulário recarregue a página e envie os dados via POST para o PHP salvar no MySQL.

    // Captura os dados baseados nas novas IDs ajustadas do HTML (Central + Prateleira)
    const diasValidadeVal = parseInt(document.getElementById('dias-alerta-validade').value) || 30;
    const caixasCentralVal = parseInt(document.getElementById('caixas-alerta-central').value) || 10;
    const caixasPrateleiraVal = parseInt(document.getElementById('caixas-alerta-prateleira').value) || 5;
    const intervaloMinutosVal = parseInt(document.getElementById('intervalo-alerta').value) || 15;

    // ATUALIZAÇÃO DO ESTADO INTERNO DO JAVASCRIPT / LOCALSTORAGE
    const novasConfigs = {
        diasAntesValidade: diasValidadeVal,
        caixasMinimasCentral: caixasCentralVal,
        caixasMinimasPrateleira: caixasPrateleiraVal,
        caixasMinimas: caixasCentralVal, // Mantém compatibilidade legada se houver
        intervaloMinutos: intervaloMinutosVal
    };

    setConfiguracoesAlertas(novasConfigs);
    localStorage.setItem('configuracoesAlertas', JSON.stringify(novasConfigs));

    // Limpa o histórico antigo para recalcular sob as novas regras após o recarregamento
    localStorage.removeItem('historicoAlertas');
}

export function preencherFormularioAlertas() {
    const dias = document.getElementById('dias-alerta-validade');
    const caixasCentral = document.getElementById('caixas-alerta-central');
    const caixasPrateleira = document.getElementById('caixas-alerta-prateleira');
    const intervalo = document.getElementById('intervalo-alerta');

    if (dias) dias.value = configuracoesAlertas.diasAntesValidade || 30;
    if (caixasCentral) caixasCentral.value = configuracoesAlertas.caixasMinimasCentral || configuracoesAlertas.caixasMinimas || 10;
    if (caixasPrateleira) caixasPrateleira.value = configuracoesAlertas.caixasMinimasPrateleira || 5;
    if (intervalo) intervalo.value = String(configuracoesAlertas.intervaloMinutos || 15);
}

export function iniciarMonitoramentoAlertas() {
    if (intervaloMonitoramentoAlertas) {
        clearInterval(intervaloMonitoramentoAlertas);
    }

    const intervaloMs = (configuracoesAlertas.intervaloMinutos || 15) * 60 * 1000;
    intervaloMonitoramentoAlertas = setInterval(() => processarAlertas(), intervaloMs);
}

export function processarAlertas(forcarRegistro = false) {
    const alertasEncontrados = [];
    const agora = new Date().toISOString();

    // Definição segura dos parâmetros para o processamento de regras locais
    const diasLimite = configuracoesAlertas.diasAntesValidade || 30;
    const estoqueMinimoCentral = configuracoesAlertas.caixasMinimasCentral || configuracoesAlertas.caixasMinimas || 10;

    produtosEstoque.forEach(prod => {
        const dataValidade = converterDataBrParaDate(prod.validade);
        const diasRestantes = dataValidade ? diferencaEmDias(dataValidade) : null;

        if (diasRestantes !== null && diasRestantes <= diasLimite) {
            const status = diasRestantes <= 0 ? 'critico' : 'aviso';
            const mensagem = diasRestantes <= 0
                ? `O produto ${prod.nome} do lote ${prod.lote} está com a validade vencida e precisa de ação imediata.`
                : `O produto ${prod.nome} do lote ${prod.lote} vence em ${diasRestantes} dia(s).`;

            alertasEncontrados.push({
                chave: `validade-${prod.lote}-${status}`,
                tipo: 'validade',
                status,
                produto: prod.nome,
                lote: prod.lote,
                referencia: diasRestantes <= 0 ? 'Validade vencida' : `${diasRestantes} dia(s) restantes`,
                mensagem,
                dataHora: agora
            });
        }

        if ((prod.caixas || 0) <= estoqueMinimoCentral) {
            const status = (prod.caixas || 0) <= Math.max(1, Math.ceil(estoqueMinimoCentral / 2)) ? 'critico' : 'aviso';
            const mensagem = `O produto ${prod.nome} está com apenas ${prod.caixas || 0} caixa(s) no estoque central.`;

            alertasEncontrados.push({
                chave: `estoque-${prod.lote}-${status}`,
                tipo: 'estoque',
                status,
                produto: prod.nome,
                lote: prod.lote,
                referencia: `${prod.caixas || 0} caixa(s) disponíveis`,
                mensagem,
                dataHora: agora
            });
        }
    });

    if (forcarRegistro || alertasEncontrados.length) {
        alertasEncontrados.forEach(registrarAlerta);
        salvarHistoricoAlertas();
    }

    atualizarResumoAlertas();
    renderizarAlertasCompletos();
}

function registrarAlerta(alerta) {
    const jaExiste = historicoAlertas.some(item => item.chave === alerta.chave && item.status === alerta.status);
    if (jaExiste) return;
    historicoAlertas.unshift(alerta);
}

function salvarHistoricoAlertas() {
    historicoAlertas.sort((a, b) => new Date(b.dataHora) - new Date(a.dataHora));
    localStorage.setItem('historicoAlertas', JSON.stringify(historicoAlertas));
}

export function filtrarAlertas(filtro) {
    filtroAtualAlertas = filtro;
    document.querySelectorAll('.filtro-btn').forEach(btn => {
        btn.classList.toggle('ativo', btn.dataset.filtro === filtro);
    });
    renderizarAlertasCompletos();
}

function obterAlertasFiltrados() {
    if (filtroAtualAlertas === 'todos') return historicoAlertas;
    if (filtroAtualAlertas === 'critico') return historicoAlertas.filter(item => item.status === 'critico');
    return historicoAlertas.filter(item => item.tipo === filtroAtualAlertas);
}

export function atualizarResumoAlertas() {
    const total = historicoAlertas.length;
    const criticos = historicoAlertas.filter(item => item.status === 'critico').length;
    const avisos = historicoAlertas.filter(item => item.status === 'aviso').length;

    const totalEl = document.getElementById('resumo-total-alertas');
    const criticosEl = document.getElementById('resumo-alertas-criticos');
    const avisosEl = document.getElementById('resumo-alertas-aviso');

    if (totalEl) totalEl.innerText = total;
    if (criticosEl) criticosEl.innerText = criticos;
    if (avisosEl) avisosEl.innerText = avisos;
}

export function renderizarAlertasCompletos() {
    const container = document.getElementById('lista-alertas-completa');
    if (!container) return;

    const alertas = obterAlertasFiltrados();
    if (!alertas.length) {
        container.innerHTML = '<div class="vazio-alertas">Nenhum alerta emitido até o momento.</div>';
        return;
    }

    container.innerHTML = alertas.map(alerta => {
        const classeTag = alerta.status === 'critico' ? 'tag-critico' : alerta.status === 'aviso' ? 'tag-aviso' : 'tag-info';
        const textoTag = alerta.status === 'critico' ? 'Crítico' : alerta.status === 'aviso' ? 'Aviso' : 'Informativo';
        const corBorda = alerta.status === 'critico' ? '#e74c3c' : alerta.tipo === 'estoque' ? '#f39c12' : '#3498db';
        const tipoLabel = alerta.tipo === 'estoque' ? 'Estoque' : 'Validade';

        return `
            <div class="alerta-item" style="border-left-color: ${corBorda}">
                <div class="alerta-meta">
                    <span class="tag-alerta ${classeTag}">${textoTag}</span>
                    <span><strong>Tipo:</strong> ${tipoLabel}</span>
                    <span><strong>Produto:</strong> ${alerta.produto}</span>
                    <span><strong>Lote:</strong> ${alerta.lote}</span>
                    <span><strong>Emitido em:</strong> ${formatarDataHora(alerta.dataHora)}</span>
                </div>
                <h3>${alerta.referencia}</h3>
                <div class="alerta-mensagem">${alerta.mensagem}</div>
            </div>
        `;
    }).join('');
}

// Helpers Utilitários de Data
function formatarDataHora(dataISO) {
    return new Date(dataISO).toLocaleString('pt-BR');
}

function converterDataBrParaDate(dataBr) {
    if (!dataBr) return null;
    const partes = dataBr.split('/');
    if (partes.length !== 3) return null;
    return new Date(partes[2], partes[1] - 1, partes[0]);
}

function diferencaEmDias(dataAlvo) {
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    dataAlvo.setHours(0, 0, 0, 0);
    const diferencaMs = dataAlvo.getTime() - hoje.getTime();
    return Math.ceil(diferencaMs / (1000 * 60 * 60 * 24));
}
