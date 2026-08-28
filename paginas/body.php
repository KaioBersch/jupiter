<?php
// Este arquivo espera que $conn (mysqli) já exista — é aberto em index.php

// Busca todas as categorias para montar os filtros dinamicamente
$todasCategorias = [];
$resCategorias = $conn->query("SELECT id_categoria, nome_categoria FROM categorias_salao ORDER BY nome_categoria");
if ($resCategorias) {
    while ($linha = $resCategorias->fetch_assoc()) {
        $todasCategorias[] = $linha;
    }
}

// Busca todos os salões cadastrados, já com a foto principal e as categorias
$saloes = [];
$sqlSaloes = "
    SELECT
        s.id_salao,
        s.nome_salao,
        s.cidade,
        s.estado,
        s.endereco,
        s.descricao,
        s.valor,
        (
            SELECT caminho FROM imagem_salao
            WHERE id_salao = s.id_salao
            ORDER BY imagem_principal DESC, id_imagem ASC
            LIMIT 1
        ) AS imagem,
        GROUP_CONCAT(DISTINCT sc.id_categoria) AS ids_categorias,
        GROUP_CONCAT(DISTINCT c.nome_categoria SEPARATOR ', ') AS nomes_categorias
    FROM salao s
    LEFT JOIN salao_categoria sc ON sc.id_salao = s.id_salao
    LEFT JOIN categorias_salao c ON c.id_categoria = sc.id_categoria
    WHERE s.situacao = 'aceito'
    GROUP BY s.id_salao
    ORDER BY s.id_salao DESC
";
$resSaloes = $conn->query($sqlSaloes);
if ($resSaloes) {
    while ($linha = $resSaloes->fetch_assoc()) {
        $saloes[] = $linha;
    }
}

