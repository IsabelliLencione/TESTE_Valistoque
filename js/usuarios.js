
    // Máscara de CPF
    const inputCpf = document.getElementById('cpf-usuario');
    if (inputCpf) {
        inputCpf.addEventListener('input', (e) => aplicarMascaraCPF(e.target));
    }


export function aplicarMascaraCPF(input) {
    let value = input.value.replace(/\D/g, ""); // Remove não dígitos
    if (value.length > 11) value = value.slice(0, 11);

    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

    input.value = value;
}

    // Validação básica de tamanho do CPF
    if (cpf.replace(/\D/g, "").length !== 11) {
        alert("Por favor, insira um CPF válido com 11 dígitos.");
        return;
    }
