<!-- ============================================
     AUTH MODAL
     Split-panel layout: a colored brand pane + a white form pane.
     Switching between "Masuk" and "Daftar" slides the colored
     pane across to the other side (see script.js + style.css).
============================================ -->
<div class="modal-overlay" id="modalOverlay" aria-hidden="true">
    <div class="modal-box modal-box--split" id="modalBox" role="dialog" aria-modal="true" aria-labelledby="authFormTitle">

        <button type="button" class="modal-close" id="modalClose" aria-label="Tutup">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M4 4l10 10M14 4L4 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>

        <!-- data-mode controls which side the color pane sits on: "login" or "register" -->
        <div class="auth-shell" id="authShell" data-mode="login">

            <!-- ===== Color Pane (slides) ===== -->
            <div class="auth-colorpane">
                <div class="auth-colorpane__inner">
                    <p class="auth-colorpane__brand">LumiTone <span></span></p>

                    <div class="auth-colorpane__content" data-for="login">
                        <p class="auth-colorpane__text">Belum punya akun?</p>
                        <button type="button" class="auth-switch-btn" data-switch="register">Daftar</button>
                    </div>

                    <div class="auth-colorpane__content" data-for="register">
                        <p class="auth-colorpane__text">Sudah punya akun?</p>
                        <button type="button" class="auth-switch-btn" data-switch="login">Masuk</button>
                    </div>
                </div>
            </div>

            <!-- ===== Form Pane (slides) ===== -->
            <div class="auth-formpane">
                <div class="auth-formpane__inner">

                    <!-- Login Form -->
                    <div class="auth-form" data-for="login">
                        <span class="modal-badge"></span>
                        <h2 class="auth-form__title" id="authFormTitle">Selamat Datang Kembali</h2>

                        <form class="modal-form" onsubmit="return false;">
                            <label class="modal-field">
                                <input type="email" placeholder="Email" required>
                            </label>
                            <label class="modal-field">
                                <input type="password" placeholder="Kata Sandi" required>
                            </label>
                            <a href="#" class="modal-forgot">Lupa kata sandi?</a>
                            <button type="submit" class="btn btn--primary btn--block ripple">Masuk</button>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div class="auth-form" data-for="register">
                        <span class="modal-badge"></span>
                        <h2 class="auth-form__title">Buat Akun Baru</h2>

                        <form class="modal-form" onsubmit="return false;">
                            <label class="modal-field">
                                <input type="text" placeholder="Nama Lengkap" required>
                            </label>
                            <label class="modal-field">
                                <input type="email" placeholder="Email" required>
                            </label>
                            <label class="modal-field">
                                <input type="password" placeholder="Kata Sandi" required>
                            </label>
                            <label class="modal-field">
                                <input type="password" placeholder="Konfirmasi Password" required>
                            </label>
                            <button type="submit" class="btn btn--primary btn--block ripple">Daftar Gratis</button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>