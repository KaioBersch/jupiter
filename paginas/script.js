/* ═══════════════════════════════════════════════════════
   ESTADO GLOBAL DA APLICAÇÃO (ORIGINAL)
═══════════════════════════════════════════════════════ */
const state = {
    activeTags: new Set(),   // Conjunto de tags ativas
    searchText: ''           // Texto de busca
};

// Filtros da aba de Filtros Avançados
const filterTabState = {
    tipo: new Set(),
    comodidade: new Set(),
    rating: null,
    priceMax: 2000
};

/* ═══════════════════════════════════════════════════════
   REFERÊNCIAS AOS ELEMENTOS DO DOM (CENTRALIZADO)
═══════════════════════════════════════════════════════ */
const dom = {
    allCards:       document.querySelectorAll('#real-content .card'),
    resultsCount:   document.getElementById('results-count'),
    clearAllBtn:    document.getElementById('clear-all-btn'),
    emptyState:     document.getElementById('empty-state'),
    searchInput:    document.getElementById('search-input'),
    clearSearchBtn: document.getElementById('clear-search'),
    priceRange:     document.getElementById('price-range'),
    priceValue:     document.getElementById('price-value'),
    filterBadge:    document.getElementById('filter-badge'),
    filterApplyBar: document.getElementById('filter-apply-bar'),
    btnApplyFilters:document.getElementById('btn-apply-filters'),
    filterSummary:  document.getElementById('active-filter-summary'),
    themeToggle:    document.getElementById('theme-toggle')
};

/* ═══════════════════════════════════════════════════════
   SISTEMA DE FILTRAGEM (MANTENDO SUA LÓGICA ESTÁTICA)
═══════════════════════════════════════════════════════ */
function filterCards() {
    let visibleCount = 0;
    const textQuery = state.searchText.toLowerCase().trim();

    dom.allCards.forEach(card => {
        // Atributos originais do HTML
        const tags = card.dataset.tags ? card.dataset.tags.split(',') : [];
        const searchContent = (card.dataset.search || '').toLowerCase();
        const rating = parseFloat(card.dataset.rating || "0");
        const price = parseInt(card.dataset.price || "999999");

        // 1. Filtros Rápidos (Busca e Categorias/Pílulas)
        const passesText = textQuery === '' || searchContent.includes(textQuery);
        const passesQuickTags = state.activeTags.size === 0 || 
                                [...state.activeTags].some(t => tags.includes(t));

        // 2. Filtros Avançados (Aba de Filtros)
        const passesType = filterTabState.tipo.size === 0 || 
                           [...filterTabState.tipo].some(t => tags.includes(t));
        
        const passesAmenity = filterTabState.comodidade.size === 0 || 
                              [...filterTabState.comodidade].some(c => tags.includes(c));
        
        const passesRating = !filterTabState.rating || rating >= Number(filterTabState.rating);
        const passesPrice = price <= filterTabState.priceMax;

        // O card só aparece se passar em todos os critérios
        const mostrar = passesText && passesQuickTags && passesType && passesAmenity && passesRating && passesPrice;

        card.classList.toggle("hidden", !mostrar);
        if (mostrar) visibleCount++;
    });

    atualizarInterfaceFiltros(visibleCount);
}

function atualizarInterfaceFiltros(resultados) {
    // Atualiza contador textual
    if (dom.resultsCount) {
        if (resultados === 0) {
            dom.resultsCount.textContent = "Nenhum imóvel encontrado";
        } else if (resultados === 1) {
            dom.resultsCount.textContent = "1 imóvel encontrado";
        } else {
            dom.resultsCount.textContent = `${resultados} imóveis encontrados`;
        }
    }

    // Exibe ou esconde o botão de limpar filtros com base nas tags ativas ou busca
    const temFiltroAtivo = state.activeTags.size > 0 || state.searchText !== '';
    if (dom.clearAllBtn) {
        dom.clearAllBtn.classList.toggle('visible', temFiltroAtivo);
    }

    // Gerencia o container de estado vazio
    if (dom.emptyState) {
        dom.emptyState.classList.toggle("visible", resultados === 0);
    }
}

/* ═══════════════════════════════════════════════════════
   AÇÕES DOS FILTROS RÁPIDOS
═══════════════════════════════════════════════════════ */
function toggleTag(tag, element) {
    if (state.activeTags.has(tag)) {
        state.activeTags.delete(tag);
        element.classList.remove('active');
    } else {
        state.activeTags.add(tag);
        element.classList.add('active');
    }
    filterCards();
}

function clearAllFilters() {
    state.activeTags.clear();
    state.searchText = '';
    
    if (dom.searchInput) dom.searchInput.value = '';
    if (dom.clearSearchBtn) dom.clearSearchBtn.style.display = 'none';

    document.querySelectorAll('.category-item, .tag-pill').forEach(el => {
        el.classList.remove('active');
    });

    filterCards();
}

/* ═══════════════════════════════════════════════════════
   AÇÕES DOS FILTROS AVANÇADOS
═══════════════════════════════════════════════════════ */
function handleFilterBtnClick(btn) {
    const type = btn.dataset.filterType;
    const value = btn.dataset.value;

    if (type === 'rating') {
        const isSame = filterTabState.rating === value;
        document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('active'));
        filterTabState.rating = isSame ? null : value;
        if (!isSame) btn.classList.add('active');
    } else {
        const set = filterTabState[type];
        if (set.has(value)) {
            set.delete(value);
            btn.classList.remove('active');
        } else {
            set.add(value);
            btn.classList.add('active');
        }
    }
    updateFilterUI();
}

