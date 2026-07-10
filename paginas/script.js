
const state = {
    activeTags: new Set(),   // Conjunto de tags atualmente ativas (evita duplicatas)
    searchText: ''           // Texto digitado no campo de busca
};

/* ═══════════════════════════════════════════════════════
   REFERÊNCIAS AOS ELEMENTOS DO DOM
═══════════════════════════════════════════════════════ */
const allCards      = document.querySelectorAll('#real-content .card');
const resultsCount  = document.getElementById('results-count');
const clearAllBtn   = document.getElementById('clear-all-btn');
const emptyState    = document.getElementById('empty-state');
const searchInput   = document.getElementById('search-input');
const clearSearchBtn= document.getElementById('clear-search');

/* ═══════════════════════════════════════════════════════
   FUNÇÃO PRINCIPAL: filterCards()
   Chamada sempre que o usuário muda algum filtro.
   Lógica:
   1. Para cada card, pega suas tags (data-tags) e o texto (data-search)
   2. Verifica se o card passa no filtro de texto (searchText)
   3. Verifica se o card passa no filtro de tags (activeTags)
   4. Mostra ou esconde o card conforme o resultado
   5. Atualiza o contador e o estado vazio
═══════════════════════════════════════════════════════ */
function filterCards() {
    let visibleCount = 0;

    allCards.forEach(card => {
        // Lê as tags do card (ex: "praia,piscina,familia")
        const cardTags    = card.dataset.tags.split(',');
        // Lê o texto de busca do card (normalizado em minúsculas)
        const cardSearch  = (card.dataset.search || '').toLowerCase();

        /* ── Filtro de TEXTO ──────────────────────────────
           O card passa se o texto digitado está contido
           no data-search OU no texto visível do card.
        ─────────────────────────────────────────────────── */
        const textQuery   = state.searchText.toLowerCase().trim();
        const passesText  = textQuery === '' ||
                            cardSearch.includes(textQuery) ||
                            card.innerText.toLowerCase().includes(textQuery);

        /* ── Filtro de TAGS ───────────────────────────────
           Se nenhuma tag está ativa → todos os cards passam.
           Se há tags ativas → o card precisa ter PELO MENOS
           uma das tags selecionadas (lógica OR).
           Para lógica AND (card deve ter TODAS as tags),
           substituir "some" por "every".
        ─────────────────────────────────────────────────── */
        const passesTags  = state.activeTags.size === 0 ||
                            [...state.activeTags].some(tag => cardTags.includes(tag));

        // Card é visível somente se passa nos dois filtros
        const isVisible   = passesText && passesTags;

        card.classList.toggle('hidden', !isVisible);
        if (isVisible) visibleCount++;
    });

    /* ── Atualiza o contador ──────────────────────────── */
    const hasAnyFilter = state.activeTags.size > 0 || state.searchText.trim() !== '';

    if (hasAnyFilter) {
        resultsCount.textContent =
            visibleCount === 0 ? 'Nenhum imóvel encontrado'
            : visibleCount === 1 ? '1 imóvel encontrado'
            : `${visibleCount} imóveis encontrados`;
        clearAllBtn.classList.add('visible');
    } else {
        // Sem filtro ativo: esconde o contador e o botão "Limpar"
        resultsCount.textContent = '';
        clearAllBtn.classList.remove('visible');
    }

    /* ── Estado vazio ─────────────────────────────────── */
    emptyState.classList.toggle('visible', visibleCount === 0);
}

/* ═══════════════════════════════════════════════════════
   TOGGLE DE UMA TAG
   Chamada ao clicar num item de categoria ou numa pílula.
   • Se a tag já está ativa → desativa e remove do Set
   • Se a tag não está ativa → ativa e adiciona ao Set
═══════════════════════════════════════════════════════ */
function toggleTag(tag, element) {
    if (state.activeTags.has(tag)) {
        state.activeTags.delete(tag);
        element.classList.remove('active');
    } else {
        state.activeTags.add(tag);
        element.classList.add('active');
    }

    // Sincroniza o visual: se uma categoria E uma pílula têm a mesma tag,
    // ambas devem refletir o estado ativo/inativo ao mesmo tempo.
    syncTagUI(tag);

    filterCards();
}

/* ═══════════════════════════════════════════════════════
   SINCRONIZAR VISUAL DAS TAGS
   Garante que categoria e pílula com a mesma tag
   fiquem sempre com o mesmo estado visual.
═══════════════════════════════════════════════════════ */
function syncTagUI(tag) {
    const isActive = state.activeTags.has(tag);
    document.querySelectorAll(`[data-tag="${tag}"]`).forEach(el => {
        el.classList.toggle('active', isActive);
    });
}

