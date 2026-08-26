<?php
require_once __DIR__ ."\conexao.php";

$id = $_GET["id"];

$sql = "SELECT * FROM produtos WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $preco = $_POST["preco"];
    $quantidade = $_POST["quantidade"];
    $categoria = $_POST["categoria"];

    if($nome == ""){
        $erro = "ERRO: O nome é obrigatório.<br>";
        echo($erro);
    }
    
     if(mb_strlen($nome, 'UTF-8') < 3){
        $erro = "ERRO: O nome tem que ter pelo menos 3 caracteres.<br>";
        echo($erro);
    }
    
    if($preco <= 0){
        $erro = "ERRO: O preço teve ser um número maior que 0.<br>";
        echo($erro);
    }
    
    if($quantidade < 0){
        $erro = "ERRO: A quantidade tem que ser um número = ou maior que 0.<br>";
        echo($erro);
    }
   
    
    if(empty($erro)){
    $sql = "UPDATE produtos SET nome = ?, preco = ?, quantidade = ?, categoria = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $preco, $quantidade, $categoria, $id]);

    header("Location: index.php");
    exit;
    }
}
?>