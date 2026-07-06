<!-- ============================================
     HERO SECTION
     Centered layout with headline, AI badge, CTA,
     and ambient background decorations (glows + sparkles)
============================================ -->
<section class="hero" id="home">

    <!-- Background decorations -->
    <div class="hero__bg" aria-hidden="true">
        <div class="hero__glow hero__glow--1"></div>
        <div class="hero__glow hero__glow--2"></div>
        <div class="hero__glow hero__glow--3"></div>

        <span class="sparkle sparkle--1">✦</span>
        <span class="sparkle sparkle--2">✦</span>
        <span class="sparkle sparkle--3">✦</span>
        <span class="sparkle sparkle--4">✦</span>
        <span class="sparkle sparkle--5">✦</span>
    </div>

    <div class="container hero__inner">

        <!-- Headline -->
        <h1 class="hero__headline fade-up">
            Kenali Skintone-mu,<br>
            Rawat dengan <span class="hero__highlight">Tepat</span>
        </h1>

        <!-- AI Analysis Badge -->
        <div class="hero__badge fade-up" style="--delay: 0.1s">
            <span class="hero__badge-sparkle">✧</span>
            <span class="hero__badge-text">AI Analysis</span>
            <span class="hero__badge-sparkle">✧</span>
        </div>

        <!-- Description -->
        <p class="hero__desc fade-up" style="--delay: 0.2s">
            Upload foto kulitmu dan biarkan AI LumiTone membantu menganalisis
            skintone, undertone, dan kondisi kulitmu.
        </p>

        <!-- Primary CTA -->
        <a href="#how-it-works" class="btn btn--primary btn--lg ripple fade-up" style="--delay: 0.3s">
            <span class="btn__sparkle">✨</span>
            Mulai Analisis Sekarang
        </a>

        <!-- Trust Note -->
        <div class="hero__trust fade-up" style="--delay: 0.4s">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 1.5L13 3.5V7.5C13 10.8 10.9 13.6 8 14.5C5.1 13.6 3 10.8 3 7.5V3.5L8 1.5Z" fill="var(--color-primary)"/>
                <path d="M5.8 8L7.3 9.5L10.3 6.2" stroke="var(--color-bg)" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Aman &amp; Privasi Terjaga</span>
        </div>
    </div>
</section>
