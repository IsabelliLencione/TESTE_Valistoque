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
        <li><a href="relatorio.html">Relatório</a></li> 
        <li><a href="produtos.php">Cadastro Produtos</a></li> 
        <li><a href="usuarios.php">Cadastro Usuários</a></li> 
        <li><a href="prateleira.html">Prateleiras</a></li> 
        <li><a href="estoque.php">Estoque Central</a></li> 
        <li><a href="alertas.php">Alertas</a></li> 
        <li><a href="ListaUsuarios.php">Usuários</a></li>

        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.php">Perfil</a>
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