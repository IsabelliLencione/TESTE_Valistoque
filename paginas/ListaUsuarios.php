<?php 
require_once __DIR__ . "/../php/config.php";


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/ListaUsuarios.css">
    
</head>
<body>
    <nav class="nav"> 
    <div class="header-nav">Valistoque</div> 

    <ul> 
        <li><a href="relatorio.html">Relatório</a></li> 
        <li><a href="produtos.php">Cadastro Produtos</a></li> 
        <li><a href="usuarios.html">Cadastro Usuários</a></li> 
        <li><a href="prateleira.html">Prateleiras</a></li> 
        <li><a href="estoque.php">Estoque Central</a></li> 
        <li><a href="alertas.html">Alertas</a></li> 
        <li><a href="ListaUsuarios.php">Usuários</a></li>

        <li style="margin-top: auto; border-top: 1px solid #34495e;">
            <a href="perfil.html">Perfil</a>
        </li> 
    </ul>
    </nav>
        <main>
<div class="Usuarios-header">
    <h2>Gerenciamento de Usuários</h2>
    <div class="container-tabelasUsuarios">
        <div>
            <table id="tabela-usuario">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Nível de Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Nenhum usuário cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nome-usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['email-usuario']) ?></td>
                                <td><?= htmlspecialchars($usuario['tipo-usuario']) ?></td>
                                <td>
                                    <a href="usuarios.php?editar_id=<?= $usuario['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="../php/excluir_usuario.php?id=<?= $usuario['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir o usuário: <?= htmlspecialchars($usuario['nome-usuario'], ENT_QUOTES) ?>?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
        </main>
</body>
</html>