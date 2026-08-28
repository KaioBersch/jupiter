<?php
session_start();
require "../config/conexao.php";
require "admin_utils.php";

if (!isset($_SESSION["id_admin"])) {
    header("Location: admin-login.php");
    exit();
}

$souMaster = ($_SESSION["tipo_adm"] ?? 0) == 1;
$mensagem = "";
$credenciaisNovoAdmin = null;

// --------- Ações (POST) ---------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST["acao"] ?? "";

    if ($acao === "aprovar_salao" || $acao === "recusar_salao") {
        $id_salao = (int) ($_POST["id_salao"] ?? 0);
        $novaSituacao = $acao === "aprovar_salao" ? "aceito" : "recusado";

        $stmt = $conn->prepare("UPDATE salao SET situacao = ? WHERE id_salao = ?");
        $stmt->bind_param("si", $novaSituacao, $id_salao);
        $stmt->execute();
        $stmt->close();

        $mensagem = $novaSituacao === "aceito" ? "Salão aceito." : "Salão recusado.";
    }

    if ($acao === "excluir_salao") {
        $id_salao = (int) ($_POST["id_salao"] ?? 0);

        $stmtImg = $conn->prepare("SELECT caminho FROM imagem_salao WHERE id_salao = ?");
        $stmtImg->bind_param("i", $id_salao);
        $stmtImg->execute();
        $resImg = $stmtImg->get_result();
        while ($linha = $resImg->fetch_assoc()) {
            $caminhoArquivo = "../" . $linha["caminho"];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }
        $stmtImg->close();

        $stmt = $conn->prepare("DELETE FROM salao WHERE id_salao = ?");
        $stmt->bind_param("i", $id_salao);
        $stmt->execute();
        $stmt->close();

        $mensagem = "Salão excluído.";
    }

    if ($acao === "excluir_usuario") {
        $id_usuario = (int) ($_POST["id_usuario"] ?? 0);

        $stmtImg = $conn->prepare("
            SELECT im.caminho FROM imagem_salao im
            INNER JOIN salao s ON s.id_salao = im.id_salao
            WHERE s.id_dono = ?
        ");
        $stmtImg->bind_param("i", $id_usuario);
        $stmtImg->execute();
        $resImg = $stmtImg->get_result();
        while ($linha = $resImg->fetch_assoc()) {
            $caminhoArquivo = "../" . $linha["caminho"];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }
        $stmtImg->close();

        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->close();

        $mensagem = "Usuário excluído (e os salões dele também).";
    }

    if ($acao === "criar_admin") {
        if (!$souMaster) {
            $mensagem = "Apenas admins master podem criar novos admins.";
        } else {
            $tipoNovo = ($_POST["tipo_adm"] ?? "0") === "1" ? 1 : 0;

            $novoLogin = gerarLoginAdmin($conn);
            $novaSenha = gerarSenhaAdmin();
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO admin (login_admin, senha_admin, senha_visivel, tipo_adm) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $novoLogin, $hash, $novaSenha, $tipoNovo);

            if ($stmt->execute()) {
                $credenciaisNovoAdmin = ["login" => $novoLogin, "senha" => $novaSenha];
                $mensagem = "Novo admin criado com sucesso.";
            } else {
                $mensagem = "Erro ao criar novo admin.";
            }
            $stmt->close();
        }
    }
}

// --------- Dados para exibir ---------

