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
criarBotaoPDF();
});

function configurarEventos() {
const setas = document.querySelectorAll('.seta-mes');

if (setas.length >= 2) {
    setas[0].addEventListener('click', () => mudarMes(-1));
    setas[1].addEventListener('click', () => mudarMes(1));
}


}

export function mudarMes(direcao) {
const novaData = new Date(dataAtualRelatorio);

novaData.setMonth(novaData.getMonth() + direcao);

const hoje = new Date();

const periodoNovaData = novaData.getFullYear() * 100 + novaData.getMonth();
const periodoHoje = hoje.getFullYear() * 100 + hoje.getMonth();

if (periodoNovaData > periodoHoje) {
    return;
}

dataAtualRelatorio = novaData;

atualizarCabecalhoData();
renderizarRelatorio();


}

function atualizarCabecalhoData() {
const txtMes = document.getElementById('txt-mes');
const txtAno = document.getElementById('txt-ano');
const setas = document.querySelectorAll('.seta-mes');


if (txtMes)
    txtMes.innerText = nomesMeses[dataAtualRelatorio.getMonth()];

if (txtAno)
    txtAno.innerText = dataAtualRelatorio.getFullYear();

if (setas.length >= 2) {
    const hoje = new Date();

    const noMesAtual =
        dataAtualRelatorio.getFullYear() === hoje.getFullYear() &&
        dataAtualRelatorio.getMonth() === hoje.getMonth();

    setas[1].style.opacity = noMesAtual ? '0.3' : '1';
    setas[1].style.cursor = noMesAtual ? 'not-allowed' : 'pointer';
}


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
    container.innerHTML =
        '<div class="item-vazio">Nenhuma entrada no estoque.</div>';
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

if (alocacoesPrateleiras.length === 0) {
    container.innerHTML =
        '<div class="item-vazio">Nenhuma saída registrada.</div>';
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
    container.innerHTML =
        '<div class="item-vazio">Nenhuma entrada na prateleira.</div>';
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

container.innerHTML =
    '<div class="item-vazio">Sem saídas de prateleira no período.</div>';


}

function renderizarAlertasRelatorio() {
const container = document.getElementById('lista-alertas');

if (!container) return;

if (historicoAlertas.length === 0) {
    container.innerHTML =
        '<div class="item-vazio">Nenhum alerta registrado.</div>';
    return;
}

container.innerHTML = historicoAlertas.map(a => `
    <div class="alerta-linha status-${a.status}">
        <span class="tag-status">
            ${a.status === 'critico' ? 'CRÍTICO' : 'AVISO'}
        </span>
        <p>${a.mensagem}</p>
    </div>
`).join('');


}

function criarBotaoPDF() {
if (document.getElementById('btn-baixar-pdf')) {
return;
}


const contentHeader = document.querySelector('.content-header');

if (!contentHeader) {
    return;
}

const botao = document.createElement('button');

botao.id = 'btn-baixar-pdf';
botao.innerHTML = '📄 Baixar PDF';

botao.style.background = '#4682b4';
botao.style.color = '#ffffff';
botao.style.border = 'none';
botao.style.padding = '10px 18px';
botao.style.borderRadius = '6px';
botao.style.fontSize = '14px';
botao.style.cursor = 'pointer';
botao.style.marginLeft = '20px';

botao.addEventListener('mouseenter', () => {
    botao.style.background = '#2f6690';
});

botao.addEventListener('mouseleave', () => {
    botao.style.background = '#4682b4';
});

botao.addEventListener('click', baixarPDF);

contentHeader.appendChild(botao);


}

async function baixarPDF() {
try {
if (!window.jspdf) {
await carregarJsPDF();
}


    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();

    const mes =
        document.getElementById('txt-mes')?.innerText || '';

    const ano =
        document.getElementById('txt-ano')?.innerText || '';

    pdf.setFontSize(22);
    pdf.setFont('helvetica', 'bold');
    pdf.text('Valistoque', 20, 20);

    pdf.setFontSize(16);
    pdf.setFont('helvetica', 'normal');
    pdf.text('Relatório de Estoque', 20, 32);

    pdf.setFontSize(12);
    pdf.text(`Período: ${mes} de ${ano}`, 20, 42);

    let y = 55;

    const secoes = [
        {
            titulo: 'Saída - Estoque central',
            id: 'saida-estoque'
        },
        {
            titulo: 'Entrada - Estoque central',
            id: 'entrada-estoque'
        },
        {
            titulo: 'Saída - Prateleira',
            id: 'saida-prateleira'
        },
        {
            titulo: 'Entrada - Prateleira',
            id: 'entrada-prateleira'
        },
        {
            titulo: 'Alertas emitidos',
            id: 'lista-alertas'
        }
    ];

    secoes.forEach(secao => {
        if (y > 270) {
            pdf.addPage();
            y = 20;
        }

        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text(secao.titulo, 20, y);

        y += 8;

        const elemento =
            document.getElementById(secao.id);

        if (!elemento) {
            return;
        }

        const texto =
            elemento.innerText.trim();

        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');

        if (texto) {
            const linhas =
                pdf.splitTextToSize(texto, 170);

            linhas.forEach(linha => {
                if (y > 275) {
                    pdf.addPage();
                    y = 20;
                }

                pdf.text(linha, 20, y);
                y += 6;
            });
        } else {
            pdf.text(
                'Nenhum registro encontrado.',
                20,
                y
            );

            y += 6;
        }

        y += 8;
    });

    if (y > 280) {
        pdf.addPage();
        y = 20;
    }

    pdf.setFontSize(9);

    pdf.text(
        `Relatório gerado em ${new Date().toLocaleDateString('pt-BR')}`,
        20,
        y
    );

    const nomeArquivo =
        `Relatorio_Valistoque_${mes}_${ano}.pdf`;

    pdf.save(nomeArquivo);

} catch (erro) {
    console.error('Erro ao gerar PDF:', erro);

    alert(
        'Não foi possível gerar o PDF. Verifique sua conexão com a internet.'
    );
}


}

function carregarJsPDF() {
return new Promise((resolve, reject) => {


    if (window.jspdf) {
        resolve();
        return;
    }

    const script =
        document.createElement('script');

    script.src =
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';

    script.onload = () => {
        resolve();
    };

    script.onerror = () => {
        reject(
            new Error('Não foi possível carregar o jsPDF.')
        );
    };

    document.head.appendChild(script);
});


}
