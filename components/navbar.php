<!-- ============================================
     NAVBAR
     Sticky navigation, blurs on scroll (see script.js)
============================================ -->
<header class="navbar" id="navbar">
    <div class="container navbar__inner">

        <!-- Logo -->
        <a href="#home" class="navbar__logo">
            <svg class="navbar__logo-icon" width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.4"/>
                <line x1="20.6" y1="20.6" x2="27" y2="27" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                <path d="M24 4.5L24.8 6.7L27 7.5L24.8 8.3L24 10.5L23.2 8.3L21 7.5L23.2 6.7L24 4.5Z" fill="currentColor"/>
            </svg>
            <span class="navbar__logo-text">LumiTone</span>
        </a>

        <!-- Nav Links -->
        <nav class="navbar__nav" id="navbarNav">
            <a href="#home" class="navbar__link">Home</a>
            <a href="#features" class="navbar__link">Features</a>
            <a href="#how-it-works" class="navbar__link">How It Works</a>
            <a href="#testimonials" class="navbar__link">Testimonials</a>
            <a href="#faq" class="navbar__link">FAQ</a>
        </nav>

        <!-- Auth Buttons -->
        <div class="navbar__actions">
            <button type="button" class="btn btn--text js-auth-trigger" data-modal="login">Masuk</button>
            <button type="button" class="btn btn--pill js-auth-trigger" data-modal="register">Daftar</button>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="navbar__burger" id="navbarBurger" aria-label="Toggle menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- Mobile Nav Panel -->
    <div class="navbar__mobile" id="navbarMobile">
        <a href="#home" class="navbar__link">Home</a>
        <a href="#features" class="navbar__link">Features</a>
        <a href="#how-it-works" class="navbar__link">How It Works</a>
        <a href="#testimonials" class="navbar__link">Testimonials</a>
        <a href="#faq" class="navbar__link">FAQ</a>
        <div class="navbar__mobile-actions">
            <button type="button" class="btn btn--text js-auth-trigger" data-modal="login">Masuk</button>
            <button type="button" class="btn btn--pill js-auth-trigger" data-modal="register">Daftar</button>
        </div>
    </div>
</header>