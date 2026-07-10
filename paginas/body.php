    <!-- ══════════════════════════════════════════════════════════
     2. HTML — cole antes do </div> do .container,
              junto com as outras abas (favoritos, viagens…)
══════════════════════════════════════════════════════════════ -->

<div class="container">
    <div id="filtros" class="tab-content">

    <!-- Cabeçalho com contador de filtros ativos -->
    <div class="filter-header">
        Filtros
        <span class="filter-badge" id="filter-badge">0</span>
    </div>

    <!-- ── TIPO DE LUGAR ──────────────────────────────────────
         Cada botão tem data-filter-type e data-value que o JS
         usa para registrar o filtro selecionado.
    ──────────────────────────────────────────────────────────-->
    <div class="filter-group">
        <p class="filter-group-title">Tipo de lugar</p>
        <div class="type-grid">

            <button class="type-btn" data-filter-type="tipo" data-value="praia">
                <i class="fa-solid fa-umbrella-beach"></i> Praia
            </button>
            <button class="type-btn" data-filter-type="tipo" data-value="casas">
                <i class="fa-solid fa-house-chimney"></i> Casas
            </button>
            <button class="type-btn" data-filter-type="tipo" data-value="campo">
                <i class="fa-solid fa-tree"></i> Campo
            </button>
            <button class="type-btn" data-filter-type="tipo" data-value="piscina">
                <i class="fa-solid fa-water-ladder"></i> Piscina
            </button>
            <button class="type-btn" data-filter-type="tipo" data-value="em-alta">
                <i class="fa-solid fa-fire"></i> Em alta
            </button>
            <button class="type-btn" data-filter-type="tipo" data-value="romantico">
                <i class="fa-solid fa-heart"></i> Romântico
            </button>

        </div>
    </div>

    <!-- ── COMODIDADES ────────────────────────────────────────
         Pílulas de texto simples, seleção múltipla.
    ──────────────────────────────────────────────────────────-->
    <div class="filter-group">
        <p class="filter-group-title">Comodidades</p>
        <div class="amenity-grid">

            <button class="amenity-btn" data-filter-type="comodidade" data-value="wifi">
                📶 Wi-Fi
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="pet-friendly">
                🐾 Pet friendly
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="familia">
                👨‍👩‍👧 Família
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="alto-padrao">
                ⭐ Alto padrão
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="vista-mar">
                🌊 Vista mar
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="ar-condicionado">
                ❄️ Ar-condicionado
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="cozinha">
                🍳 Cozinha equipada
            </button>
            <button class="amenity-btn" data-filter-type="comodidade" data-value="estacionamento">
                🚗 Estacionamento
            </button>

        </div>
    </div>

    <!-- ── AVALIAÇÃO MÍNIMA ───────────────────────────────────
         Botões de nota mínima (só um pode ser selecionado por vez).
    ──────────────────────────────────────────────────────────-->
    <div class="filter-group">
        <p class="filter-group-title">Avaliação mínima</p>
        <div class="rating-row">

            <button class="rating-btn" data-filter-type="rating" data-value="4">
                <i class="fa-solid fa-star"></i> 4+
            </button>
            <button class="rating-btn" data-filter-type="rating" data-value="4.5">
                <i class="fa-solid fa-star"></i> 4,5+
            </button>
            <button class="rating-btn" data-filter-type="rating" data-value="4.8">
                <i class="fa-solid fa-star"></i> 4,8+
            </button>

        </div>
    </div>

    <!-- ── FAIXA DE PREÇO ─────────────────────────────────────
         Slider que atualiza o rótulo "Até R$ X" em tempo real.
    ──────────────────────────────────────────────────────────-->
    <div class="filter-group">
        <p class="filter-group-title">Faixa de preço (por noite)</p>
        <div class="price-range-wrap">
            <input
                type="range"
                id="price-range"
                min="200"
                max="5000"
                step="100"
                value="2000"
                oninput="updatePriceLabel(this.value)"
            >
            <div class="price-labels">
                <span>R$ 200</span>
                Até <span id="price-value">R$ 2.000</span>
            </div>
        </div>
    </div>

    <!-- Rodapé com "Limpar tudo" + espaço antes do botão fixo -->
    <div class="filter-footer-row">
        <button class="btn-clear-all" onclick="clearFilterTab()">
            Limpar tudo
        </button>
        <span style="font-size:13px; color:var(--text-muted)" id="active-filter-summary">
            Nenhum filtro selecionado
        </span>
    </div>

    <!-- Espaço extra para o botão fixo não cobrir conteúdo -->
    <div style="height: 80px;"></div>

</div>
<!-- FIM ABA: FILTROS -->

