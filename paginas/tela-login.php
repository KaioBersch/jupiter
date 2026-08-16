<?php
session_start();
require "../config/conexao.php";

$erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    }

    if (empty($erro)) {
        $sql = "SELECT id_usuario, nome_usuario, senha FROM usuario WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($senha, $usuario["senha"])) {
                $_SESSION["id_usuario"] = $usuario["id_usuario"];
                $_SESSION["nome"] = $usuario["nome_usuario"];
                header("Location: index.php");
                exit();
            } else {
                $erro = "E-mail ou senha inválidos.";
            }
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessar Conta</title>
    <link rel="stylesheet" href="../css/estilotelalogin.css">
</head>
<body>

<div class="login">
    <div class="logo">🔒</div>
    <h1>Bem-vindo!</h1>
    <div class="sub">Faça login para acessar sua conta</div>

    <form id="formLogin" method="POST">
        <label for="email">E-mail</label>
        <div class="input">
            <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
        </div>

        <label for="senha">Senha</label>
        <div class="input">
            <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
        </div>

        <div class="senha">
            <a href="#">Esqueceu sua senha?</a>
        </div>

        <?php if (!empty($erro)) { ?>
            <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php } ?>

        <button type="submit">Entrar</button>
    </form>

    <div class="cadastro">
        Não possui uma conta? <a href="tela-cadastro.php">Cadastre-se</a>
    </div>
</div>

<script>
document.getElementById("formLogin").addEventListener("submit", function(e) {
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value;

    if (!email || !senha) {
        alert("Preencha todos os campos.");
        e.preventDefault();
    }
});
</script>
</body>
</html>