$saloesPendentes = $conn->query("
    SELECT s.id_salao, s.nome_salao, s.cidade, s.estado, u.nome_usuario
    FROM salao s
    JOIN usuario u ON u.id_usuario = s.id_dono
    WHERE s.situacao = 'pendente'
    ORDER BY s.id_salao DESC
");

$todosSaloes = $conn->query("
    SELECT s.id_salao, s.nome_salao, s.cidade, s.estado, s.situacao, u.nome_usuario
    FROM salao s
    JOIN usuario u ON u.id_usuario = s.id_dono
    ORDER BY s.id_salao DESC
");

$todosUsuarios = $conn->query("
    SELECT id_usuario, nome_usuario, email, telefone
    FROM usuario
    ORDER BY id_usuario DESC
");

$todosAdmins = $conn->query("
    SELECT id_admin, login_admin, senha_visivel, tipo_adm, data_criacao
    FROM admin
    ORDER BY id_admin DESC
");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
        header { background:#222; color:#fff; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; }
        header a { color:#fff; text-decoration:none; font-size:14px; }
        main { max-width:1000px; margin:20px auto; padding:0 15px; }
        section { background:#fff; border-radius:8px; padding:20px; margin-bottom:25px; box-shadow:0 1px 4px rgba(0,0,0,.1); }
        h2 { margin-top:0; font-size:18px; }
        table { width:100%; border-collapse: collapse; font-size:14px; }
        th, td { text-align:left; padding:8px; border-bottom:1px solid #eee; }
        .badge { padding:3px 8px; border-radius:10px; font-size:12px; color:#fff; }
        .badge-pendente { background:#e0a800; }
        .badge-aceito { background:#28a745; }
        .badge-recusado { background:#c0392b; }
        .badge-master { background:#5b3ea6; }
        .badge-comum { background:#6c757d; }
        button { cursor:pointer; border:none; border-radius:4px; padding:6px 10px; font-size:13px; margin-right:5px; }
        .btn-aprovar { background:#28a745; color:#fff; }
        .btn-recusar { background:#e0a800; color:#fff; }
        .btn-excluir { background:#c0392b; color:#fff; }
        .btn-criar { background:#222; color:#fff; padding:8px 14px; }
        .msg { background:#eaf6ec; color:#1e7e34; padding:10px 15px; border-radius:6px; margin-bottom:20px; }
        .credenciais-novas { background:#fff3cd; color:#664d03; padding:12px 15px; border-radius:6px; margin-bottom:15px; font-size:14px; }
        .vazio { color:#888; font-size:14px; }
        select { padding:6px; border-radius:4px; border:1px solid #ccc; margin-right:8px; }
    </style>
</head>
<body>
    <header>
        <span>Painel Administrativo — <?= htmlspecialchars($_SESSION["login_admin"]) ?> (<?= $souMaster ? "master" : "comum" ?>)</span>
        <a href="admin-logout.php">Sair</a>
    </header>

    <main>
        <?php if ($mensagem): ?>
            <div class="msg"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($credenciaisNovoAdmin): ?>
            <div class="credenciais-novas">
                <strong>Anote agora — essa senha só aparece em destaque aqui:</strong><br>
                Login: <strong><?= htmlspecialchars($credenciaisNovoAdmin["login"]) ?></strong> —
                Senha: <strong><?= htmlspecialchars($credenciaisNovoAdmin["senha"]) ?></strong>
            </div>
        <?php endif; ?>

        <section>
            <h2>Salões pendentes de aprovação</h2>
            <?php if ($saloesPendentes->num_rows === 0): ?>
                <p class="vazio">Nenhum salão pendente.</p>
            <?php else: ?>
                <table>
                    <tr><th>Nome</th><th>Cidade/UF</th><th>Dono</th><th>Ações</th></tr>
                    <?php while ($s = $saloesPendentes->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['nome_salao']) ?></td>
                            <td><?= htmlspecialchars($s['cidade'] . '/' . $s['estado']) ?></td>
                            <td><?= htmlspecialchars($s['nome_usuario']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="acao" value="aprovar_salao">
                                    <input type="hidden" name="id_salao" value="<?= (int) $s['id_salao'] ?>">
                                    <button type="submit" class="btn-aprovar">Aprovar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="acao" value="recusar_salao">
                                    <input type="hidden" name="id_salao" value="<?= (int) $s['id_salao'] ?>">
                                    <button type="submit" class="btn-recusar">Recusar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </section>

        <section>
            <h2>Todos os salões</h2>
            <?php if ($todosSaloes->num_rows === 0): ?>
                <p class="vazio">Nenhum salão cadastrado.</p>
            <?php else: ?>
                <table>
                    <tr><th>Nome</th><th>Cidade/UF</th><th>Dono</th><th>Status</th><th>Ações</th></tr>
                    <?php while ($s = $todosSaloes->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['nome_salao']) ?></td>
                            <td><?= htmlspecialchars($s['cidade'] . '/' . $s['estado']) ?></td>
                            <td><?= htmlspecialchars($s['nome_usuario']) ?></td>
                            <td><span class="badge badge-<?= htmlspecialchars($s['situacao']) ?>"><?= htmlspecialchars($s['situacao']) ?></span></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este salão? Essa ação não pode ser desfeita.');">
                                    <input type="hidden" name="acao" value="excluir_salao">
                                    <input type="hidden" name="id_salao" value="<?= (int) $s['id_salao'] ?>">
                                    <button type="submit" class="btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </section>

        <section>
            <h2>Usuários</h2>
            <?php if ($todosUsuarios->num_rows === 0): ?>
                <p class="vazio">Nenhum usuário cadastrado.</p>
            <?php else: ?>
                <table>
                    <tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Ações</th></tr>
                    <?php while ($u = $todosUsuarios->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nome_usuario']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telefone']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir este usuário? Todos os salões dele também serão excluídos. Essa ação não pode ser desfeita.');">
                                    <input type="hidden" name="acao" value="excluir_usuario">
                                    <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                                    <button type="submit" class="btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </section>

        <section>
            <h2>Administradores</h2>

            <?php if ($souMaster): ?>
                <form method="POST" style="margin-bottom:15px;">
                    <input type="hidden" name="acao" value="criar_admin">
                    <select name="tipo_adm">
                        <option value="0">Admin comum</option>
                        <option value="1">Admin master</option>
                    </select>
                    <button type="submit" class="btn-criar">Criar novo admin</button>
                </form>
            <?php endif; ?>

            <?php if ($todosAdmins->num_rows === 0): ?>
                <p class="vazio">Nenhum admin cadastrado.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <?php if ($souMaster): ?>
                            <th>Login</th>
                            <th>Senha</th>
                            <th>Tipo</th>
                            <th>Criado em</th>
                        <?php endif; ?>
                    </tr>
                    <?php while ($a = $todosAdmins->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= (int) $a['id_admin'] ?></td>
                            <?php if ($souMaster): ?>
                                <td><?= htmlspecialchars($a['login_admin']) ?></td>
                                <td><?= htmlspecialchars($a['senha_visivel']) ?></td>
                                <td>
                                    <span class="badge <?= $a['tipo_adm'] == 1 ? 'badge-master' : 'badge-comum' ?>">
                                        <?= $a['tipo_adm'] == 1 ? 'master' : 'comum' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($a['data_criacao']) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <?php if (!$souMaster): ?>
                    <p class="vazio">Só admins master podem ver login, senha e tipo dos outros admins.</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
