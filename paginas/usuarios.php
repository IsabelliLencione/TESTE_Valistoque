<?php
require_once __DIR__ . '/../php/config.php';

$usuarioParaEditar = null;

// 1. Busca os dados do usuário caso venha um ID para edição na URL
if (isset($_GET['editar_id'])) {
    $id_editar = filter_input(INPUT_GET, 'editar_id', FILTER_VALIDATE_INT);
    if ($id_editar) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $id_editar, PDO::PARAM_INT);
        $stmt->execute();
        $usuarioParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 2. Processa o salvamento (Cadastro ou Edição)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome']);
    $email = trim(strtolower($_POST['email']));
    $cpf = trim($_POST['cpf']);
    $senha_digitada = trim($_POST['senha']);
    $tipo = $_POST['tipo'];

    try {
        if ($id) {
            // Se tem ID, atualiza o usuário existente (UPDATE)
            if (!empty($senha_digitada)) {
                // Se digitou uma nova senha, atualiza a senha também (híbrido/texto limpo conforme seu banco atual)
                $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ?, senha = ?, tipo = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $cpf, $senha_digitada, $tipo, $id]);
            } else {
                // Se deixou a senha em branco na edição, mantém a senha atual
                $sql = "UPDATE usuarios SET nome = ?, email = ?, cpf = ?, tipo = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome, $email, $cpf, $tipo, $id]);
            }
        } else {
            // Se não tem ID, cria um usuário novo (INSERT)
            $sql = "INSERT INTO usuarios (nome, email, cpf, senha, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $cpf, $senha_digitada, $tipo]);
        }

        // Redireciona para a listagem de usuários após salvar
        header("Location: ListaUsuarios.php");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar o usuário: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/produtos.css">
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
            <li><a href="alertas.php">Alertas</a></li>
            <li><a href="ListaUsuarios.php">Usuários</a></li>
            <li style="margin-top: auto; border-top: 1px solid #34495e;">
                <a href="perfil.php">Perfil</a>
            </li>
        </ul>
    </nav>
    <main>
        <!-- CADASTRO DOS USUÁRIOS -->
        <div id="secao-produto">
            <h1 id="titulo-cadastro-produto">
                <?= $usuarioParaEditar ? 'Editar Usuário' : 'Cadastro de Usuário' ?>
            </h1>
            
            <form action="" method="POST" class="produto">
                <!-- Campo oculto para enviar o ID do usuário quando estiver editando -->
                <input type="hidden" name="id" value="<?= $usuarioParaEditar['id'] ?? '' ?>">

                <div>
                    <label for="nome">Nome Completo:</label>
                    <input type="text" id="nome" name="nome" 
                           value="<?= htmlspecialchars($usuarioParaEditar['nome'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="email">E-mail corporativo:</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($usuarioParaEditar['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" placeholder="111.111.111-11"
                           value="<?= htmlspecialchars($usuarioParaEditar['cpf'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" 
                           placeholder="<?= $usuarioParaEditar ? 'Deixe em branco para não alterar' : 'Digite a senha' ?>" 
                           <?= $usuarioParaEditar ? '' : 'required' ?>>
                </div>
                <div>
                    <label for="tipo">Tipo de Perfil:</label>
                    <select id="tipo" name="tipo" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                        <option value="funcionario" <?= (isset($usuarioParaEditar['tipo']) && $usuarioParaEditar['tipo'] === 'funcionario') ? 'selected' : '' ?>>Funcionário</option>
                        <option value="administrador" <?= (isset($usuarioParaEditar['tipo']) && $usuarioParaEditar['tipo'] === 'administrador') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-cadastrar">
                    <?= $usuarioParaEditar ? 'Atualizar Usuário' : 'Salvar Usuário' ?>
                </button>
            </form>
        </div>
    </main>
</body>
</html>
