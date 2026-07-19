/**
 * history.js
 * ------------------------------------------------------------------
 * Interactions for the Analysis History page (history.php):
 *  1) Live search across skin tone / undertone / skin type / concern
 *  2) Filter by month (This Month / Last Month / All)
 *  3) Sort by date (Newest / Oldest)
 *  4) Detail modal (populated from window.LT_HISTORY_DATA, which is
 *     inlined by history.php — no extra network round-trip needed)
 *  5) Delete row (dummy front-end removal only, no backend call yet)
 *  6) Empty state toggle + a lightweight dummy pagination control
 *
 * Depends on nothing outside the DOM; dashboard.js (loaded globally by
 * includes/footer.php) is untouched and unaffected by this file.
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    var historyData = window.LT_HISTORY_DATA || [];

    var tableBody   = document.getElementById('historyTableBody');
    var tableWrap   = document.getElementById('historyTableWrap');
    var emptyState  = document.getElementById('historyEmpty');
    var pagination  = document.getElementById('historyPagination');
    var searchInput = document.getElementById('historySearch');
    var filterSelect = document.getElementById('historyFilter');
    var sortSelect   = document.getElementById('historySort');

    function getRows() {
        return tableBody ? Array.prototype.slice.call(tableBody.querySelectorAll('.history-row')) : [];
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
        var rows = getRows();
        var query = (searchInput && searchInput.value || '').trim().toLowerCase();
        var filter = filterSelect ? filterSelect.value : 'all';
        var visibleCount = 0;

        rows.forEach(function (row) {
            var matchesSearch = !query || (row.dataset.search || '').indexOf(query) !== -1;

            var matchesFilter = true;
            if (filter === 'this-month') {
                matchesFilter = row.dataset.month === thisMonthKey;
            } else if (filter === 'last-month') {
                matchesFilter = row.dataset.month === lastMonthKey;
            }

            var isVisible = matchesSearch && matchesFilter;
            row.classList.toggle('is-filtered-out', !isVisible);
            if (isVisible) visibleCount++;
        });

        toggleEmptyState(visibleCount === 0);
    }

    function toggleEmptyState(isEmpty) {
        if (tableWrap) tableWrap.style.display = isEmpty ? 'none' : '';
        if (pagination) pagination.style.display = isEmpty ? 'none' : '';
        if (emptyState) emptyState.classList.toggle('is-hidden', !isEmpty);
    }

    if (searchInput) searchInput.addEventListener('input', applySearchAndFilter);
    if (filterSelect) filterSelect.addEventListener('change', applySearchAndFilter);

    /* ---- 3) Sort (Newest / Oldest) — reorders rows by their original DOM order ---- */
    if (sortSelect && tableBody) {
        var originalOrder = getRows();

        sortSelect.addEventListener('change', function () {
            var rows = getRows();
            var ordered = rows.slice();

            if (sortSelect.value === 'oldest') {
                ordered.reverse();
            } else {
                // "newest" = restore the original (already newest-first) order
                ordered = originalOrder.filter(function (row) {
                    return tableBody.contains(row);
                });
            }

            ordered.forEach(function (row) {
                tableBody.appendChild(row);
            });
        });
    }

    /* ---- 4) Detail modal ---- */
    var modalOverlay = document.getElementById('historyModalOverlay');
    var modalClose   = document.getElementById('historyModalClose');

    function findRecord(id) {
        for (var i = 0; i < historyData.length; i++) {
            if (String(historyData[i].id) === String(id)) return historyData[i];
        }
        return null;
    }

    function openModal(record) {
        if (!modalOverlay || !record) return;

        setText('historyModalAvatar', record.initials || '?');
        setText('historyModalDate', record.date || '\u2014');
        setText('historyModalSkintone', record.skintone || '\u2014');
        setText('historyModalUndertone', record.undertone || '\u2014');
        setText('historyModalSkintype', record.skintype || '\u2014');
        setText('historyModalConcern', record.concern || '\u2014');

        var colorsWrap = document.getElementById('historyModalColors');
        if (colorsWrap) {
            colorsWrap.innerHTML = '';
            (record.colors || []).forEach(function (hex) {
                var swatch = document.createElement('span');
                swatch.className = 'history-modal-swatch';
                swatch.style.background = hex;
                swatch.title = hex;
                colorsWrap.appendChild(swatch);
            });
            if (!record.colors || !record.colors.length) {
                colorsWrap.innerHTML = '<span class="history-modal-value">Belum tersedia</span>';
            }
        }

        var productsWrap = document.getElementById('historyModalProducts');
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
        setText('historyModalConfidenceValue', confidence ? confidence + '%' : 'Sedang diproses');
        var fill = document.getElementById('historyModalConfidenceFill');
        if (fill) fill.style.width = confidence + '%';

        modalOverlay.classList.add('is-open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function closeModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('is-open');
        modalOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (tableBody) {
        tableBody.addEventListener('click', function (event) {
            var viewBtn = event.target.closest('.history-view-btn');
            if (viewBtn) {
                var record = findRecord(viewBtn.dataset.id);
                openModal(record);
                return;
            }

            var deleteBtn = event.target.closest('.history-delete-btn');
            if (deleteBtn) {
                handleDelete(deleteBtn);
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

    /* ---- 5) Delete row (front-end only for now — no DELETE request yet) ---- */
    function handleDelete(button) {
        var row = button.closest('.history-row');
        if (!row) return;

        var confirmed = window.confirm('Hapus riwayat analisis ini?');
        if (!confirmed) return;

        row.classList.add('is-removing');
        window.setTimeout(function () {
            row.remove();
            applySearchAndFilter();
        }, 200);
    }

    /* ---- 6) Dummy pagination (visual only — data is not paginated yet) ---- */
    var pageNumbers = document.getElementById('historyPageNumbers');
    var prevBtn = document.getElementById('historyPrev');
    var nextBtn = document.getElementById('historyNext');

    if (pageNumbers && prevBtn && nextBtn) {
        var pageButtons = Array.prototype.slice.call(pageNumbers.querySelectorAll('.history-page-num'));

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
