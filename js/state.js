// Estado global e sincronização com o LocalStorage

export let produtosEstoque = JSON.parse(localStorage.getItem('produtosEstoque')) || [];
export let alocacoesPrateleiras = JSON.parse(localStorage.getItem('alocacoesPrateleiras')) || [];
export let historicoAlertas = JSON.parse(localStorage.getItem('historicoAlertas')) || [];
export let listaUsuarios = JSON.parse(localStorage.getItem('listaUsuarios')) || [];
export let configuracoesAlertas = JSON.parse(localStorage.getItem('configuracoesAlertas')) || {
    diasAntesValidade: 30,
    caixasMinimas: 10,
    intervaloMinutos: 15
};

export function setProdutosEstoque(novosProdutos) {
    produtosEstoque = novosProdutos;
}

export function setAlocacoesPrateleiras(novasAlocacoes) {
    alocacoesPrateleiras = novasAlocacoes;
}

export function setHistoricoAlertas(novoHistorico) {
    historicoAlertas = novoHistorico;
}

export function setListaUsuarios(novosUsuarios) {
    listaUsuarios = novosUsuarios;
}

export function setConfiguracoesAlertas(novasConfigs) {
    configuracoesAlertas = novasConfigs;
}

export function carregarDadosDoStorage() {
    produtosEstoque = JSON.parse(localStorage.getItem('produtosEstoque')) || [];
    alocacoesPrateleiras = JSON.parse(localStorage.getItem('alocacoesPrateleiras')) || [];
    historicoAlertas = JSON.parse(localStorage.getItem('historicoAlertas')) || [];
    listaUsuarios = JSON.parse(localStorage.getItem('listaUsuarios')) || [];
    
    const configsSalvas = localStorage.getItem('configuracoesAlertas');
    if (configsSalvas) {
        configuracoesAlertas = JSON.parse(configsSalvas);
    }
}

export function salvarProdutosNoStorage() {
    localStorage.setItem('produtosEstoque', JSON.stringify(produtosEstoque));
}