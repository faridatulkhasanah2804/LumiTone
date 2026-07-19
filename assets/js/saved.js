/**
 * saved.js
 * ------------------------------------------------------------------
 * Interactions for the Saved Results page (saved.php):
 *  1) Live search across skin tone / undertone / skin type / concern
 *  2) Filter by month (This Month / Last Month / All)
 *  3) Sort by date (Newest / Oldest)
 *  4) Detail modal (populated from window.LT_SAVED_DATA, which is
 *     inlined by saved.php — no extra network round-trip needed)
 *  5) Unsave card (dummy front-end removal only, no backend call yet)
 *  6) Empty state toggle + a lightweight dummy pagination control
 *
 * Depends on nothing outside the DOM; dashboard.js (loaded globally by
 * includes/footer.php) is untouched and unaffected by this file.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    var savedData = window.LT_SAVED_DATA || [];

    var grid        = document.getElementById('savedGrid');
    var emptyState  = document.getElementById('savedEmpty');
    var pagination  = document.getElementById('savedPagination');
    var searchInput = document.getElementById('savedSearch');
    var filterSelect = document.getElementById('savedFilter');
    var sortSelect   = document.getElementById('savedSort');

    function getCards() {
        return grid ? Array.prototype.slice.call(grid.querySelectorAll('.saved-card')) : [];
    }

    /* ---- Current month helpers, for the "This Month" / "Last Month" filter ---- */
    function monthKey(date) {
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    }
    var now = new Date();
    var thisMonthKey = monthKey(now);
    var lastMonthDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    var lastMonthKey = monthKey(lastMonthDate);

    /* ---- 1) + 2) Combined search + filter ---- */
    function applySearchAndFilter() {
        var cards = getCards();
        var query = (searchInput && searchInput.value || '').trim().toLowerCase();
        var filter = filterSelect ? filterSelect.value : 'all';
        var visibleCount = 0;

        cards.forEach(function (card) {
            var matchesSearch = !query || (card.dataset.search || '').indexOf(query) !== -1;

            var matchesFilter = true;
            if (filter === 'this-month') {
                matchesFilter = card.dataset.month === thisMonthKey;
            } else if (filter === 'last-month') {
                matchesFilter = card.dataset.month === lastMonthKey;
            }

            var isVisible = matchesSearch && matchesFilter;
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
    if (filterSelect) filterSelect.addEventListener('change', applySearchAndFilter);

    /* ---- 3) Sort (Newest / Oldest) — reorders cards by their original DOM order ---- */
    if (sortSelect && grid) {
        var originalOrder = getCards();

        sortSelect.addEventListener('change', function () {
            var cards = getCards();
            var ordered = cards.slice();

            if (sortSelect.value === 'oldest') {
                ordered.reverse();
            } else {
                // "newest" = restore the original (already newest-first) order
                ordered = originalOrder.filter(function (card) {
                    return grid.contains(card);
                });
            }

            ordered.forEach(function (card) {
                grid.appendChild(card);
            });
        });
    }

    /* ---- 4) Detail modal ---- */
    var modalOverlay = document.getElementById('savedModalOverlay');
    var modalClose   = document.getElementById('savedModalClose');

    function findRecord(id) {
        for (var i = 0; i < savedData.length; i++) {
            if (String(savedData[i].id) === String(id)) return savedData[i];
        }
        return null;
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function openModal(record) {
        if (!modalOverlay || !record) return;

        setText('savedModalAvatar', record.initials || '?');
        setText('savedModalDate', record.date || '\u2014');
        setText('savedModalSkintone', record.skintone || '\u2014');
        setText('savedModalUndertone', record.undertone || '\u2014');
        setText('savedModalSkintype', record.skintype || '\u2014');
        setText('savedModalConcern', record.concern || '\u2014');

        var colorsWrap = document.getElementById('savedModalColors');
        if (colorsWrap) {
            colorsWrap.innerHTML = '';
            (record.colors || []).forEach(function (hex) {
                var swatch = document.createElement('span');
                swatch.className = 'saved-modal-swatch';
                swatch.style.background = hex;
                swatch.title = hex;
                colorsWrap.appendChild(swatch);
            });
            if (!record.colors || !record.colors.length) {
                colorsWrap.innerHTML = '<span class="saved-modal-value">Belum tersedia</span>';
            }
        }

        var productsWrap = document.getElementById('savedModalProducts');
        if (productsWrap) {
            productsWrap.innerHTML = '';
            (record.products || []).forEach(function (name) {
                var li = document.createElement('li');
                li.textContent = name;
                productsWrap.appendChild(li);
            });
            if (!record.products || !record.products.length) {
                var li = document.createElement('li');
                li.textContent = 'Belum tersedia';
                productsWrap.appendChild(li);
            }
        }

        var confidence = typeof record.confidence === 'number' ? record.confidence : 0;
        setText('savedModalConfidenceValue', confidence ? confidence + '%' : '\u2014');
        var fill = document.getElementById('savedModalConfidenceFill');
        if (fill) fill.style.width = confidence + '%';

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
            var viewBtn = event.target.closest('.saved-view-btn');
            if (viewBtn) {
                openModal(findRecord(viewBtn.dataset.id));
                return;
            }

            var unsaveBtn = event.target.closest('.saved-unsave-btn');
            if (unsaveBtn) {
                handleUnsave(unsaveBtn);
            }
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

    /* ---- 5) Unsave card (front-end only for now — no backend call yet) ---- */
    function handleUnsave(button) {
        var card = button.closest('.saved-card');
        if (!card) return;

        var confirmed = window.confirm('Hapus hasil ini dari Saved Results?');
        if (!confirmed) return;

        card.classList.add('is-removing');
        window.setTimeout(function () {
            card.remove();
            applySearchAndFilter();
        }, 200);
    }

    /* ---- 6) Dummy pagination (visual only — data is not paginated yet) ---- */
    var pageNumbers = document.getElementById('savedPageNumbers');
    var prevBtn = document.getElementById('savedPrev');
    var nextBtn = document.getElementById('savedNext');

    if (pageNumbers && prevBtn && nextBtn) {
        var pageButtons = Array.prototype.slice.call(pageNumbers.querySelectorAll('.saved-page-num'));

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