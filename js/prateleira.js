import { 
    alocacoesPrateleiras, 
    setAlocacoesPrateleiras, 
    produtosEstoque, 
    carregarDadosDoStorage 
} from './state.js';
import { processarAlertas } from './alertas.js';

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    renderizarCardsPrateleiras();
    inicializarLogicaPrateleiras();
    configurarEventos();
});

function configurarEventos() {
    // Campo de Busca
    const inputBusca = document.getElementById('prateleiraBusca');
    if (inputBusca) {
        inputBusca.addEventListener('input', handleBuscaPrateleira);
    }

    // Event Delegation para o botão de exclusão da prateleira
    const container = document.querySelector('.prateleiras-container');
    if (container) {
        container.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remover-prat')) {
                const idPrat = e.target.getAttribute('data-id');
                excluirPrateleira(idPrat);
            }
        });
    }
}

export function salvarPrateleirasNoStorage() {
    localStorage.setItem('alocacoesPrateleiras', JSON.stringify(alocacoesPrateleiras));
    renderizarCardsPrateleiras();
    processarAlertas();
}

export function renderizarCardsPrateleiras(lista = alocacoesPrateleiras) {
    const container = document.querySelector('.prateleiras-container');
    if (!container) return;

    container.innerHTML = '';

    lista.forEach(prat => {
        const cardPrat = document.createElement('div');
        cardPrat.className = 'prateleira-card-horizontal';
        cardPrat.id = prat.id;

        cardPrat.innerHTML = `
            <div class="prat-numero">Prateleira ${prat.numero}</div>
            <div class="prat-detalhes">
                <h4>${prat.nome}</h4>
                <p><strong>Alocado:</strong> ${prat.unidades} unidades</p>
                <p><strong>Validade:</strong> ${prat.validade}</p>
                <p><strong>Lote:</strong> ${prat.lote}</p>
            </div>
            <button class="btn-remover-prat" data-id="${prat.id}">X</button>
        `;

        container.appendChild(cardPrat);
    });

    if (lista.length === 0) {
        container.innerHTML = `<div class="sem-resultados">Nenhuma prateleira encontrada.</div>`;
    }
}

export function excluirPrateleira(idPrat) {
    if (confirm("Tem certeza que deseja remover esta alocação de prateleira?")) {
        const novasPrateleiras = alocacoesPrateleiras.filter(p => p.id !== idPrat);
        setAlocacoesPrateleiras(novasPrateleiras);
        salvarPrateleirasNoStorage();
    }
}

function atualizarSelectLotes() {
    const selectLote = document.getElementById('lote-prat');
    if (!selectLote) return;
    
    selectLote.innerHTML = '<option value="">Selecione um lote...</option>';
    const lotesAdicionados = [];

    produtosEstoque.forEach(prod => {
        if (prod.lote && !lotesAdicionados.includes(prod.lote)) {
            lotesAdicionados.push(prod.lote);
            const opcao = document.createElement('option');
            opcao.value = prod.lote;
            opcao.innerText = `${prod.lote} (${prod.nome})`;
            selectLote.appendChild(opcao);
        }
    });
}

function gerarOpcoesPrateleiras() {
    const selectPrateleira = document.getElementById('num-prateleira');
    if (!selectPrateleira) return;

    selectPrateleira.innerHTML = '<option value="">Escolha o número...</option>';
    for (let i = 1; i <= 1000; i++) {
        const opcao = document.createElement('option');
        opcao.value = i;
        opcao.innerText = `Prateleira ${i}`;
        selectPrateleira.appendChild(opcao);
    }
}

function inicializarLogicaPrateleiras() {
    const modal = document.getElementById('cadastrarPrateleira');
    const btnMais = document.querySelector('.btn-mais');
    const btnCloseModal = document.getElementById('closeModalBtn');

    if (!modal || !btnMais || !btnCloseModal) return;

    btnMais.addEventListener('click', () => { 
        atualizarSelectLotes(); 
        gerarOpcoesPrateleiras();
        modal.showModal(); 
    });

    btnCloseModal.addEventListener('click', (e) => {
        e.preventDefault();
        
        const numeroPrateleira = document.getElementById('num-prateleira').value;
        const loteSelecionado = document.getElementById('lote-prat').value;
        const caixasAlocadas = parseInt(document.getElementById('caixas-prat').value) || 0;

        if (!numeroPrateleira || !loteSelecionado || caixasAlocadas <= 0) {
            alert("Por favor, preencha todos os campos corretamente.");
            return;
        }

        const prodEstoque = produtosEstoque.find(p => p.lote === loteSelecionado);
        
        if (prodEstoque) {
            const calculoUnidades = caixasAlocadas * (prodEstoque.proporcao || 1);

            const novaAlocacao = {
                id: 'prat-' + Date.now(),
                numero: numeroPrateleira,
                nome: prodEstoque.nome,
                caixas: caixasAlocadas,
                unidades: calculoUnidades,
                validade: prodEstoque.validade,
                lote: loteSelecionado
            };

            alocacoesPrateleiras.push(novaAlocacao);
            salvarPrateleirasNoStorage();
        }

        const form = modal.querySelector('form');
        if (form) form.reset();
        modal.close();
    });

    // Fechar ao clicar fora do Modal
    modal.addEventListener('click', (e) => {
        const dim = modal.getBoundingClientRect();
        if (
            e.clientX < dim.left || e.clientX > dim.right ||
            e.clientY < dim.top || e.clientY > dim.bottom
        ) {
            const form = modal.querySelector('form');
            if (form) form.reset();
            modal.close();
        }
    });
}

export function handleBuscaPrateleira() {
    const inputBusca = document.getElementById('prateleiraBusca');
    if (!inputBusca) return;

    let termo = inputBusca.value.toLowerCase().trim();
    if (termo === '') {
        renderizarCardsPrateleiras();
        return;
    }

    termo = termo.replace('prateleira', '').trim();

    const listaFiltrada = alocacoesPrateleiras.filter(prateleira => {
        const numero = String(prateleira.numero).toLowerCase();
        const nome = String(prateleira.nome || '').toLowerCase();
        const lote = String(prateleira.lote || '').toLowerCase();

        return (
            numero.includes(termo) ||
            nome.includes(termo) ||
            lote.includes(termo)
        );
    });

    renderizarCardsPrateleiras(listaFiltrada);
}