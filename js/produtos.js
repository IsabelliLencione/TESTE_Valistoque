import { 
    produtosEstoque, 
    salvarProdutosNoStorage, 
    carregarDadosDoStorage 
} from './state.js';

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    verificarModoEdicao();
    configurarEventos();
});

function configurarEventos() {
    const formProduto = document.querySelector('form.produto');
    if (formProduto) {
        formProduto.addEventListener('submit', salvarProduto);
    }
}

export function salvarProduto(event) {
    event.preventDefault();

    const nome = document.getElementById('produto').value;
    const lote = document.getElementById('lote').value;
    const validade = document.getElementById('validade').value;
    const caixas = parseInt(document.getElementById('caixa').value) || 0;
    const prodPorCaixa = parseInt(document.getElementById('produto-caixa').value) || 1;
    const idEditando = document.getElementById('editando-id').value;

    const dataFormatada = validade.includes('-') ? validade.split('-').reverse().join('/') : validade;

    if (idEditando) {
        // Atualiza item existente no array
        const index = produtosEstoque.findIndex(p => p.id === idEditando);
        if (index !== -1) {
            produtosEstoque[index].nome = nome;
            produtosEstoque[index].lote = lote;
            produtosEstoque[index].validade = dataFormatada;
            produtosEstoque[index].caixas = caixas;
            produtosEstoque[index].proporcao = prodPorCaixa;
        }
        document.getElementById('editando-id').value = "";
        document.getElementById('titulo-cadastro-produto').innerText = "Cadastro de Produto";
    } else {
        // Insere novo item no array
        const novoProd = {
            id: 'card-' + Date.now(),
            nome: nome,
            marca: 'Não especificada',
            lote: lote,
            validade: dataFormatada,
            caixas: caixas,
            proporcao: prodPorCaixa
        };
        produtosEstoque.push(novoProd);
    }

    salvarProdutosNoStorage();
    event.target.reset();
    
    // Redireciona para o estoque após salvar
    window.location.href = 'estoque.html';
}

function verificarModoEdicao() {
    const urlParams = new URLSearchParams(window.location.search);
    const idEditar = urlParams.get('editar');

    if (idEditar) {
        const prod = produtosEstoque.find(p => p.id === idEditar);
        if (!prod) return;

        const validadeEN = prod.validade.includes('/') ? prod.validade.split('/').reverse().join('-') : prod.validade;

        document.getElementById('produto').value = prod.nome;
        document.getElementById('lote').value = prod.lote;
        document.getElementById('validade').value = validadeEN;
        document.getElementById('caixa').value = prod.caixas;
        document.getElementById('produto-caixa').value = prod.proporcao || 1;
        document.getElementById('editando-id').value = idEditar;

        const titulo = document.getElementById('titulo-cadastro-produto');
        if (titulo) titulo.innerText = "Editando Produto";
    }
}