$maiorValor = 5000;
foreach ($saloes as $s) {
    if ($s['valor'] !== null && $s['valor'] > $maiorValor) {
        $maiorValor = (int) ceil($s['valor'] / 100) * 100;
    }
}
?>
<div class="container">

    <div id="filtros" class="tab-content">

        <div class="filter-header">
            Filtros
            <span class="filter-badge" id="filter-badge">0</span>
        </div>

        <div class="filter-group">
            <p class="filter-group-title">Categorias</p>
            <div class="type-grid">
                <?php foreach ($todasCategorias as $cat): ?>
                    <button class="type-btn" data-filter-type="tipo" data-value="<?= (int) $cat['id_categoria'] ?>">
                        <?= htmlspecialchars($cat['nome_categoria']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="filter-group">
            <p class="filter-group-title">Faixa de preço</p>
            <div class="price-range-wrap">
                <input
                    type="range"
                    id="price-range"
                    min="0"
                    max="<?= (int) $maiorValor ?>"
                    step="100"
                    value="<?= (int) $maiorValor ?>"
                    oninput="updatePriceLabel(this.value)"
                >
                <div class="price-labels">
                    <span>R$ 0</span>
                    Até <span id="price-value">R$ <?= number_format($maiorValor, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="filter-footer-row">
            <button class="btn-clear-all" onclick="clearFilterTab()">
                Limpar tudo
            </button>
            <span style="font-size:13px; color:var(--text-muted)" id="active-filter-summary">
                Nenhum filtro selecionado
            </span>
        </div>

        <div style="height: 80px;"></div>

    </div>
    <div class="filter-apply-bar" id="filter-apply-bar">
        <button class="btn-apply" id="btn-apply-filters" onclick="applyFilters()">
            Mostrar resultados
        </button>
    </div>

    <div id="explorar" class="tab-content active-tab">

    <!-- area de botoes do topo -->
        <div class="header">
            <h2>Explore</h2>
            <div class="header-buttons">
                <?php if (isset($_SESSION["id_usuario"])): ?>
                    <span class="btn-header">Olá, <?= htmlspecialchars($_SESSION["nome"] ?? "") ?></span>
                    <a href="logout.php" class="btn-header">Sair</a>
                <?php else: ?>
                    <a href="tela-login.php" class="btn-header">Entrar</a>
                    <a href="tela-cadastro.php" class="btn-header">Cadastrar</a>
                <?php endif; ?>
                <a href="tela_cadastro_imovel.php" class="btn-header">seja um anfitriao</a>
            </div>
        </div>

        <div class="search-wrap">
            <div class="search-card">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted)"></i>
                <input id="search-input" class="search-input" placeholder="Buscar por cidade ou categoria…">
                <i id="clear-search" class="fa-solid fa-xmark" style="color:var(--text-muted);cursor:pointer;display:none;"></i>
            </div>
        </div>

        <div class="tags-section">
            <div class="tag-pills" id="tag-pills">
                <?php foreach ($todasCategorias as $cat): ?>
                    <button class="tag-pill" data-tag="<?= (int) $cat['id_categoria'] ?>">
                        <?= htmlspecialchars($cat['nome_categoria']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="results-bar">
            <span class="results-count" id="results-count"></span>
            <button class="clear-btn" id="clear-all-btn" onclick="clearAllFilters()">
                Limpar filtros
            </button>
        </div>

        <div class="section">

            <div id="loading-skeleton" class="cards" style="display:none;">
                <div class="card">
                    <div class="skeleton skeleton-img"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text short"></div>
                </div>
            </div>

            <div id="real-content" class="cards">

                <?php if (empty($saloes)): ?>
                    <p style="padding: 20px; color: var(--text-muted);">
                        Nenhum salão cadastrado ainda.
                    </p>
                <?php else: ?>
                    <?php foreach ($saloes as $s): ?>
                        <?php
                            $tags = $s['ids_categorias'] ?? '';
                            $textoBusca = strtolower(
                                $s['nome_salao'] . ' ' . $s['cidade'] . ' ' . $s['estado'] . ' ' . ($s['nomes_categorias'] ?? '')
                            );
                            $imagemSrc = $s['imagem'] ? '../' . htmlspecialchars($s['imagem']) : '../imagens/placeholder.jpg';
                            $precoFormatado = $s['valor'] !== null ? 'R$ ' . number_format($s['valor'], 2, ',', '.') : 'Sob consulta';
                        ?>
                        <div class="card"
                             data-tags="<?= htmlspecialchars($tags) ?>"
                             data-search="<?= htmlspecialchars($textoBusca) ?>"
                             data-price="<?= $s['valor'] !== null ? (float) $s['valor'] : 0 ?>">
                            <i class="fa-regular fa-heart fav-btn"></i>
                            <div class="image-carousel">
                                <img src="<?= $imagemSrc ?>" alt="<?= htmlspecialchars($s['nome_salao']) ?>">
                            </div>
                            <div class="card-info">
                                <h3><?= htmlspecialchars($s['nome_salao']) ?></h3>
                                <p><?= htmlspecialchars($s['cidade'] . ' - ' . $s['estado']) ?></p>
                                <?php if (!empty($s['nomes_categorias'])): ?>
                                    <p style="font-size:12px;color:var(--text-muted);">
                                        <?= htmlspecialchars($s['nomes_categorias']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="price"><?= $precoFormatado ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div id="favoritos" class="tab-content">
        <div class="header">Favoritos</div>
        <div class="placeholder-screen">
            <i class="fa-regular fa-heart" style="font-size:40px;margin-bottom:15px;"></i>
            <p>Seus favoritos aparecerão aqui.</p>
        </div>
    </div>

    <div id="viagens" class="tab-content">
        <div class="header">Viagens</div>
        <div class="placeholder-screen">
            <i class="fa-solid fa-plane" style="font-size:40px;margin-bottom:15px;"></i>
            <p>Nenhuma viagem programada.</p>
        </div>
    </div>

    <div id="mensagens" class="tab-content">
        <div class="header">Mensagens</div>
        <div class="placeholder-screen">
            <i class="fa-regular fa-message" style="font-size:40px;margin-bottom:15px;"></i>
            <p>Sua caixa de entrada está vazia.</p>
        </div>
    </div>

    <div id="perfil" class="tab-content">
        <div class="header">Perfil</div>
        <div class="section">
            <button id="theme-toggle" class="btn">
                <i class="fa-solid fa-moon"></i> Ativar Modo Escuro
            </button>
        </div>
    </div>

    <div class="bottom-nav">
        <a class="active" data-target="explorar">
            <i class="fa-solid fa-magnifying-glass"></i>Explorar
        </a>
        <a data-target="favoritos">
            <i class="fa-regular fa-heart"></i>Favoritos
        </a>
        <a data-target="filtros" id="nav-filtros">
            <i class="fa-solid fa-sliders"></i>Filtros
        </a>
        <a data-target="mensagens">
            <i class="fa-regular fa-message"></i>Mensagens
        </a>
        <a data-target="perfil">
            <i class="fa-regular fa-circle-user"></i>Perfil
        </a>
    </div>

</div> <script src="script.js"></script>
</body>
</html>
