<?php
require "../config/conexao.php";
session_start();

if (!isset($_SESSION["id_usuario"])) {
    die("Você precisa estar logado para cadastrar um salão.");
}
$id_dono = $_SESSION["id_usuario"];

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $categorias_selecionadas = $_POST["categoria"] ?? [];
    $nome_salao = trim($_POST["nome_salao"] ?? "");
    $cep        = trim($_POST["cep"] ?? "");
    $estado     = trim($_POST["estado"] ?? "");
    $cidade     = trim($_POST["cidade"] ?? "");
    $endereco   = trim($_POST["endereco"] ?? "");
    $descricao  = trim($_POST["descricao"] ?? "");
    $valorBruto = trim($_POST["valor"] ?? "");
    // Aceita tanto "1500" quanto "1.500,00" e converte para float
    $valor      = $valorBruto === "" ? null : (float) str_replace(",", ".", str_replace(".", "", $valorBruto));

    $pasta = "../imagens/saloes/";
    $permitidas = ["jpg", "jpeg", "png", "webp"];
    $tamanhoMaximo = 5 * 1024 * 1024; // 5MB por imagem

    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    // Guarda os caminhos já movidos para o disco, para conseguir apagar
    // caso algo dê errado depois e a transação precise ser desfeita.
    $arquivosMovidos = [];

    $conn->begin_transaction();

    try {

        if ($nome_salao === "") {
            throw new Exception("Informe o nome do salão.");
        }

        // 1) Cadastra o salão
        $sql = "INSERT INTO salao (nome_salao, cep, estado, cidade, endereco, descricao, valor, id_dono)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssdi",
            $nome_salao,
            $cep,
            $estado,
            $cidade,
            $endereco,
            $descricao,
            $valor,
            $id_dono
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao cadastrar o salão: " . $stmt->error);
        }

        $id_salao = $conn->insert_id;
        $stmt->close();

        // 2) Salva as categorias escolhidas (relação N:N)
        if (!empty($categorias_selecionadas)) {
            $sqlCategoria = "INSERT INTO salao_categoria (id_salao, id_categoria) VALUES (?, ?)";
            $stmtCategoria = $conn->prepare($sqlCategoria);

            foreach ($categorias_selecionadas as $id_categoria) {
                $id_categoria = (int) $id_categoria;
                if ($id_categoria <= 0) {
                    continue;
                }
                $stmtCategoria->bind_param("ii", $id_salao, $id_categoria);
                if (!$stmtCategoria->execute()) {
                    throw new Exception("Erro ao salvar categoria: " . $stmtCategoria->error);
                }
            }
            $stmtCategoria->close();
        }

        // 3) Salva todas as imagens enviadas (sem limite fixo de quantidade)
        if (!empty($_FILES["imagens"]["name"][0])) {
            $total = count($_FILES["imagens"]["name"]);

            $sqlImagem = "INSERT INTO imagem_salao (id_salao, caminho, imagem_principal) VALUES (?, ?, ?)";
            $stmtImagem = $conn->prepare($sqlImagem);

            $primeiraImagemSalva = false;

            for ($i = 0; $i < $total; $i++) {

                if ($_FILES["imagens"]["error"][$i] !== UPLOAD_ERR_OK) {
                    continue; // pula arquivos com erro de upload
                }

                if ($_FILES["imagens"]["size"][$i] > $tamanhoMaximo) {
                    continue; // pula arquivos maiores que 5MB
                }

                $extensao = strtolower(
                    pathinfo($_FILES["imagens"]["name"][$i], PATHINFO_EXTENSION)
                );

                if (!in_array($extensao, $permitidas)) {
                    continue; // pula extensões não permitidas
                }

                $nomeImagem = uniqid("salao_", true) . "." . $extensao;
                $destino = $pasta . $nomeImagem;

                if (!move_uploaded_file($_FILES["imagens"]["tmp_name"][$i], $destino)) {
                    continue;
                }

                $arquivosMovidos[] = $destino;

                $caminhoBanco = "imagens/saloes/" . $nomeImagem;
                $principal = !$primeiraImagemSalva ? 1 : 0;

                $stmtImagem->bind_param("isi", $id_salao, $caminhoBanco, $principal);

                if (!$stmtImagem->execute()) {
                    throw new Exception("Erro ao salvar imagem: " . $stmtImagem->error);
                }

                $primeiraImagemSalva = true;
            }

            $stmtImagem->close();

            if (!$primeiraImagemSalva) {
                throw new Exception("Nenhuma imagem válida foi enviada. Use apenas JPG, JPEG, PNG ou WEBP.");
            }
        } else {
            throw new Exception("Envie ao menos uma foto do salão.");
        }

        $conn->commit();
        header("Location: body.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();

        // Desfaz os arquivos que já tinham sido movidos para o disco
        foreach ($arquivosMovidos as $arquivo) {
            if (file_exists($arquivo)) {
                unlink($arquivo);
            }
        }

        $erro = $e->getMessage();
    }
}

