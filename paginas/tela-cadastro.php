<?php
require "../config/conexao.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$nome = $_POST["nome"];
$cpf = $_POST["cpf"];
$telefone = $_POST["telefone"];
$email = $_POST["email"];
$senha = $_POST["senha"];

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

//verifica se os dados estãoi vazios
if (empty($nome) || empty($cpf) || empty($telefone) || empty($email) || empty($senha)){
    $erro="Todos os campos são obrigatórios.";
}

// Verifica se o e-mail já está cadastrado
$sqlVerifica = "SELECT id_usuario FROM usuario WHERE email = ?";
$stmtVerifica = $conn->prepare($sqlVerifica);
$stmtVerifica->bind_param("s", $email);
$stmtVerifica->execute();
$resultado = $stmtVerifica->get_result();

if ($resultado->num_rows > 0) {
    $erro = "Este e-mail já está cadastrado.";;
}

//inseri os dados no sql
if (empty($erro)){
$sql = "INSERT INTO usuario
(nome_usuario, cpf, telefone, email, senha) VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssss",
    $nome,
    $cpf,
    $telefone,
    $email,
    $senhaHash
);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Erro ao cadastrar.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/Estilotelacadaastro.css">

</head>
<body>

<div class="cadastro">

    <div class="logo">📝</div>

    <h1>Criar Conta</h1>

    <div class="sub">
        Preencha os dados abaixo para começar.
    </div>

    <form id="formCadastro" method="POST">

        
        <div class="input">
            <label>Nome de usuário</label>
            <input type="text" id="usuario" name="nome" placeholder="Digite seu usuário" required>
        </div>

        <div class="input">
            <label>E-mail</label>
            <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
        </div>

        <div class="input">
            <label>Senha</label>
            <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
        </div>

        <div class="input">
            <label>Confirmar senha</label>
            <input type="password" id="confirmar" name="confirmar" placeholder="Repita sua senha" required>
        </div>

        <div class="input">
            <label>CPF</label>
            <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF" required>
        </div>

        <div class="input">
            <label>Telefone</label>
            <input type="text" id="telefone" name="telefone" placeholder="Digite seu telefone" required>
        </div>
        
            <?php if (!empty($erro)) { ?>
                <div class="erro">
                    <?php echo $erro; ?>
                </div>
            <?php } ?>

        <button type="submit">
            Criar Conta
        </button>
    </form>

    <div class="footer">
        Já possui uma conta?
        <a href="tela-login.php">Entrar</a>
    </div>
</div>

<script>

const form = document.getElementById("formCadastro");
const erro = document.getElementById("erro");

form.addEventListener("submit",function(e){
    erro.innerHTML="";

    const usuario=document.getElementById("usuario").value.trim();
    const email=document.getElementById("email").value.trim();
    const senha=document.getElementById("senha").value;
    const cpf = document.getElementById("cpf").value.trim();
    const telefone = document.getElementById("telefone").value.trim();
    const confirmar=document.getElementById("confirmar").value;

    if(usuario=="" || email=="" || senha=="" || telefone=="" || cpf=="" ||confirmar=="" ){
        erro.innerHTML="Preencha todos os campos.";
        e.preventDefault();
        return;
    }

    if(senha.length<6){
        erro.innerHTML="A senha deve possuir pelo menos 6 caracteres.";
        e.preventDefault();
        return;
    }

    if(senha!==confirmar){
        erro.innerHTML="As senhas não coincidem.";
        e.preventDefault();
        return;
    }
    alert("Cadastro realizado com sucesso!");
});

</script>

</body>
</html>