/* ═══════════════════════════════════════════════════════
   LIMPAR TODOS OS FILTROS
   Reseta tags e texto, atualiza o visual e refiltra.
═══════════════════════════════════════════════════════ */
function clearAllFilters() {
    // Limpa o Set de tags e remove a classe 'active' de todos os elementos
    state.activeTags.clear();
    document.querySelectorAll('[data-tag]').forEach(el => el.classList.remove('active'));

    // Limpa o campo de texto
    state.searchText = '';
    searchInput.value = '';
    clearSearchBtn.style.display = 'none';

    filterCards();
}

/* ═══════════════════════════════════════════════════════
   EVENTOS: CATEGORIAS (ícone + texto)
═══════════════════════════════════════════════════════ */
document.querySelectorAll('.category-item').forEach(item => {
    item.addEventListener('click', function() {
        // Lê a tag armazenada no atributo data-tag do elemento
        toggleTag(this.dataset.tag, this);
    });
});

/* ═══════════════════════════════════════════════════════
   EVENTOS: PÍLULAS (texto simples)
═══════════════════════════════════════════════════════ */
document.querySelectorAll('.tag-pill').forEach(pill => {
    pill.addEventListener('click', function() {
        toggleTag(this.dataset.tag, this);
    });
});

/* ═══════════════════════════════════════════════════════
   EVENTO: BUSCA POR TEXTO
   Filtra a cada tecla digitada (debounce leve via input).
═══════════════════════════════════════════════════════ */
searchInput.addEventListener('input', function() {
    state.searchText = this.value;
    // Mostra ou esconde o X de limpar o campo
    clearSearchBtn.style.display = this.value.length > 0 ? 'block' : 'none';
    filterCards();
});

/* ═══════════════════════════════════════════════════════
   EVENTO: LIMPAR CAMPO DE TEXTO (botão X)
═══════════════════════════════════════════════════════ */
clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    state.searchText  = '';
    this.style.display = 'none';
    filterCards();
});

/* ═══════════════════════════════════════════════════════
   SKELETON LOADING
   Simula 1.5s de carregamento antes de exibir os cards.
   Após exibir, roda filterCards() para respeitar
   filtros que porventura já estejam ativos.
═══════════════════════════════════════════════════════ */
setTimeout(() => {
    document.getElementById('loading-skeleton').style.display = 'none';
    document.getElementById('real-content').style.display     = 'flex';
    filterCards(); // garante estado correto logo após exibir
}, 1500);

/* ═══════════════════════════════════════════════════════
   NAVEGAÇÃO POR ABAS
═══════════════════════════════════════════════════════ */
document.querySelectorAll('.bottom-nav a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.bottom-nav a').forEach(n => n.classList.remove('active'));
        this.classList.add('active');
        const id = this.getAttribute('data-target');
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active-tab'));
        const tab = document.getElementById(id);
        if (tab) tab.classList.add('active-tab');
    });
});

/* ═══════════════════════════════════════════════════════
   MODO ESCURO
═══════════════════════════════════════════════════════ */
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    this.innerHTML = document.body.classList.contains('dark-mode')
        ? '<i class="fa-solid fa-sun"></i> Ativar Modo Claro'
        : '<i class="fa-solid fa-moon"></i> Ativar Modo Escuro';
});

/* ═══════════════════════════════════════════════════════
   FAVORITAR (coração nos cards)
═══════════════════════════════════════════════════════ */
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const isFav = this.classList.contains('fa-solid');
        this.classList.toggle('fa-regular', isFav);
        this.classList.toggle('fa-solid', !isFav);
        this.style.color = isFav ? 'white' : '#ff385c';
    });
});

/* ═══════════════════════════════════════════════════════════
   ESTADO DOS FILTROS DA ABA
   Objeto separado do filtro inline da aba Explorar.
   Guarda os valores selecionados em cada grupo.
═══════════════════════════════════════════════════════════ */
const filterTabState = {
    tipo:       new Set(),   // Tipos de lugar (praia, casas…)
    comodidade: new Set(),   // Comodidades (wifi, piscina…)
    rating:     null,        // Avaliação mínima (4 | 4.5 | 4.8 | null)
    priceMax:   2000         // Preço máximo em reais
};

/* ═══════════════════════════════════════════════════════════
   TOGGLE DE BOTÃO (tipo / comodidade)
   Chamada ao clicar em qualquer .type-btn ou .amenity-btn.
   - tipo/comodidade → múltipla seleção (Set)
   - rating → seleção única (somente um ativo por vez)
═══════════════════════════════════════════════════════════ */
function handleFilterBtnClick(btn) {
    const type  = btn.dataset.filterType;  // "tipo" | "comodidade" | "rating"
    const value = btn.dataset.value;

    if (type === 'rating') {
        /* Seleção única: clicou no mesmo → desativa; outro → troca */
        const isSame = filterTabState.rating === value;
        document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
        filterTabState.rating = isSame ? null : value;
        if (!isSame) btn.classList.add('active');

    } else {
        /* Seleção múltipla: toggle normal */
        if (filterTabState[type].has(value)) {
            filterTabState[type].delete(value);
            btn.classList.remove('active');
        } else {
            filterTabState[type].add(value);
            btn.classList.add('active');
        }
    }

    updateFilterUI();
}

