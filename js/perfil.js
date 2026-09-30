document.addEventListener("DOMContentLoaded", () => {
  carregarPerfil();
  configurarEventos();
});

function configurarEventos() {
  const avatarWrapper = document.getElementById("avatar-wrapper");
  const fileInput = document.getElementById("file-input");

  if (avatarWrapper && fileInput) {
    avatarWrapper.addEventListener("click", () => {
      fileInput.click();
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", previewImage);
  }

  const profileForm = document.getElementById("profile-form");
  if (profileForm) {
    profileForm.addEventListener("submit", saveProfile);
  }
}

function triggerSelectFile() {
  const fileInput = document.getElementById("file-input");
  if (fileInput) fileInput.click();
}

function previewImage(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const profileImg = document.getElementById("profile-img");
      if (profileImg) {
        profileImg.src = e.target.result;
      }
      // Salva a imagem em Base64 no LocalStorage para persistir ao recarregar
      localStorage.setItem("fotoPerfilUsuario", e.target.result);
    };
    reader.readAsDataURL(file);
  }
}

function saveProfile(event) {
  event.preventDefault();

  const nome = document.getElementById("user-name").value;
  const email = document.getElementById("user-email").value;
  const badgePerfil = document.getElementById("badge-perfil");

  const perfilAtual = {
    nome: nome,
    email: email,
    tipo: badgePerfil ? badgePerfil.innerText.toLowerCase() : "administrador",
  };

  // Atualiza a chave padrão de usuário utilizada pelo sistema
  localStorage.setItem("usuario", JSON.stringify(perfilAtual));
  alert("Perfil atualizado com sucesso!");
}

function carregarPerfil() {
  // CORREÇÃO AQUI: Busca diretamente a chave 'usuario' que foi gerada pelo login.html
  const perfilSalvo = JSON.parse(localStorage.getItem("usuario")) || {
    nome: "Administrador Valistoque",
    email: "admin@valistoque.com",
    perfil: "administrador",
  };

  // Mapeamento extra para garantir compatibilidade se a propriedade vier como .tipo ou .perfil
  const nomeUser = perfilSalvo.nome || "Usuário";
  const emailUser = perfilSalvo.email || "email@valistoque.com";
  const tipoUser = (
    perfilSalvo.tipo ||
    perfilSalvo.perfil ||
    "administrador"
  ).toLowerCase();

  // Busca a foto salva
  const fotoSalva = localStorage.getItem("fotoPerfilUsuario");

  // Elementos do DOM
  const inputNome = document.getElementById("user-name");
  const inputEmail = document.getElementById("user-email");
  const badgePerfil = document.getElementById("badge-perfil");
  const tituloPerfil = document.getElementById("titulo-perfil-admin");
  const imgPerfil = document.getElementById("profile-img");

  // Preenche os dados nos campos dinamicamente baseado no login atual
  if (inputNome) inputNome.value = nomeUser;
  if (inputEmail) inputEmail.value = emailUser;

  const tipoTexto =
    tipoUser === "funcionario" ? "Funcionário" : "Administrador";
  if (badgePerfil) badgePerfil.textContent = tipoTexto;
  if (tituloPerfil) tituloPerfil.textContent = `Perfil do ${tipoTexto}`;

  if (imgPerfil && fotoSalva) {
    imgPerfil.src = fotoSalva;
  }
}
