<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../php/config.php";

// Recupera os dados da sessão ou define valores padrão caso não esteja logado
$nomeUsuario  = $_SESSION["usuario_nome"] ?? "Usuário";
$emailUsuario = $_SESSION["usuario_email"] ?? "email@valistoque.com";
$tipoUsuario  = $_SESSION["usuario_tipo"] ?? "funcionario";

// Formata o título e o badge visual dinamicamente
$tituloPerfil = (strtolower($tipoUsuario) === 'administrador') ? 'Perfil do Administrador' : 'Perfil do Funcionário';
$badgePerfil  = (strtolower($tipoUsuario) === 'administrador') ? 'Administrador' : 'Funcionário';
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
<nav class="nav"> 
  <div class="header-nav">Valistoque</div> 
  <ul> 
    <!-- Relatório -->
    <li>
      <a href="relatorio.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
        Relatório
      </a>
    </li> 

    <!-- Cadastro Produtos -->
    <li>
      <a href="produtos.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-8 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
        Cadastro Produtos
      </a>
    </li> 

    <!-- Cadastro Usuários -->
    <li>
      <a href="usuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 12 4 12zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Cadastro Usuários
      </a>
    </li> 

    <!-- Prateleiras -->
    <li>
      <a href="prateleira.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 11h5V5H4v6zm0 8h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-8h5V5h-5v6zm6-6v6h5V5h-5z"/></svg>
        Prateleiras
      </a>
    </li> 

    <!-- Estoque Central -->
    <li>
      <a href="estoque.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/></svg>
        Estoque Central
      </a>
    </li> 

    <!-- Alertas -->
    <li>
      <a href="alertas.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
        Alertas
      </a>
    </li> 

    <!-- Usuários -->
    <li>
      <a class="profile-item" href="ListaUsuarios.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Usuários
      </a>
    </li>
    
    <!-- Perfil -->
    <li style="margin-top: auto; border-top: 1px solid #34495e;">
      <a href="perfil.php">
        <svg class="nav-icon" viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3-1.07-3-3s1.07-3 3-3 3 1.07 3 3-1.07 3-3 3z"/></svg>
        Perfil
      </a>
    </li> 
  </ul> 
</nav>

<main>
<!-- SECÇÃO PERFIL (BLOCO COMPLETO E UNIFICADO) -->
<div id="secao-perfil">
    <div class="container">

        <!-- Topo: Foto e Título Dinâmico -->
      <div class="profile-header">
        <div class="avatar-wrapper" id="avatar-wrapper">
            <img id="profile-img" src="https://via.placeholder.com/150" alt="Foto de Perfil">
         <div class="avatar-overlay">
             <span>EDITAR</span>
         </div>
        </div>
        <input type="file" id="file-input" accept="image/*" style="display: none;">

        <h2 id="titulo-perfil-admin"><?= htmlspecialchars($tituloPerfil) ?></h2>
    </div>

        <form id="profile-form" onsubmit="saveProfile(event)">
            <!-- Campo Nome Dinâmico -->
            <div class="info-group">
                <label class="label" for="user-name">Nome do usuário:</label>
                <input type="text" id="user-name" class="input-field" value="<?= htmlspecialchars($nomeUsuario) ?>" readonly required>
            </div>

            <!-- Campo E-mail Dinâmico -->
            <div class="info-group">
                <label class="label" for="user-email">Email do usuário:</label>
                <input type="email" id="user-email" class="input-field" value="<?= htmlspecialchars($emailUsuario) ?>" readonly required>
            </div>

            <!-- Campo Tipo de Conta Dinâmico -->
            <div class="info-group">
                <span class="label">Tipo de conta:</span>
                <div class="input-field disabled-field">
                    <span class="badge" id="badge-perfil"><?= htmlspecialchars($badgePerfil) ?></span>
                </div>
            </div>

            <!-- Botões organizados -->
            <div class="button-group">
                <a href="Principal.html" class="btn-exit">
                    Sair para a tela principal
                </a>
            </div>
        </form>

    </div>
</div>
<script type="module" src="../js/perfil.js"></script>
</main>
</body>
</html>
