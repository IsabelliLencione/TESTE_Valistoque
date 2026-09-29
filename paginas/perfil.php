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
        <li><a href="relatorio.html">Relatório</a></li> 
        <li><a href="produtos.php">Cadastro Produtos</a></li> 
        <li><a href="usuarios.php">Cadastro Usuários</a></li> 
        <li><a href="prateleira.php">Prateleiras</a></li> 
        <li><a href="estoque.php">Estoque Central</a></li> 
        <li><a href="alertas.html">Alertas</a></li> 
         <li><a href="ListaUsuarios.php">Usuários</a></li>

        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.php">Perfil</a>
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
