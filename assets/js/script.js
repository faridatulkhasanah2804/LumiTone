/* =========================================================
   LumiTone — script.js
   Table of Contents:
   1. Navbar scroll effect
   2. Mobile menu toggle
   3. Smooth scroll (native + offset correction)
   4. Scroll-reveal (fade-up) via IntersectionObserver
   5. FAQ accordion
   6. Statistics count-up
   7. Button ripple effect
========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    /* -----------------------------------------------------
       1. Navbar scroll effect
       Adds a blurred background once the page scrolls past
       a small threshold, removes it near the top.
    ----------------------------------------------------- */
    const navbar = document.getElementById('navbar');
    const SCROLL_THRESHOLD = 24;

    function handleNavbarScroll() {
        if (window.scrollY > SCROLL_THRESHOLD) {
            navbar.classList.add('is-scrolled');
        } else {
            navbar.classList.remove('is-scrolled');
        }
    }
    handleNavbarScroll();
    window.addEventListener('scroll', handleNavbarScroll, { passive: true });


    /* -----------------------------------------------------
       2. Mobile menu toggle
    ----------------------------------------------------- */
    const burger = document.getElementById('navbarBurger');
    const mobileNav = document.getElementById('navbarMobile');

    if (burger && mobileNav) {
        burger.addEventListener('click', function () {
            const isOpen = mobileNav.classList.toggle('is-open');
            burger.classList.toggle('is-active', isOpen);
            burger.setAttribute('aria-expanded', isOpen);
        });

        // Close mobile menu when a link is clicked
        mobileNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileNav.classList.remove('is-open');
                burger.classList.remove('is-active');
                burger.setAttribute('aria-expanded', 'false');
            });
        });
    }


    /* -----------------------------------------------------
       3. Smooth scroll for in-page anchor links
       (CSS scroll-behavior handles most of it; this adds
       a safety net for older browsers)
    ----------------------------------------------------- */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId.length > 1) {
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    const navHeight = navbar ? navbar.offsetHeight : 0;
                    const top = target.getBoundingClientRect().top + window.scrollY - navHeight + 1;
                    window.scrollTo({ top: top, behavior: 'smooth' });
                }
            }
        });
    });


    /* -----------------------------------------------------
       4. Scroll-reveal animation (fade-up)
       Elements with the .fade-up class animate into view
       once they enter the viewport.
    ----------------------------------------------------- */
    const fadeEls = document.querySelectorAll('.fade-up');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        fadeEls.forEach(function (el) { revealObserver.observe(el); });
    } else {
        // Fallback: reveal everything immediately
        fadeEls.forEach(function (el) { el.classList.add('is-visible'); });
    }


    /* -----------------------------------------------------
       5. FAQ accordion
       Only one answer open at a time, smooth height transition.
    ----------------------------------------------------- */
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(function (item) {
        const question = item.querySelector('.faq-item__question');
        const answer = item.querySelector('.faq-item__answer');

        question.addEventListener('click', function () {
            const isOpen = item.classList.contains('is-open');

            // Close all other items
            faqItems.forEach(function (otherItem) {
                if (otherItem !== item) {
                    otherItem.classList.remove('is-open');
                    otherItem.querySelector('.faq-item__question').setAttribute('aria-expanded', 'false');
                    otherItem.querySelector('.faq-item__answer').style.maxHeight = null;
                }
            });

            // Toggle current item
            if (isOpen) {
                item.classList.remove('is-open');
                question.setAttribute('aria-expanded', 'false');
                answer.style.maxHeight = null;
            } else {
                item.classList.add('is-open');
                question.setAttribute('aria-expanded', 'true');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });


    /* -----------------------------------------------------
       6. Statistics count-up
       Animates numbers from 0 to their target value once
       the statistics section scrolls into view.
    ----------------------------------------------------- */
    const statNumbers = document.querySelectorAll('.stat-card__number');

    function animateCount(el) {
        const target = parseInt(el.getAttribute('data-count'), 10);
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1400;
        const startTime = performance.now();

        function tick(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            // Ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(eased * target);
            el.textContent = current + suffix;

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = target + suffix;
            }
        }
        requestAnimationFrame(tick);
    }

    if (statNumbers.length && 'IntersectionObserver' in window) {
        const statObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCount(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(function (el) { statObserver.observe(el); });
    }


    /* -----------------------------------------------------
       7. Button ripple effect
       Adds a Material-style ripple on click for elements
       carrying the .ripple class.
    ----------------------------------------------------- */
    document.querySelectorAll('.ripple').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const rect = btn.getBoundingClientRect();
            const circle = document.createElement('span');
            const size = Math.max(rect.width, rect.height);

            circle.className = 'ripple-circle';
            circle.style.width = circle.style.height = size + 'px';
            circle.style.left = (e.clientX - rect.left - size / 2) + 'px';
            circle.style.top = (e.clientY - rect.top - size / 2) + 'px';

            btn.appendChild(circle);
            setTimeout(function () { circle.remove(); }, 650);
        });
    });


    /* -----------------------------------------------------
       8. Auth modal (Masuk / Daftar)
       Split-panel layout: opens centered with a fade + scale,
       and the colored pane physically slides across when
       switching between "Masuk" and "Daftar" (data-mode).
    ----------------------------------------------------- */
    const modalOverlay = document.getElementById('modalOverlay');
    const modalClose = document.getElementById('modalClose');
    const authShell = document.getElementById('authShell');
    const authTriggers = document.querySelectorAll('.js-auth-trigger');
    const switchButtons = document.querySelectorAll('[data-switch]');

    function setAuthMode(mode) {
        if (authShell) authShell.setAttribute('data-mode', mode || 'login');
    }

    function openModal(mode) {
        setAuthMode(mode);
        modalOverlay.classList.add('is-open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modalOverlay.classList.remove('is-open');
        modalOverlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    if (modalOverlay) {
        authTriggers.forEach(function (btn) {
            btn.addEventListener('click', function () {
                openModal(btn.getAttribute('data-modal'));
            });
        });

        switchButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setAuthMode(btn.getAttribute('data-switch'));
            });
        });

        modalClose.addEventListener('click', closeModal);

        // Click on the dimmed backdrop (not the box itself) closes the modal
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) closeModal();
        });

        // Esc key closes the modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modalOverlay.classList.contains('is-open')) {
                closeModal();
            }
        });
    }

});