/* ═══════════════════════════════════════════════════════════
   ATUALIZAR INTERFACE DA ABA FILTROS
   Recalcula o total de filtros ativos e atualiza:
   - Badge no cabeçalho (número)
   - Badge no ícone do bottom nav (ponto vermelho)
   - Resumo textual no rodapé
   - Visibilidade do botão "Mostrar resultados"
═══════════════════════════════════════════════════════════ */
function updateFilterUI() {
    const total = filterTabState.tipo.size
                + filterTabState.comodidade.size
                + (filterTabState.rating ? 1 : 0);

    /* Badge no cabeçalho */
    const badge = document.getElementById('filter-badge');
    if (badge) {
        badge.textContent = total;
        badge.classList.toggle('visible', total > 0);
    }

    /* Botão flutuante "Mostrar resultados" */
    const applyBar = document.getElementById('filter-apply-bar');
    if (applyBar) {
        applyBar.classList.toggle('visible', total > 0);

        const btn = document.getElementById('btn-apply-filters');
        if (btn) {
            btn.textContent = total === 0
                ? 'Mostrar resultados'
                : `Mostrar resultados (${total} filtro${total > 1 ? 's' : ''})`;
        }
    }

    /* Resumo textual no rodapé da aba */
    const summary = document.getElementById('active-filter-summary');
    if (summary) {
        summary.textContent = total === 0
            ? 'Nenhum filtro selecionado'
            : `${total} filtro${total > 1 ? 's' : ''} ativo${total > 1 ? 's' : ''}`;
    }
}

/* ═══════════════════════════════════════════════════════════
   ATUALIZAR LABEL DO PREÇO
   Chamada pelo oninput do <input type="range">.
   Atualiza o texto "Até R$ X" e o gradiente da barra.
═══════════════════════════════════════════════════════════ */
function updatePriceLabel(value) {
    filterTabState.priceMax = parseInt(value);

    /* Texto "Até R$ X.XXX" */
    const label = document.getElementById('price-value');
    if (label) {
        label.textContent = 'R$ ' + parseInt(value).toLocaleString('pt-BR');
    }

    /* Gradiente da barra do slider */
    const slider = document.getElementById('price-range');
    if (slider) {
        const min = parseInt(slider.min);
        const max = parseInt(slider.max);
        const pct = ((value - min) / (max - min) * 100).toFixed(1) + '%';
        slider.style.setProperty('--pct', pct);
    }
}

/* ═══════════════════════════════════════════════════════════
   LIMPAR TODOS OS FILTROS DA ABA
   Reseta o estado, remove .active de todos os botões
   e atualiza a interface.
═══════════════════════════════════════════════════════════ */
function clearFilterTab() {
    filterTabState.tipo.clear();
    filterTabState.comodidade.clear();
    filterTabState.rating  = null;
    filterTabState.priceMax = 2000;

    /* Remove estilo ativo de todos os botões de filtro */
    document.querySelectorAll('.type-btn, .amenity-btn, .rating-btn')
            .forEach(b => b.classList.remove('active'));

    /* Reseta o slider de preço */
    const slider = document.getElementById('price-range');
    if (slider) {
        slider.value = 2000;
        updatePriceLabel(2000);
    }

    updateFilterUI();
}

