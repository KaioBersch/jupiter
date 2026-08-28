<?php
// Funções auxiliares para gerar credenciais de novos admins.
// Usa random_int() (gerador criptograficamente seguro), não rand()/mt_rand().

function gerarLoginAdmin(mysqli $conn): string {
    $letras = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    do {
        $login = "";
        for ($i = 0; $i < 6; $i++) {
            $login .= $letras[random_int(0, strlen($letras) - 1)];
        }

        $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE login_admin = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $jaExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($jaExiste); // gera de novo se colidir com um login existente

    return $login;
}

function gerarSenhaAdmin(): string {
    $caracteres = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $tamanho = random_int(6, 10);

    $senha = "";
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $senha;
}