// Busca as categorias direto do banco para montar o <select> dinamicamente
$categoriasBanco = [];
$resultCategorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias_salao ORDER BY nome_categoria");
if ($resultCategorias) {
    while ($linha = $resultCategorias->fetch_assoc()) {
        $categoriasBanco[] = $linha;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/EstiloCadastroImovel.css">
    <title>Cadastro de imóvel</title>
    <style>
        .categoria-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eee;
            border-radius: 16px;
            padding: 4px 10px;
            margin: 4px 4px 0 0;
            font-size: 14px;
        }
        .categoria-chip button {
            border: none;
            background: none;
            cursor: pointer;
            font-weight: bold;
        }
        #preview-imagens {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-item {
            position: relative;
            width: 120px;
        }
        .preview-item img {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .preview-item button {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: none;
            background: #c0392b;
            color: #fff;
            cursor: pointer;
            line-height: 1;
        }
        .erro {
            color: #c0392b;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div>
        <h1>INFORME OS DADOS DO SEU IMOVEL</h1>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="form-cadastro">
            <label for="nome_salao">Nome do seu salão</label>
            <input type="text" id="nome_salao" name="nome_salao" placeholder="Nome do seu salão" required>
            <br><br>

            <label for="cep">CEP</label>
            <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required>
            <br><br>

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
            <br><br>

            <label for="cidade">CIDADE</label>
            <input type="text" id="cidade" name="cidade" placeholder="Digite a cidade" required>
            <br><br>

            <label for="endereco">ENDEREÇO</label>
            <input type="text" id="endereco" name="endereco" placeholder="Digite o endereço" required>
            <br><br>

            <label for="valor">VALOR DO ALUGUEL (R$)</label>
            <input type="text" id="valor" name="valor" placeholder="Ex: 1500,00" required>
            <br><br>

            <label>Fotos do salão</label><br>
            <input type="file" id="input-imagens" accept="image/png,image/jpeg,image/jpg,image/webp" multiple>
            <div id="preview-imagens"></div>
            <br>

            <label for="descricao">Faça uma breve descrição do seu estabelecimento</label>
            <input type="text" id="descricao" name="descricao" placeholder="Nosso salão é muito bonito e ocupa até 100 pessoas">
            <br><br>

            <label for="categoria">Categorias do Salão:</label>
            <select id="categoria">
                <option value="">Escolha uma categoria</option>
                <?php foreach ($categoriasBanco as $cat): ?>
                    <option value="<?= (int) $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" onclick="adicionarCategoria()">Adicionar</button>

            <div id="selecionadas"></div>

            <br><br>
            <button type="submit">Finalizar</button>
        </form>
    </div>

    <script>
        // ---------- Categorias ----------
        const select = document.getElementById("categoria");

        function adicionarCategoria() {
            const id = select.value;
            const nome = select.options[select.selectedIndex].text;

            if (!id || document.querySelector(`[data-id="${id}"]`)) {
                return;
            }

            const chip = document.createElement("span");
            chip.className = "categoria-chip";
            chip.dataset.id = id;
            chip.innerHTML = `
                ${nome}
                <button type="button" aria-label="Remover">×</button>
                <input type="hidden" name="categoria[]" value="${id}">
            `;
            chip.querySelector("button").addEventListener("click", () => chip.remove());

            document.getElementById("selecionadas").appendChild(chip);
            select.value = "";
        }

        // ---------- Imagens (adicionar aos poucos, com preview e remoção) ----------
        // Usamos DataTransfer para manter uma lista acumulada de arquivos,
        // já que um <input type="file"> normal substitui a seleção anterior
        // a cada escolha.
        const inputImagens = document.getElementById("input-imagens");
        const previewContainer = document.getElementById("preview-imagens");
        const form = document.getElementById("form-cadastro");

        let arquivosSelecionados = new DataTransfer();

        inputImagens.addEventListener("change", () => {
            for (const file of inputImagens.files) {
                arquivosSelecionados.items.add(file);
            }
            inputImagens.value = ""; // limpa para permitir escolher os mesmos arquivos de novo se precisar
            renderPreview();
        });

        function renderPreview() {
            previewContainer.innerHTML = "";

            [...arquivosSelecionados.files].forEach((file, index) => {
                const item = document.createElement("div");
                item.className = "preview-item";

                const img = document.createElement("img");
                img.src = URL.createObjectURL(file);

                const removerBtn = document.createElement("button");
                removerBtn.type = "button";
                removerBtn.textContent = "×";
                removerBtn.addEventListener("click", () => removerImagem(index));

                item.appendChild(img);
                item.appendChild(removerBtn);
                previewContainer.appendChild(item);
            });
        }

        function removerImagem(index) {
            const novaLista = new DataTransfer();
            [...arquivosSelecionados.files].forEach((file, i) => {
                if (i !== index) {
                    novaLista.items.add(file);
                }
            });
            arquivosSelecionados = novaLista;
            renderPreview();
        }

        // No momento do envio, criamos o input real com o nome "imagens[]"
        // já preenchido com todos os arquivos acumulados.
        form.addEventListener("submit", (e) => {
            if (arquivosSelecionados.files.length === 0) {
                e.preventDefault();
                alert("Envie ao menos uma foto do salão.");
                return;
            }

            const inputFinal = document.createElement("input");
            inputFinal.type = "file";
            inputFinal.name = "imagens[]";
            inputFinal.multiple = true;
            inputFinal.style.display = "none";
            inputFinal.files = arquivosSelecionados.files;
            form.appendChild(inputFinal);
        });
    </script>
</body>
</html>
