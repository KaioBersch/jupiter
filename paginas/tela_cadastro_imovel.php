<?php
    require "../config/conexao.php";
    session_start();
    if (!isset($_SESSION["id_usuario"])) {
        die("Você precisa estar logado para cadastrar um salão.");
    }
    $id_dono = $_SESSION["id_usuario"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome_salao = $_POST["nome_salao"];
        $cep = $_POST["cep"];
        $estado = $_POST["estado"];
        $descricao = $_POST["descricao"];
        $cidade = $_POST["cidade"];
        
        $sql = "INSERT INTO salao
    (nome_salao, cep, estado, descricao, cidade, id_dono)
    VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssi",
        $nome_salao,
        $cep,
        $estado,
        $descricao,
        $cidade,
        $id_dono
    );    

    if ($stmt->execute()) {
        header("location: index.php");
        exit();
    } else {
        echo "Erro ao cadastrar.";
    };
    
    };

    
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cadastro imovel</title>
</head>
<body>
    <div>
        <h1>INFORME OS DADOS DO SEU IMOVEL</h1>
        <form action="" method="post">
            <label for="nome_salao">nome do seu salao</label>
            <input type="text" id="nome_salao" name="nome_salao" placeholder="Nome do seu salão">

            <label for="cep">CEP</label>
            <input type="int" id="cep" name="cep" placeholder="00000-00">
            
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required>
                <option value="">Selecione um estado</option>
                <option value="AC">Acre</option>
                <option value="AL">Alagoas</option>
                <option value="AP">Amapá</option>
                <option value="AM">Amazonas</option>
                <option value="BA">Bahia</option>
                <option value="CE">Ceará</option>
                <option value="DF">Distrito Federal</option>
                <option value="ES">Espírito Santo</option>
                <option value="GO">Goiás</option>
                <option value="MA">Maranhão</option>
                <option value="MT">Mato Grosso</option>
                <option value="MS">Mato Grosso do Sul</option>
                <option value="MG">Minas Gerais</option>
                <option value="PA">Pará</option>
                <option value="PB">Paraíba</option>
                <option value="PR">Paraná</option>
                <option value="PE">Pernambuco</option>
                <option value="PI">Piauí</option>
                <option value="RJ">Rio de Janeiro</option>
                <option value="RN">Rio Grande do Norte</option>
                <option value="RS">Rio Grande do Sul</option>
                <option value="RO">Rondônia</option>
                <option value="RR">Roraima</option>
                <option value="SC">Santa Catarina</option>
                <option value="SP">São Paulo</option>
                <option value="SE">Sergipe</option>
                <option value="TO">Tocantins</option>
            </select>
            
            <label for="cidade">CIDADE</label>
            <input type="text" id="cidade" name="cidade" placeholder="Digite a cidade" required>
            

            <label for="descricao">Faça uma breve descrição do seu estabelecimento</label>
            <input type="text" id="descricao" name="descricao" placeholder="Nosso salão é muito bonito e ocupa te 100 pessoas">

            <button type="submit">Mandar dados</button>

        </form>
    </div>
    
</body>
</html>