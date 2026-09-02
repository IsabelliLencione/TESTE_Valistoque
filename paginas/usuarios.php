<?php
require_once __DIR__ . "/../php/config.php";

?>

<!DOCTYPE html>
<html lang="en">
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
        <li><a href="alertas.html">Alertas</a></li> 
        <li><a href="ListaUsuarios.html">Usuários</a></li>

        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.html">Perfil</a>
        </li> 
    </ul> 
</nav>

<main>
        
         <!-- CADASTRO DE USUÁRIOS --> 
        <div id="secao-usuario" class="secao-form">
        <h1>Cadastro de Usuário</h1>
    
        <div>
            <form action="" method="POST" class="funcionario" onsubmit="salvarUsuario(event)">
                <label for="nome-usuario">Nome do Usuário:</label>
                <input type="text" id="nome-usuario" name="nome-usuario" required> 
            
                <label for="email-usuario">Email do usuário:</label>
                <input type="email" id="email-usuario" name="email-usuario" required> 
            
                <label for="cpf-usuario">CPF do usuário:</label>
                <input type="text" id="cpf-usuario" name="cpf-usuario" oninput="aplicarMascaraCPF(this)" required> 
            
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
        
        
<script type="module" src="../js/usuarios.js"></script>
</main>
</body>
</html>