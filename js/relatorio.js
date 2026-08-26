import { 
    produtosEstoque, 
    alocacoesPrateleiras, 
    historicoAlertas, 
    carregarDadosDoStorage 
} from './state.js';

const nomesMeses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

let dataAtualRelatorio = new Date();

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    atualizarCabecalhoData();
    renderizarRelatorio();
    configurarEventos();
});

function configurarEventos() {
    // Escuta os cliques nas setas de alteração de mês
    const setas = document.querySelectorAll('.seta-mes');
    if (setas.length >= 2) {
        setas[0].addEventListener('click', () => mudarMes(-1));
        setas[1].addEventListener('click', () => mudarMes(1));
    }
}

export function mudarMes(direcao) {
    dataAtualRelatorio.setMonth(dataAtualRelatorio.getMonth() + direcao);
    atualizarCabecalhoData();
    renderizarRelatorio();
}

function atualizarCabecalhoData() {
    const txtMes = document.getElementById('txt-mes');
    const txtAno = document.getElementById('txt-ano');

    if (txtMes) txtMes.innerText = nomesMeses[dataAtualRelatorio.getMonth()];
    if (txtAno) txtAno.innerText = dataAtualRelatorio.getFullYear();
}

export function renderizarRelatorio() {
    renderizarEntradasEstoque();
    renderizarSaidasEstoque();
    renderizarEntradasPrateleira();
    renderizarSaidasPrateleira();
    renderizarAlertasRelatorio();
}

function renderizarEntradasEstoque() {
    const container = document.getElementById('entrada-estoque');
    if (!container) return;

    if (produtosEstoque.length === 0) {
        container.innerHTML = '<div class="item-vazio">Nenhuma entrada no estoque.</div>';
        return;
    }

    container.innerHTML = produtosEstoque.map(p => `
        <div class="relatorio-item">
            <strong>${p.nome}</strong>
            <span>Lote: ${p.lote} | Qtd: ${p.caixas} caixas</span>
            <small>Validade: ${p.validade}</small>
        </div>
    `).join('');
}

function renderizarSaidasEstoque() {
    const container = document.getElementById('saida-estoque');
    if (!container) return;

    // Exibe itens transferidos do estoque para as prateleiras
    if (alocacoesPrateleiras.length === 0) {
        container.innerHTML = '<div class="item-vazio">Nenhuma saída registrada.</div>';
        return;
    }

    container.innerHTML = alocacoesPrateleiras.map(p => `
        <div class="relatorio-item">
            <strong>${p.nome}</strong>
            <span>Enviado para: Prateleira ${p.numero}</span>
            <small>Lote: ${p.lote} | Qtd: ${p.caixas || 0} caixas</small>
        </div>
    `).join('');
}

function renderizarEntradasPrateleira() {
    const container = document.getElementById('entrada-prateleira');
    if (!container) return;

    if (alocacoesPrateleiras.length === 0) {
        container.innerHTML = '<div class="item-vazio">Nenhuma entrada na prateleira.</div>';
        return;
    }

    container.innerHTML = alocacoesPrateleiras.map(p => `
        <div class="relatorio-item">
            <strong>Prateleira ${p.numero}</strong>
            <span>${p.nome} (Lote: ${p.lote})</span>
            <small>Unidades: ${p.unidades}</small>
        </div>
    `).join('');
}

function renderizarSaidasPrateleira() {
    const container = document.getElementById('saida-prateleira');
    if (!container) return;

    container.innerHTML = '<div class="item-vazio">Sem saídas de prateleira no período.</div>';
}

function renderizarAlertasRelatorio() {
    const container = document.getElementById('lista-alertas');
    if (!container) return;

    if (historicoAlertas.length === 0) {
        container.innerHTML = '<div class="item-vazio">Nenhum alerta registrado.</div>';
        return;
    }

    container.innerHTML = historicoAlertas.map(a => `
        <div class="alerta-linha status-${a.status}">
            <span class="tag-status">${a.status === 'critico' ? 'CRÍTICO' : 'AVISO'}</span>
            <p>${a.mensagem}</p>
        </div>
    `).join('');
}