/* ═══════════════════════════════════════════════════════════
   APLICAR FILTROS E VOLTAR PARA EXPLORAR
   Lê o filterTabState, executa a lógica de filtragem nos
   cards da aba Explorar e navega de volta para ela.
═══════════════════════════════════════════════════════════ */
function applyFilters() {
    const cards = document.querySelectorAll('#real-content .card');

    cards.forEach(card => {
        const tags        = card.dataset.tags ? card.dataset.tags.split(',') : [];
        const cardRating  = parseFloat(card.dataset.rating  || '5');
        const cardPrice   = parseInt(card.dataset.price     || '0', 10);

        /* ── Filtro por tipo de lugar ──────────────────── */
        const passesTipo = filterTabState.tipo.size === 0
            || [...filterTabState.tipo].some(t => tags.includes(t));

        /* ── Filtro por comodidade ─────────────────────── */
        const passesComodidade = filterTabState.comodidade.size === 0
            || [...filterTabState.comodidade].some(c => tags.includes(c));

        /* ── Filtro por avaliação ──────────────────────── */
        const passesRating = !filterTabState.rating
            || cardRating >= parseFloat(filterTabState.rating);

        /* ── Filtro por preço ─────────────────────────── */
        const passesPrice = cardPrice === 0
            || cardPrice <= filterTabState.priceMax;

        /* Card visível somente se passar em TODOS os filtros */
        card.classList.toggle('hidden', !(passesTipo && passesComodidade && passesRating && passesPrice));
    });

    /* Volta para a aba Explorar */
    document.querySelectorAll('.bottom-nav a').forEach(a => a.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active-tab'));

    const explorarLink = document.querySelector('[data-target="explorar"]');
    const explorarTab  = document.getElementById('explorar');
    if (explorarLink) explorarLink.classList.add('active');
    if (explorarTab)  explorarTab.classList.add('active-tab');

    /* Esconde o botão flutuante ao sair da aba */
    const applyBar = document.getElementById('filter-apply-bar');
    if (applyBar) applyBar.classList.remove('visible');
}

/* ═══════════════════════════════════════════════════════════
   REGISTRAR EVENTOS NOS BOTÕES DE FILTRO
   Roda após o DOM carregar para pegar todos os botões.
═══════════════════════════════════════════════════════════ */
document.querySelectorAll('.type-btn, .amenity-btn, .rating-btn')
        .forEach(btn => {
            btn.addEventListener('click', () => handleFilterBtnClick(btn));
        });

/* Inicializa o slider de preço com o valor padrão */
updatePriceLabel(2000);

/* ═══════════════════════════════════════════════════════════
   MOSTRAR/ESCONDER BOTÃO FLUTUANTE AO MUDAR DE ABA
   Integra com o sistema de navegação por abas já existente.
   Adicione este bloco DENTRO do forEach do navLinks existente,
   ou substitua o addEventListener de navegação pelo abaixo.
═══════════════════════════════════════════════════════════ */
document.querySelectorAll('.bottom-nav a').forEach(link => {
    link.addEventListener('click', function() {

        /* Lógica de aba existente (não alterar) */
        document.querySelectorAll('.bottom-nav a').forEach(n => n.classList.remove('active'));
        this.classList.add('active');
        const targetId = this.getAttribute('data-target');
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active-tab'));
        const tab = document.getElementById(targetId);
        if (tab) tab.classList.add('active-tab');

        /* Novo: controla visibilidade do botão flutuante */
        const applyBar = document.getElementById('filter-apply-bar');
        if (!applyBar) return;

        const total = filterTabState.tipo.size
                    + filterTabState.comodidade.size
                    + (filterTabState.rating ? 1 : 0);

        /* Botão só aparece se estiver na aba Filtros E houver filtros ativos */
        applyBar.classList.toggle('visible', targetId === 'filtros' && total > 0);
    });
});

function applyFilters() {

    const cards = document.querySelectorAll("#real-content .card");
    let resultados = 0;

    cards.forEach(card => {

        const tags = (card.dataset.tags || "").split(",");
        const rating = parseFloat(card.dataset.rating || "5");
        const price = parseInt(card.dataset.price || "999999");

        const passaTipo =
            filterTabState.tipo.size === 0 ||
            [...filterTabState.tipo].some(tipo => tags.includes(tipo));

        const passaComodidade =
            filterTabState.comodidade.size === 0 ||
            [...filterTabState.comodidade].some(item => tags.includes(item));

        const passaRating =
            !filterTabState.rating ||
            rating >= Number(filterTabState.rating);

        const passaPreco =
            price <= filterTabState.priceMax;

        const mostrar =
            passaTipo &&
            passaComodidade &&
            passaRating &&
            passaPreco;

        card.classList.toggle("hidden", !mostrar);

        if (mostrar) resultados++;
    });

    // Atualiza contador
    const contador = document.getElementById("results-count");

    if (contador) {

        if(resultados == 0){
            contador.textContent = "Nenhum imóvel encontrado";
        }
        else if(resultados == 1){
            contador.textContent = "1 imóvel encontrado";
        }
        else{
            contador.textContent = `${resultados} imóveis encontrados`;
        }

    }

    // Estado vazio
    document
        .getElementById("empty-state")
        .classList.toggle("visible", resultados === 0);

    // Ir para Explorar
    document.querySelectorAll(".tab-content")
        .forEach(tab => tab.classList.remove("active-tab"));

    document.getElementById("explorar")
        .classList.add("active-tab");

    document.querySelectorAll(".bottom-nav a")
        .forEach(link => link.classList.remove("active"));

    document.querySelector('[data-target="explorar"]')
        .classList.add("active");

    document.getElementById("filter-apply-bar")
        .classList.remove("visible");
}
