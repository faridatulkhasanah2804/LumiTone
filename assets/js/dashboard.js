/* ==========================================================================
   LumiTone — Dashboard JS
   Handles sidebar collapse/expand, mobile sidebar toggle, and small
   interactive UI behaviors for the dashboard shell.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

  const sidebar        = document.querySelector('.sidebar');
  const collapseBtn    = document.querySelector('.sidebar__toggle');
  const mobileMenuBtn  = document.querySelector('.topbar__menu-btn');
  const overlay        = document.querySelector('.sidebar-overlay');

  /* ------------------------------------------------------------------
     1. Desktop sidebar collapse (persists choice for the session)
     ------------------------------------------------------------------ */
  const COLLAPSE_KEY = 'lumitone_sidebar_collapsed';

  function applyStoredState() {
    const stored = sessionStorage.getItem(COLLAPSE_KEY);
    if (stored === 'true') {
      sidebar.classList.add('collapsed');
    }
  }

  if (collapseBtn) {
    collapseBtn.addEventListener('click', function () {
      sidebar.classList.toggle('collapsed');
      sessionStorage.setItem(COLLAPSE_KEY, sidebar.classList.contains('collapsed'));
    });
  }

  applyStoredState();

  /* ------------------------------------------------------------------
     2. Mobile sidebar (slide-in with overlay)
     ------------------------------------------------------------------ */
  function openMobileSidebar() {
    sidebar.classList.add('mobile-open');
    overlay.classList.add('active');
  }

  function closeMobileSidebar() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
  }

  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', openMobileSidebar);
  }

  if (overlay) {
    overlay.addEventListener('click', closeMobileSidebar);
  }

  /* Close mobile sidebar automatically on wider viewports */
  window.addEventListener('resize', function () {
    if (window.innerWidth > 1024) {
      closeMobileSidebar();
    }
  });

  /* ------------------------------------------------------------------
     3. Notification / user dropdown placeholders
     ------------------------------------------------------------------ */
  const notifBtn = document.querySelector('[data-notif-btn]');
  if (notifBtn) {
    notifBtn.addEventListener('click', function () {
      // Dropdown panel will be wired up when notifications are implemented
      console.log('Notifications clicked — panel coming soon.');
    });
  }

});