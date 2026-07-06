/**
 * dashboard.js
 * ------------------------------------------------------------------
 * Small, dependency-free interactions for the LumiTone dashboard:
 *  1) Mobile sidebar open/close
 *  2) Profile dropdown toggle
 *  3) Scroll-reveal animation for .reveal elements
 *  4) Floating/condensed topbar on scroll (search-only pill)
 *  5) Resizable / collapsible sidebar (drag handle + toggle button)
 * ------------------------------------------------------------------
 */
(function () {
    'use strict';

    /* ---- 1) Mobile sidebar toggle ---- */
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function openSidebar() {
        sidebar.classList.add('is-open');
        sidebarBackdrop.classList.add('is-visible');
    }

    function closeSidebar() {
        sidebar.classList.remove('is-open');
        sidebarBackdrop.classList.remove('is-visible');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (sidebar.classList.contains('is-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    /* ---- 2) Profile dropdown ---- */
    var profileBtn = document.getElementById('profileMenuBtn');
    var profileDropdown = document.getElementById('profileDropdown');

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            var isOpen = profileDropdown.classList.toggle('is-open');
            profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!profileDropdown.contains(event.target) && !profileBtn.contains(event.target)) {
                profileDropdown.classList.remove('is-open');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---- 3) Scroll-reveal animation ---- */
    var revealEls = document.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window && revealEls.length) {
        var observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );

        revealEls.forEach(function (el) {
            observer.observe(el);
        });
    } else {
        // Fallback: no IntersectionObserver support, just show everything.
        revealEls.forEach(function (el) {
            el.classList.add('is-visible');
        });
    }

    /* ---- 4) Topbar: float + shrink to search-only on scroll ---- */
    var topbar = document.querySelector('.topbar');

    if (topbar) {
        var CONDENSE_AT = 24; // px scrolled before it condenses
        var ticking = false;

        function updateTopbarState() {
            var scrolled = window.scrollY > CONDENSE_AT;
            topbar.classList.toggle('is-condensed', scrolled);
            ticking = false;
        }

        function onScroll() {
            if (!ticking) {
                window.requestAnimationFrame(updateTopbarState);
                ticking = true;
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        updateTopbarState(); // set correct state on load (e.g. after a refresh mid-scroll)
    }

    /* ---- 5) Resizable / collapsible sidebar ---- */
    var root = document.documentElement;
    var resizer = document.getElementById('sidebarResizer');
    var collapseBtn = document.getElementById('sidebarCollapseToggle');

    if (sidebar && resizer) {
        var STORAGE_WIDTH = 'lt_sidebar_width';
        var STORAGE_COLLAPSED = 'lt_sidebar_collapsed';
        var DEFAULT_WIDTH = 264;
        var MIN_WIDTH = 200;
        var MAX_WIDTH = 380;
        var COLLAPSE_THRESHOLD = 168; // drag below this snaps to icon-only
        var COLLAPSED_WIDTH = 76;

        var lastExpandedWidth = DEFAULT_WIDTH;

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function setWidth(px) {
            root.style.setProperty('--sidebar-width', px + 'px');
        }

        function setCollapsed(collapsed) {
            sidebar.classList.toggle('is-collapsed', collapsed);
            if (collapseBtn) {
                collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                collapseBtn.title = collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar';
                collapseBtn.setAttribute('aria-label', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
            }
            setWidth(collapsed ? COLLAPSED_WIDTH : lastExpandedWidth);
            try {
                localStorage.setItem(STORAGE_COLLAPSED, collapsed ? '1' : '0');
                localStorage.setItem(STORAGE_WIDTH, String(lastExpandedWidth));
            } catch (e) { /* storage unavailable, ignore */ }
        }

        // Restore persisted state on load
        (function restore() {
            var savedWidth = null;
            var savedCollapsed = false;
            try {
                var w = localStorage.getItem(STORAGE_WIDTH);
                if (w) savedWidth = clamp(parseInt(w, 10), MIN_WIDTH, MAX_WIDTH);
                savedCollapsed = localStorage.getItem(STORAGE_COLLAPSED) === '1';
            } catch (e) { /* ignore */ }

            lastExpandedWidth = savedWidth || DEFAULT_WIDTH;
            setCollapsed(savedCollapsed);
        })();

        // Drag to resize
        var dragging = false;
        var startX = 0;
        var startWidth = 0;

        function onPointerMove(e) {
            if (!dragging) return;
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var delta = clientX - startX;
            var newWidth = startWidth + delta;

            if (newWidth < COLLAPSE_THRESHOLD) {
                sidebar.classList.add('is-collapsed');
                setWidth(COLLAPSED_WIDTH);
            } else {
                sidebar.classList.remove('is-collapsed');
                var clamped = clamp(newWidth, MIN_WIDTH, MAX_WIDTH);
                setWidth(clamped);
                lastExpandedWidth = clamped;
            }
        }

        function onPointerUp() {
            if (!dragging) return;
            dragging = false;
            sidebar.classList.remove('is-resizing');
            document.body.style.userSelect = '';
            var isCollapsed = sidebar.classList.contains('is-collapsed');
            if (collapseBtn) {
                collapseBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            }
            try {
                localStorage.setItem(STORAGE_COLLAPSED, isCollapsed ? '1' : '0');
                localStorage.setItem(STORAGE_WIDTH, String(lastExpandedWidth));
            } catch (e) { /* ignore */ }
            document.removeEventListener('mousemove', onPointerMove);
            document.removeEventListener('mouseup', onPointerUp);
            document.removeEventListener('touchmove', onPointerMove);
            document.removeEventListener('touchend', onPointerUp);
        }

        function onPointerDown(e) {
            // Only left click / single touch, and only above the mobile drawer breakpoint
            if (window.innerWidth <= 1024) return;
            if (e.type === 'mousedown' && e.button !== 0) return;

            dragging = true;
            startX = e.touches ? e.touches[0].clientX : e.clientX;
            startWidth = sidebar.classList.contains('is-collapsed') ? COLLAPSED_WIDTH : sidebar.getBoundingClientRect().width;
            sidebar.classList.add('is-resizing');
            document.body.style.userSelect = 'none';

            document.addEventListener('mousemove', onPointerMove);
            document.addEventListener('mouseup', onPointerUp);
            document.addEventListener('touchmove', onPointerMove, { passive: false });
            document.addEventListener('touchend', onPointerUp);
            e.preventDefault();
        }

        resizer.addEventListener('mousedown', onPointerDown);
        resizer.addEventListener('touchstart', onPointerDown, { passive: false });

        // Double-click the handle: quick toggle collapse/expand
        resizer.addEventListener('dblclick', function () {
            setCollapsed(!sidebar.classList.contains('is-collapsed'));
        });

        // Keyboard support on the handle (arrow keys resize, Enter toggles)
        resizer.addEventListener('keydown', function (e) {
            var isCollapsed = sidebar.classList.contains('is-collapsed');
            if (e.key === 'ArrowLeft') {
                var narrower = clamp((isCollapsed ? COLLAPSED_WIDTH : lastExpandedWidth) - 16, MIN_WIDTH, MAX_WIDTH);
                if ((isCollapsed ? COLLAPSED_WIDTH : lastExpandedWidth) - 16 < COLLAPSE_THRESHOLD) {
                    setCollapsed(true);
                } else {
                    sidebar.classList.remove('is-collapsed');
                    lastExpandedWidth = narrower;
                    setWidth(narrower);
                }
                e.preventDefault();
            } else if (e.key === 'ArrowRight') {
                if (isCollapsed) {
                    setCollapsed(false);
                } else {
                    var wider = clamp(lastExpandedWidth + 16, MIN_WIDTH, MAX_WIDTH);
                    lastExpandedWidth = wider;
                    setWidth(wider);
                }
                e.preventDefault();
            } else if (e.key === 'Enter' || e.key === ' ') {
                setCollapsed(!isCollapsed);
                e.preventDefault();
            }
        });

        // Explicit toggle button
        if (collapseBtn) {
            collapseBtn.addEventListener('click', function () {
                setCollapsed(!sidebar.classList.contains('is-collapsed'));
            });
        }
    }
})();