<!-- ── BOTÃO FLUTUANTE (fora do #filtros, dentro do .container) -->
<div class="filter-apply-bar" id="filter-apply-bar">
    <button class="btn-apply" id="btn-apply-filters" onclick="applyFilters()">
        Mostrar resultados
    </button>
</div>


<!-- ══════════════════════════════════════════════════════════
     3. BOTTOM NAV — substitua o bloco <div class="bottom-nav">
              pelo abaixo (adicionando o item Filtros)
══════════════════════════════════════════════════════════════ -->

<div class="bottom-nav">
    <a class="active" data-target="explorar">
        <i class="fa-solid fa-magnifying-glass"></i>Explorar
    </a>
    <a data-target="favoritos">
        <i class="fa-regular fa-heart"></i>Favoritos
    </a>

    <!-- Item novo: Filtros -->
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



    <!-- ════════════════════════════════════════════════
         ABA: EXPLORAR
    ════════════════════════════════════════════════ -->
    <div id="explorar" class="tab-content active-tab">

        <div class="header">
            <h2>Explore</h2>
                <div class="header-buttons">
                    <a href="tela-login.php" class="btn-header">Entrar</a>
                    <a href="tela-cadastro.php" class="btn-header">Cadastrar</a>
                </div>
        </div>



        <!-- ── BUSCA POR TEXTO ──────────────────────────
             O evento "input" no JS chama filterCards() a cada tecla
             digitada, combinando o texto com os filtros de tag ativos.
        ──────────────────────────────────────────────── -->
        <div class="search-wrap">
            <div class="search-card">
                <i class="fa-solid fa-magnifying-glass" style="color:var(--text-muted)"></i>
                <input id="search-input" class="search-input" placeholder="Buscar por cidade ou tipo…">
                <!-- Botão X para limpar o campo de texto rapidamente -->
                <i id="clear-search" class="fa-solid fa-xmark"
                   style="color:var(--text-muted);cursor:pointer;display:none;"></i>
            </div>
        </div>

        <!-- ── FILTROS POR TAGS ─────────────────────────
             Linha 1: categorias com ícone (igual ao original).
             Linha 2: pílulas de texto para filtros mais específicos.

             Funcionamento:
             • Clicar numa categoria OU numa pílula ATIVA ou DESATIVA o filtro.
             • Vários filtros podem estar ativos ao mesmo tempo.
             • Um card é exibido se ele contiver PELO MENOS UMA das tags ativas.
             • Se nenhuma tag estiver ativa, todos os cards são exibidos.
        ──────────────────────────────────────────────── -->
        <div class="tags-section">

            <!-- Linha 1: categorias principais -->
            <div class="categories" id="categories">
                <div class="category-item" data-tag="praia">
                    <i class="fa-solid fa-umbrella-beach"></i> Praia
                </div>
                <div class="category-item" data-tag="casas">
                    <i class="fa-solid fa-house-chimney"></i> Casas
                </div>
                <div class="category-item" data-tag="campo">
                    <i class="fa-solid fa-tree"></i> Campo
                </div>
                <div class="category-item" data-tag="piscina">
                    <i class="fa-solid fa-water-ladder"></i> Piscina
                </div>
                <div class="category-item" data-tag="em-alta">
                    <i class="fa-solid fa-fire"></i> Em alta
                </div>
            </div>

            <!-- Linha 2: pílulas de texto (filtros complementares) -->
            <div class="tag-pills" id="tag-pills">
                <button class="tag-pill" data-tag="alto-padrao">Alto padrão</button>
                <button class="tag-pill" data-tag="familia">Família</button>
                <button class="tag-pill" data-tag="romantico">Romântico</button>
                <button class="tag-pill" data-tag="pet-friendly">Pet friendly</button>
                <button class="tag-pill" data-tag="vista-mar">Vista mar</button>
                <button class="tag-pill" data-tag="wifi">Wi-Fi</button>
            </div>
        </div>

        <!-- ── BARRA DE RESULTADOS ──────────────────────
             Mostra a contagem dinâmica e o botão "Limpar".
        ──────────────────────────────────────────────── -->
        <div class="results-bar">
            <span class="results-count" id="results-count"></span>
            <button class="clear-btn" id="clear-all-btn" onclick="clearAllFilters()">
                Limpar filtros
            </button>
        </div>

        <div class="section">

            <!-- Skeleton exibido durante o "carregamento" simulado -->
            <div id="loading-skeleton" class="cards">
                <div class="card">
                    <div class="skeleton skeleton-img"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text short"></div>
                </div>
            </div>

            <!-- ── LISTA DE CARDS ────────────────────────────
                 Cada card tem o atributo data-tags com as tags do imóvel,
                 separadas por vírgula e sem espaços.
                 O JS compara essas tags com os filtros ativos.
                 Também há data-search com texto livre para busca por texto.
            ────────────────────────────────────────────────── -->
            <div id="real-content" class="cards" style="display:none;">

                <!-- CARD 1 ─ Arraial do Cabo -->
                <div class="card"
                     data-tags="praia,vista-mar,em-alta,romantico"
                     data-search="arraial do cabo praia mar">
                    <span class="badge">Mais procurado</span>
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?1" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?2" alt="Foto 2">
                        <img src="https://picsum.photos/400/250?3" alt="Foto 3">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Arraial do Cabo, RJ <span class="rating"><i class="fa-solid fa-star"></i> 4.95</span></h3>
                        <p>A 2 km da praia · 3–5 de jul</p>
                        <p class="price">R$ 1.486 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🏖️ Praia</span>
                            <span class="card-tag-label">🌊 Vista mar</span>
                            <span class="card-tag-label">🔥 Em alta</span>
                            <span class="card-tag-label">💑 Romântico</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 2 ─ Cabo Frio -->
                <div class="card"
                     data-tags="praia,piscina,familia,wifi,pet-friendly"
                     data-search="cabo frio praia piscina família">
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?4" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?5" alt="Foto 2">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Cabo Frio, RJ <span class="rating"><i class="fa-solid fa-star"></i> 4.82</span></h3>
                        <p>Frente ao mar · 19–21 de jun</p>
                        <p class="price">R$ 950 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🏖️ Praia</span>
                            <span class="card-tag-label">🏊 Piscina</span>
                            <span class="card-tag-label">👨‍👩‍👧 Família</span>
                            <span class="card-tag-label">🐾 Pet friendly</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 3 ─ Gramado -->
                <div class="card"
                     data-tags="campo,alto-padrao,romantico,wifi"
                     data-search="gramado campo serra chalé romantIco">
                    <span class="badge">Novo</span>
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?6" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?7" alt="Foto 2">
                        <img src="https://picsum.photos/400/250?8" alt="Foto 3">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Gramado, RS <span class="rating"><i class="fa-solid fa-star"></i> 4.98</span></h3>
                        <p>Chalé na serra · 10–13 de ago</p>
                        <p class="price">R$ 2.100 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🌲 Campo</span>
                            <span class="card-tag-label">⭐ Alto padrão</span>
                            <span class="card-tag-label">💑 Romântico</span>
                            <span class="card-tag-label">📶 Wi-Fi</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 4 ─ Bonito -->
                <div class="card"
                     data-tags="campo,familia,pet-friendly,wifi"
                     data-search="bonito campo natureza família ecoturismo">
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?9" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?10" alt="Foto 2">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Bonito, MS <span class="rating"><i class="fa-solid fa-star"></i> 4.76</span></h3>
                        <p>Sítio com cachoeira · 5–8 de set</p>
                        <p class="price">R$ 780 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🌲 Campo</span>
                            <span class="card-tag-label">👨‍👩‍👧 Família</span>
                            <span class="card-tag-label">🐾 Pet friendly</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 5 ─ Florianópolis -->
                <div class="card"
                     data-tags="praia,piscina,alto-padrao,vista-mar,em-alta"
                     data-search="florianópolis floripa praia luxo villa">
                    <span class="badge">Em alta</span>
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?11" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?12" alt="Foto 2">
                        <img src="https://picsum.photos/400/250?13" alt="Foto 3">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Florianópolis, SC <span class="rating"><i class="fa-solid fa-star"></i> 4.91</span></h3>
                        <p>Villa com piscina infinita · 20–24 de jan</p>
                        <p class="price">R$ 3.400 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🏖️ Praia</span>
                            <span class="card-tag-label">🏊 Piscina</span>
                            <span class="card-tag-label">⭐ Alto padrão</span>
                            <span class="card-tag-label">🌊 Vista mar</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 6 ─ Campos do Jordão -->
                <div class="card"
                     data-tags="casas,campo,romantico,wifi,alto-padrao"
                     data-search="campos do jordão casa campo inverno">
                    <i class="fa-regular fa-heart fav-btn"></i>
                    <div class="image-carousel">
                        <img src="https://picsum.photos/400/250?14" alt="Foto 1">
                        <img src="https://picsum.photos/400/250?15" alt="Foto 2">
                    </div>
                    <div class="dots">
                        <div class="dot active-dot"></div>
                        <div class="dot"></div>
                    </div>
                    <div class="card-info">
                        <h3>Campos do Jordão, SP <span class="rating"><i class="fa-solid fa-star"></i> 4.88</span></h3>
                        <p>Casa com lareira · 15–18 de jul</p>
                        <p class="price">R$ 1.250 total</p>
                        <div class="card-tags">
                            <span class="card-tag-label">🏠 Casas</span>
                            <span class="card-tag-label">🌲 Campo</span>
                            <span class="card-tag-label">💑 Romântico</span>
                            <span class="card-tag-label">📶 Wi-Fi</span>
                        </div>
                    </div>
                </div>

            </div>
            <!-- FIM #real-content -->

            <!-- Mensagem exibida quando nenhum card corresponde ao filtro -->
            <div class="empty-state" id="empty-state">
                <i class="fa-solid fa-filter-circle-xmark"></i>
                <p>Nenhum imóvel encontrado</p>
                <span>Tente outros filtros ou limpe a busca.</span>
            </div>

        </div>
    </div>
    <!-- FIM ABA: EXPLORAR -->

    <!-- ════════════════════════════════════════════════
         ABAS SECUNDÁRIAS (placeholders)
    ════════════════════════════════════════════════ -->
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
        <script src="script.js"></script>
    </body>
</html>