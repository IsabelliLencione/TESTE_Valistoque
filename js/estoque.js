import { 
    produtosEstoque, 
    setProdutosEstoque, 
    carregarDadosDoStorage 
} from './state.js';
import { processarAlertas } from './alertas.js';

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    renderizarCardsEstoque();
    configurarEventosCards();
});

export function salvarProdutosNoStorage() {
    localStorage.setItem('produtosEstoque', JSON.stringify(produtosEstoque));
    renderizarCardsEstoque();
    processarAlertas();
}

export function renderizarCardsEstoque() {
    const container = document.querySelector('.cards-container');
    if (!container) return;

    container.innerHTML = '';

    produtosEstoque.forEach(prod => {
        const card = document.createElement('div');
        card.className = 'card-item';
        card.id = prod.id;
        card.setAttribute('data-nome', prod.nome);
        card.setAttribute('data-validade', prod.validade);
        card.setAttribute('data-lote', prod.lote);
        card.setAttribute('data-proporcao', prod.proporcao);

        card.innerHTML = `
            <h3>${prod.nome}</h3> 
            <div class="card-info"><strong>Validade:</strong> <span class="d-validade">${prod.validade}</span></div> 
            <div class="card-info"><strong>Lote:</strong> <span class="d-lote">${prod.lote}</span></div> 
            <div class="card-info"><strong>Qtd. Caixas:</strong> <span class="d-caixas">${prod.caixas}</span></div> 
            <div class="card-acoes">
                <button class="btn-editar" data-id="${prod.id}">Editar</button>
                <button class="btn-excluir" data-id="${prod.id}">Excluir</button>
            </div>
        `;
        container.appendChild(card);
    });
}

function configurarEventosCards() {
    const container = document.querySelector('.cards-container');
    if (!container) return;

    // Event Delegation: escuta os cliques dentro do container de cards
    container.addEventListener('click', (event) => {
        const target = event.target;
        const idCard = target.getAttribute('data-id');

        if (target.classList.contains('btn-excluir')) {
            excluirCard(idCard);
        } else if (target.classList.contains('btn-editar')) {
            editarCard(idCard);
        }
    });
}

export function excluirCard(idCard) {
    if (confirm("Tem certeza que deseja excluir este item do estoque?")) {
        const novosProdutos = produtosEstoque.filter(p => p.id !== idCard);
        setProdutosEstoque(novosProdutos);
        salvarProdutosNoStorage();
    }
}

export function editarCard(idCard) {
    // Redireciona para a página de produtos passando o ID do item a ser editado
    window.location.href = `produtos.html?editar=${idCard}`;
}




