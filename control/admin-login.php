<?php
session_start();
require "../config/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login_admin"] ?? "");
    $senha = $_POST["senha_admin"] ?? "";

    if (empty($login) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT id_admin, login_admin, senha_admin, tipo_adm FROM admin WHERE login_admin = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $admin = $resultado->fetch_assoc();

            if (password_verify($senha, $admin["senha_admin"])) {
                $_SESSION["id_admin"] = $admin["id_admin"];
                $_SESSION["login_admin"] = $admin["login_admin"];
                $_SESSION["tipo_adm"] = (int) $admin["tipo_adm"];
                header("Location: admin-painel.php");
                exit();
            } else {
                $erro = "Login ou senha inválidos.";
            }
        } else {
            $erro = "Login ou senha inválidos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .login-box { background:#fff; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.1); width:300px; }
        .login-box h1 { font-size:20px; margin-bottom:20px; }
        .login-box input { width:100%; padding:8px; margin-bottom:12px; box-sizing:border-box; border:1px solid #ccc; border-radius:4px; }
        .login-box button { width:100%; padding:10px; background:#222; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        .erro { color:#c0392b; margin-bottom:12px; font-size:14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Painel Administrativo</h1>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="login_admin" placeholder="Login (6 letras)" maxlength="6" required>
            <input type="password" name="senha_admin" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
