const nomesMeses = [
  "Janeiro",
  "Fevereiro",
  "Março",
  "Abril",
  "Maio",
  "Junho",
  "Julho",
  "Agosto",
  "Setembro",
  "Outubro",
  "Novembro",
  "Dezembro",
];

let dataAtualRelatorio = new Date(
  Number(window.anoRelatorio),
  Number(window.mesRelatorio) - 1,
  1,
);

document.addEventListener("DOMContentLoaded", () => {
  atualizarCabecalhoData();
  criarBotaoPDF();
});

function atualizarCabecalhoData() {
  const txtMes = document.getElementById("txt-mes");
  const txtAno = document.getElementById("txt-ano");
  const setas = document.querySelectorAll(".seta-mes");

  if (txtMes) {
    txtMes.innerText = nomesMeses[dataAtualRelatorio.getMonth()];
  }

  if (txtAno) {
    txtAno.innerText = dataAtualRelatorio.getFullYear();
  }

  if (setas.length >= 2) {
    const hoje = new Date();

    const noMesAtual =
      dataAtualRelatorio.getFullYear() === hoje.getFullYear() &&
      dataAtualRelatorio.getMonth() === hoje.getMonth();

    setas[1].style.opacity = noMesAtual ? "0.3" : "1";

    setas[1].style.cursor = noMesAtual ? "not-allowed" : "pointer";
  }
}

window.mudarMes = function (direcao) {
  const novaData = new Date(dataAtualRelatorio);

  novaData.setMonth(novaData.getMonth() + direcao);

  const hoje = new Date();

  const periodoNovaData = novaData.getFullYear() * 100 + novaData.getMonth();

  const periodoHoje = hoje.getFullYear() * 100 + hoje.getMonth();

  if (periodoNovaData > periodoHoje) {
    return;
  }

  const mes = novaData.getMonth() + 1;

  const ano = novaData.getFullYear();

  window.location.href = `relatorio.php?mes=${mes}&ano=${ano}`;
};

function criarBotaoPDF() {
  if (document.getElementById("btn-baixar-pdf")) {
    return;
  }

  const contentHeader = document.querySelector(".content-header");

  if (!contentHeader) {
    return;
  }

  const botao = document.createElement("button");

  botao.id = "btn-baixar-pdf";

  botao.innerHTML = "📄 Baixar PDF";

  botao.style.background = "#4682b4";

  botao.style.color = "#ffffff";

  botao.style.border = "none";

  botao.style.padding = "10px 18px";

  botao.style.borderRadius = "6px";

  botao.style.fontSize = "14px";

  botao.style.cursor = "pointer";

  botao.style.marginLeft = "20px";

  botao.addEventListener("mouseenter", () => {
    botao.style.background = "#2f6690";
  });

  botao.addEventListener("mouseleave", () => {
    botao.style.background = "#4682b4";
  });

  botao.addEventListener("click", baixarPDF);

  contentHeader.appendChild(botao);
}

function pegarDados(containerId) {
  const container = document.getElementById(containerId);

  if (!container) {
    return [];
  }

  const itens = container.querySelectorAll(".relatorio-item");

  return Array.from(itens).map((item) => {
    const strong = item.querySelector("strong");

    const spans = item.querySelectorAll("span");

    const small = item.querySelector("small");

    return {
      nome: strong ? strong.innerText.trim() : "",

      info: spans.length > 0 ? spans[0].innerText.trim() : "",

      quantidade: small ? small.innerText.trim() : "",
    };
  });
}

async function baixarPDF() {
  try {
    if (!window.jspdf) {
      await carregarJsPDF();
    }

    const { jsPDF } = window.jspdf;

    const pdf = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4",
    });

    const mes = document.getElementById("txt-mes")?.innerText || "";

    const ano = document.getElementById("txt-ano")?.innerText || "";

    const saidasEstoque = pegarDados("saida-estoque");

    const entradasEstoque = pegarDados("entrada-estoque");

    const saidasPrateleira = pegarDados("saida-prateleira");

    const entradasPrateleira = pegarDados("entrada-prateleira");

    let y = 20;

    pdf.setFont("helvetica", "bold");

    pdf.setFontSize(18);

    pdf.text("Relatório", 20, y);

    y += 8;

    pdf.setFont("helvetica", "normal");

    pdf.setFontSize(10);

    pdf.text(`${mes} de ${ano}`, 20, y);

    y += 12;

    y = adicionarMovimentacoesPDF(
      pdf,
      "Saída - Estoque central",
      "Saída no estoque",
      saidasEstoque,
      y,
    );

    y = adicionarMovimentacoesPDF(
      pdf,
      "Entrada - Estoque central",
      "Entrada no estoque",
      entradasEstoque,
      y,
    );

    y = adicionarMovimentacoesPDF(
      pdf,
      "Saída - Prateleira",
      "Saída da prateleira",
      saidasPrateleira,
      y,
    );

    y = adicionarMovimentacoesPDF(
      pdf,
      "Entrada - Prateleira",
      "Entrada na prateleira",
      entradasPrateleira,
      y,
    );

    const nomeArquivo = `Relatorio_${mes}_${ano}.pdf`;

    pdf.save(nomeArquivo);
  } catch (erro) {
    console.error("Erro ao gerar PDF:", erro);

    alert("Não foi possível gerar o PDF.");
  }
}

function adicionarMovimentacoesPDF(pdf, titulo, descricao, dados, y) {
  if (dados.length === 0) {
    return y;
  }

  dados.forEach((item) => {
    if (y > 260) {
      pdf.addPage();
      y = 20;
    }

    pdf.setFont("helvetica", "bold");

    pdf.setFontSize(11);

    pdf.text(titulo, 20, y);

    y += 6;

    pdf.setFont("helvetica", "bold");

    pdf.setFontSize(10);

    let linha = item.nome;

    if (item.info) {
      linha += ` ${item.info}`;
    }

    if (item.quantidade) {
      linha += ` | ${item.quantidade}`;
    }

    pdf.text(linha, 20, y);

    y += 6;

    pdf.setFont("helvetica", "normal");

    pdf.setFontSize(9);

    pdf.text(descricao, 20, y);

    y += 10;
  });

  return y;
}

function carregarJsPDF() {
  return new Promise((resolve, reject) => {
    if (window.jspdf) {
      resolve();
      return;
    }

    const script = document.createElement("script");

    script.src =
      "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js";

    script.onload = () => resolve();

    script.onerror = () =>
      reject(new Error("Não foi possível carregar o jsPDF."));

    document.head.appendChild(script);
  });
}
