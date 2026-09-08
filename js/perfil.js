document.addEventListener('DOMContentLoaded', () => {
    carregarPerfil();
    configurarEventos();
});

function configurarEventos() {
    const avatarWrapper = document.getElementById('avatar-wrapper');
    const fileInput = document.getElementById('file-input');

    if (avatarWrapper && fileInput) {
        avatarWrapper.addEventListener('click', () => {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', previewImage);
    }

    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', saveProfile);
    }
}

function triggerSelectFile() {
    const fileInput = document.getElementById('file-input');
    if (fileInput) fileInput.click();
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const profileImg = document.getElementById('profile-img');
            if (profileImg) {
                profileImg.src = e.target.result;
            }
            // Salva a imagem em Base64 no LocalStorage para persistir ao recarregar
            localStorage.setItem('fotoPerfilUsuario', e.target.result);
        };
        reader.readAsDataURL(file);
    }
}

function saveProfile(event) {
    event.preventDefault();

    const nome = document.getElementById('user-name').value;
    const email = document.getElementById('user-email').value;
    const badgePerfil = document.getElementById('badge-perfil');
    
    const perfilAtual = {
        nome: nome,
        email: email,
        perfil: badgePerfil ? badgePerfil.innerText.toLowerCase() : 'administrador'
    };

    localStorage.setItem('perfilUsuario', JSON.stringify(perfilAtual));
    alert("Perfil atualizado com sucesso!");
}

function carregarPerfil() {
    // 1. Busca os dados do perfil salvos
    const perfilSalvo = JSON.parse(localStorage.getItem('perfilUsuario')) || 
                        JSON.parse(localStorage.getItem('valistoqueUsuario')) || {
                            nome: 'Administrador Valistoque',
                            email: 'admin@valistoque.com',
                            perfil: 'administrador'
                        };

    // 2. Busca a foto salva
    const fotoSalva = localStorage.getItem('fotoPerfilUsuario');

    // 3. Elementos do DOM
    const inputNome = document.getElementById('user-name');
    const inputEmail = document.getElementById('user-email');
    const badgePerfil = document.getElementById('badge-perfil');
    const tituloPerfil = document.getElementById('titulo-perfil-admin');
    const imgPerfil = document.getElementById('profile-img');

    // 4. Preenche os dados nos campos
    if (inputNome) inputNome.value = perfilSalvo.nome || '';
    if (inputEmail) inputEmail.value = perfilSalvo.email || '';
    
    const tipoTexto = perfilSalvo.perfil === 'funcionario' ? 'Funcionário' : 'Administrador';
    if (badgePerfil) badgePerfil.textContent = tipoTexto;
    if (tituloPerfil) tituloPerfil.textContent = `Perfil do ${tipoTexto}`;
    
    if (imgPerfil && fotoSalva) {
        imgPerfil.src = fotoSalva;
    }
}