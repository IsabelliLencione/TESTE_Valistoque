<?php
require_once __DIR__ . '/../php/config.php';

$usuarioParaEditar = null;

// 1. Se veio 'editar_id' na URL, busca os dados no banco para preencher o formulário
if (isset($_GET['editar_id'])) {
    $idEditar = filter_input(INPUT_GET, 'editar_id', FILTER_VALIDATE_INT);
    if ($idEditar) {
        $stmt = $pdo->prepare("SELECT id, nome, email, cpf, tipo FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $idEditar, PDO::PARAM_INT);
        $stmt->execute();
        $usuarioParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 2. Processamento do Envio do Formulário (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $nome = $_POST['nome-usuario'];
    $email = $_POST['email-usuario'];
    $cpf = $_POST['cpf-usuario'];
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo-usuario'];

    try {
        if ($id_usuario) {
            // EDITAR: Se já existe um ID, faz UPDATE
            if (!empty($senha)) {
                // Atualiza com nova senha
                $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ?, senha = ?, tipo = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $cpf, $senha, $tipo, $id_usuario]);
            } else {
                // Atualiza mantendo a senha atual
                $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ?, tipo = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $cpf, $tipo, $id_usuario]);
            }
        } else {
            // CADASTRAR: Se não tem ID, faz INSERT
            if ($senha !== $_POST['confirmsenha']) {
                die("As senhas não são iguais!");
            }
            $sql = "INSERT INTO usuarios (nome, email, cpf, senha, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $cpf, $senha, $tipo]);
        }

        header("Location: ListaUsuarios.php");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar usuário: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuarios</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/usuarios.css">
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
        
         <!-- CADASTRO DE USUÁRIOS --> 
        <div id="secao-usuario" class="secao-form">
        <h1>Cadastro de Usuário</h1>
    
        <div>
            <form action="" method="POST" class="funcionario" >
                <label for="nome-usuario">Nome do Usuário:</label>
                <input type="text" id="nome-usuario" name="nome-usuario" required> 
            
                <label for="email-usuario">Email do usuário:</label>
                <input type="email" id="email-usuario" name="email-usuario" required> 
            
                <label for="cpf-usuario">CPF do usuário:</label>
                <input type="number" id="cpf-usuario" name="cpf-usuario" maxlength="11" required>

            
                <label for="senha">Senha para cadastro:</label>
                <input type="password" id="senha" name="senha" required> 
            
                <label for="confirmsenha">Confirmar senha:</label>
                <input type="password" id="confirmsenha" name="confirmsenha" required> 
            
                    <fieldset>
                    <legend>Selecione o tipo:</legend>
                    <div class="tipoAdm">
                        <input type="radio" id="tipo-admin" name="tipo-usuario" value="administrador" checked />
                        <label for="tipo-admin">Administrador</label>
                    </div>
                    <div class="tipoFunc">
                        <input type="radio" id="tipo-func" name="tipo-usuario" value="funcionario" />
                        <label for="tipo-func">Funcionario</label>
                    </div>
                </fieldset>
            
                    <button type="submit">Cadastrar</button>
            </form>
            </div>
            
           
           
    </div>
        
        
<script  src="../js/usuarios.js"></script>
</main>
</body>
</html>