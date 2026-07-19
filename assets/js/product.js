/**
 * products.js
 * ------------------------------------------------------------------
 * Interactions for the Product Recommendation page (products.php):
 *  1) Live search by product name
 *  2) Category filter tabs (All / Cleanser / Serum / ...)
 *  3) Sort by Highest Match / Name A-Z / Name Z-A
 *  4) Detail modal (populated from window.LT_PRODUCTS_DATA, which is
 *     inlined by products.php — no extra network round-trip needed)
 *  5) Empty state toggle + a lightweight dummy pagination control
 *
 * Depends on nothing outside the DOM; dashboard.js (loaded globally by
 * includes/footer.php) is untouched and unaffected by this file.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    var productsData = window.LT_PRODUCTS_DATA || [];

    var grid        = document.getElementById('productsGrid');
    var emptyState  = document.getElementById('productsEmpty');
    var pagination  = document.getElementById('productsPagination');
    var searchInput = document.getElementById('productsSearch');
    var sortSelect  = document.getElementById('productsSort');
    var tabsWrap    = document.getElementById('productsTabs');

    var activeCategory = 'All';

    function getCards() {
        return grid ? Array.prototype.slice.call(grid.querySelectorAll('.products-card')) : [];
    }

    /* ---- 1) + 2) Combined search + category filter ---- */
    function applySearchAndFilter() {
        var cards = getCards();
        var query = (searchInput && searchInput.value || '').trim().toLowerCase();
        var visibleCount = 0;

        cards.forEach(function (card) {
            var matchesSearch = !query || (card.dataset.name || '').indexOf(query) !== -1;
            var matchesCategory = activeCategory === 'All' || card.dataset.category === activeCategory;
            var isVisible = matchesSearch && matchesCategory;

            card.classList.toggle('is-filtered-out', !isVisible);
            if (isVisible) visibleCount++;
        });

        toggleEmptyState(visibleCount === 0);
    }

    function toggleEmptyState(isEmpty) {
        if (grid) grid.style.display = isEmpty ? 'none' : '';
        if (pagination) pagination.style.display = isEmpty ? 'none' : '';
        if (emptyState) emptyState.classList.toggle('is-hidden', !isEmpty);
    }

    if (searchInput) searchInput.addEventListener('input', applySearchAndFilter);

    if (tabsWrap) {
        tabsWrap.addEventListener('click', function (event) {
            var tab = event.target.closest('.products-tab');
            if (!tab) return;

            Array.prototype.forEach.call(tabsWrap.querySelectorAll('.products-tab'), function (btn) {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            activeCategory = tab.dataset.category;
            applySearchAndFilter();
        });
    }

    /* ---- 3) Sort ---- */
    if (sortSelect && grid) {
        var originalOrder = getCards();

        sortSelect.addEventListener('change', function () {
            var cards = getCards();
            var ordered = cards.slice();

            if (sortSelect.value === 'az') {
                ordered.sort(function (a, b) { return a.dataset.name.localeCompare(b.dataset.name); });
            } else if (sortSelect.value === 'za') {
                ordered.sort(function (a, b) { return b.dataset.name.localeCompare(a.dataset.name); });
            } else {
                // "match" = highest match score first
                ordered.sort(function (a, b) { return Number(b.dataset.match) - Number(a.dataset.match); });
            }

            ordered.forEach(function (card) {
                grid.appendChild(card);
            });
        });
    }

    /* ---- 4) Detail modal ---- */
    var modalOverlay = document.getElementById('productsModalOverlay');
    var modalClose   = document.getElementById('productsModalClose');

    function findRecord(id) {
        for (var i = 0; i < productsData.length; i++) {
            if (String(productsData[i].id) === String(id)) return productsData[i];
        }
        return null;
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function fillChips(containerId, items) {
        var wrap = document.getElementById(containerId);
        if (!wrap) return;
        wrap.innerHTML = '';
        (items || []).forEach(function (label) {
            var span = document.createElement('span');
            span.textContent = label;
            wrap.appendChild(span);
        });
        if (!items || !items.length) {
            var span = document.createElement('span');
            span.textContent = 'Belum tersedia';
            wrap.appendChild(span);
        }
    }

    function openModal(record) {
        if (!modalOverlay || !record) return;

        var thumb = document.getElementById('productsModalThumb');
        if (thumb) {
            var sourceIcon = grid ? grid.querySelector('.products-card[data-id="' + record.id + '"] .product-thumb') : null;
            thumb.innerHTML = sourceIcon ? sourceIcon.innerHTML : '';
        }

        setText('productsModalTag', record.category || '\u2014');
        setText('productsModalTitle', record.name || '\u2014');
        setText('productsModalDesc', record.desc || '\u2014');

        var match = typeof record.match === 'number' ? record.match : 0;
        setText('productsModalMatchValue', match + '%');
        var fill = document.getElementById('productsModalMatchFill');
        if (fill) fill.style.width = match + '%';

        fillChips('productsModalConcerns', record.concerns);
        fillChips('productsModalIngredients', record.ingredients);
        setText('productsModalHowTo', record.how_to_use || 'Belum tersedia');

        modalOverlay.classList.add('is-open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('is-open');
        modalOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (grid) {
        grid.addEventListener('click', function (event) {
            var viewBtn = event.target.closest('.products-view-btn');
            if (!viewBtn) return;
            openModal(findRecord(viewBtn.dataset.id));
        });
    }

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function (event) {
            if (event.target === modalOverlay) closeModal();
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    /* ---- 5) Dummy pagination (visual only — data is not paginated yet) ---- */
    var pageNumbers = document.getElementById('productsPageNumbers');
    var prevBtn = document.getElementById('productsPrev');
    var nextBtn = document.getElementById('productsNext');

    if (pageNumbers && prevBtn && nextBtn) {
        var pageButtons = Array.prototype.slice.call(pageNumbers.querySelectorAll('.products-page-num'));

        function setActivePage(pageNum) {
            pageButtons.forEach(function (btn) {
                btn.classList.toggle('is-active', btn.dataset.page === String(pageNum));
            });
            prevBtn.disabled = pageNum <= 1;
            nextBtn.disabled = pageNum >= pageButtons.length;
        }

        pageButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setActivePage(btn.dataset.page);
            });
        });

        prevBtn.addEventListener('click', function () {
            var current = pageButtons.findIndex(function (btn) { return btn.classList.contains('is-active'); });
            if (current > 0) setActivePage(pageButtons[current - 1].dataset.page);
        });

        nextBtn.addEventListener('click', function () {
            var current = pageButtons.findIndex(function (btn) { return btn.classList.contains('is-active'); });
            if (current < pageButtons.length - 1) setActivePage(pageButtons[current + 1].dataset.page);
        });

        setActivePage(1);
    }

    /* ---- Initial state ---- */
    applySearchAndFilter();
})();