function updatePriceLabel(val) {
    filterTabState.priceMax = parseInt(val, 10);
    if (dom.priceValue) {
        dom.priceValue.textContent = `R$ ${filterTabState.priceMax.toLocaleString('pt-BR')}`;
    }
    if (dom.priceRange) {
        const min = parseInt(dom.priceRange.min || "200", 10);
        const max = parseInt(dom.priceRange.max || "5000", 10);
        const pct = ((filterTabState.priceMax - min) / (max - min) * 100).toFixed(1) + '%';
        dom.priceRange.style.setProperty('--pct', pct);
    }
}

function updateFilterUI() {
    const total = filterTabState.tipo.size + filterTabState.comodidade.size + (filterTabState.rating ? 1 : 0);

    if (dom.filterBadge) {
        dom.filterBadge.textContent = total;
        dom.filterBadge.classList.toggle('visible', total > 0);
    }

    if (dom.filterApplyBar) {
        dom.filterApplyBar.classList.toggle('visible', total > 0);
    }

    if (dom.btnApplyFilters) {
        dom.btnApplyFilters.textContent = total === 0 ? 'Mostrar resultados' : `Mostrar resultados (${total})`;
    }

    if (dom.filterSummary) {
        dom.filterSummary.textContent = total === 0 ? 'Nenhum filtro selecionado' : `${total} filtro(s) ativo(s)`;
    }
}

function clearFilterTab() {
    filterTabState.tipo.clear();
    filterTabState.comodidade.clear();
    filterTabState.rating = null;
    filterTabState.priceMax = 2000;

    document.querySelectorAll('.type-btn, .amenity-btn, .rating-btn').forEach(b => {
        b.classList.remove('active');
    });

    if (dom.priceRange) {
        dom.priceRange.value = 2000;
        updatePriceLabel(2000);
    }
    updateFilterUI();
}

function applyFilters() {
    filterCards();

    // Volta para a aba Explorar para exibir os resultados
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active-tab'));
    const explorarTab = document.getElementById('explorar');
    if (explorarTab) explorarTab.classList.add('active-tab');

    document.querySelectorAll('.bottom-nav a').forEach(link => link.classList.remove('active'));
    const explorarLink = document.querySelector('[data-target="explorar"]');
    if (explorarLink) explorarLink.classList.add('active');

    if (dom.filterApplyBar) {
        dom.filterApplyBar.classList.remove('visible');
    }
}

/* ═══════════════════════════════════════════════════════
   INICIALIZAÇÃO DOS EVENTOS (DOM CONTENT LOADED)
═══════════════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", () => {
    
    // Navegação de Abas
    document.querySelectorAll('.bottom-nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.bottom-nav a').forEach(n => n.classList.remove('active'));
            this.classList.add('active');
            
            const targetId = this.getAttribute('data-target');
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active-tab'));
            
            const targetTab = document.getElementById(targetId);
            if (targetTab) targetTab.classList.add('active-tab');
        });
    });

    // Filtros Rápidos (Categorias e Pílulas)
    document.querySelectorAll('.category-item, .tag-pill').forEach(el => {
        el.addEventListener('click', () => {
            const tag = el.getAttribute('data-tag');
            if (tag) toggleTag(tag, el);
        });
    });

    // Caixa de busca textual
    if (dom.searchInput) {
        dom.searchInput.addEventListener('input', function() {
            state.searchText = this.value;
            if (dom.clearSearchBtn) {
                dom.clearSearchBtn.style.display = this.value.length > 0 ? 'block' : 'none';
            }
            filterCards();
        });
    }

    if (dom.clearSearchBtn) {
        dom.clearSearchBtn.addEventListener('click', () => {
            if (dom.searchInput) dom.searchInput.value = '';
            state.searchText = '';
            dom.clearSearchBtn.style.display = 'none';
            filterCards();
        });
    }

    // Botão limpar filtros da aba Explorar
    if (dom.clearAllBtn) {
        dom.clearAllBtn.addEventListener('click', clearAllFilters);
    }

    // Botões de filtros avançados
    document.querySelectorAll('.type-btn, .amenity-btn, .rating-btn').forEach(btn => {
        btn.addEventListener('click', () => handleFilterBtnClick(btn));
    });

    // Slider de Preço
    if (dom.priceRange) {
        dom.priceRange.addEventListener('input', (e) => updatePriceLabel(e.target.value));
    }
    updatePriceLabel(2000);

    // Botão Limpar Tudo da aba de filtros
    const btnClearFilterTab = document.getElementById('btn-clear-filter-tab');
    if (btnClearFilterTab) {
        btnClearFilterTab.addEventListener('click', clearFilterTab);
    }

    // Botão Aplicar Filtros
    if (dom.btnApplyFilters) {
        dom.btnApplyFilters.addEventListener('click', applyFilters);
    }

    // Funcionalidade dos Favoritos (Apenas altera a classe visual do coração nos seus cards estáticos)
    document.querySelectorAll('.fav-btn').forEach(favBtn => {
        favBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isFav = this.classList.contains('fa-solid');
            this.classList.toggle('fa-regular', isFav);
            this.classList.toggle('fa-solid', !isFav);
            this.style.color = isFav ? 'white' : '#ff385c';
        });
    });

    // Modo Escuro
    if (dom.themeToggle) {
        dom.themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            this.innerHTML = document.body.classList.contains('dark-mode')
                ? '<i class="fa-solid fa-sun"></i> Ativar Modo Claro'
                : '<i class="fa-solid fa-moon"></i> Ativar Modo Escuro';
        });
    }

    // Executa uma filtragem inicial para alinhar os cards estáticos com a interface
    filterCards();
});