import { 
    listaUsuarios, 
    setListaUsuarios, 
    carregarDadosDoStorage 
} from './state.js';

document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDoStorage();
    renderizarTabelaUsuarios();
    configurarEventos();
});

function configurarEventos() {
    // Formulário de Cadastro
    const formUsuario = document.querySelector('form.funcionario');
    if (formUsuario) {
        formUsuario.addEventListener('submit', salvarUsuario);
    }

    // Máscara de CPF
    const inputCpf = document.getElementById('cpf-usuario');
    if (inputCpf) {
        inputCpf.addEventListener('input', (e) => aplicarMascaraCPF(e.target));
    }

    // Modal de Usuários
    const btnVerEquipe = document.getElementById('botao-UsuariosCadastrados');
    const modal = document.getElementById('modalUsuarios');
    const btnFechar = document.querySelector('.btn-fecharModalUsuarios');

    if (btnVerEquipe && modal) {
        btnVerEquipe.addEventListener('click', () => modal.showModal());
    }

    if (btnFechar && modal) {
        btnFechar.addEventListener('click', () => modal.close());
    }

    // Campo de Busca na Tabela
    const inputBusca = document.getElementById('usuarioBusca');
    if (inputBusca) {
        inputBusca.addEventListener('input', filtrarUsuarios);
    }
}

export function aplicarMascaraCPF(input) {
    let value = input.value.replace(/\D/g, ""); // Remove não dígitos
    if (value.length > 11) value = value.slice(0, 11);

    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

    input.value = value;
}

export function salvarUsuario(event) {
    event.preventDefault();

    const nome = document.getElementById('nome-usuario').value;
    const email = document.getElementById('email-usuario').value;
    const cpf = document.getElementById('cpf-usuario').value;
    const senha = document.getElementById('senha').value;
    const confirmSenha = document.getElementById('confirmsenha').value;
    
    const tipoAdmin = document.getElementById('tipo-admin');
    const perfil = tipoAdmin && tipoAdmin.checked ? 'administrador' : 'funcionario';

    // Validação de confirmação de senha
    if (senha !== confirmSenha) {
        alert("As senhas não coincidem!");
        return;
    }

    // Validação básica de tamanho do CPF
    if (cpf.replace(/\D/g, "").length !== 11) {
        alert("Por favor, insira um CPF válido com 11 dígitos.");
        return;
    }

    const novoUsuario = {
        id: 'usr-' + Date.now(),
        nome,
        email,
        cpf,
        senha,
        perfil
    };

    listaUsuarios.push(novoUsuario);
    salvarUsuariosNoStorage();

    alert("Usuário cadastrado com sucesso!");
    event.target.reset();
}

function salvarUsuariosNoStorage() {
    localStorage.setItem('listaUsuarios', JSON.stringify(listaUsuarios));
    renderizarTabelaUsuarios();
}

export function renderizarTabelaUsuarios(lista = listaUsuarios) {
    const tbody = document.querySelector('#tabela-usuario tbody');
    if (!tbody) return;

    if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Nenhum usuário cadastrado.</td></tr>';
        return;
    }

    tbody.innerHTML = lista.map(usr => {
        const nivelAcesso = usr.perfil === 'administrador' ? 'Administrador' : 'Funcionário';
        return `
            <tr>
                <td>${usr.nome}</td>
                <td>${usr.email}</td>
                <td>${nivelAcesso}</td>
            </tr>
        `;
    }).join('');
}

function filtrarUsuarios() {
    const termo = document.getElementById('usuarioBusca').value.toLowerCase().trim();
    
    const usuariosFiltrados = listaUsuarios.filter(usr => 
        usr.nome.toLowerCase().includes(termo) || 
        usr.email.toLowerCase().includes(termo)
    );

    renderizarTabelaUsuarios(usuariosFiltrados);
}