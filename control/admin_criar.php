<?php
// ============================================================
// SCRIPT DE USO ÚNICO — cria o primeiro admin master (tipo_adm = 1).
// Depois de usar, APAGUE este arquivo do servidor por segurança.
// ============================================================

require "../config/conexao.php";
require "admin_utils.php";

$novoLogin = "";
$novaSenha = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novoLogin = gerarLoginAdmin($conn);
    $novaSenha = gerarSenhaAdmin();
    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admin (login_admin, senha_admin, senha_visivel, tipo_adm) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $novoLogin, $hash, $novaSenha);

    if (!$stmt->execute()) {
        $erro = "Erro ao criar admin.";
        $novoLogin = "";
        $novaSenha = "";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar primeiro admin (uso único)</title>
</head>
<body>
    <h1>Criar o primeiro admin master</h1>
    <p style="color:red;"><strong>Apague este arquivo depois de usar!</strong></p>

    <?php if ($erro): ?>
        <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <?php if ($novoLogin): ?>
        <p style="color:green;">Admin master criado! Anote agora, essa senha não aparece de novo aqui:</p>
        <ul>
            <li><strong>Login:</strong> <?= htmlspecialchars($novoLogin) ?></li>
            <li><strong>Senha:</strong> <?= htmlspecialchars($novaSenha) ?></li>
        </ul>
        <p>Entre em <code>admin-login.php</code> com essas credenciais.</p>
    <?php else: ?>
        <form method="POST">
            <button type="submit">Gerar admin master</button>
        </form>
    <?php endif; ?>
</body>
</html>
