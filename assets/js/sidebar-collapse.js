/**
 * sidebar-collapse.js
 * Toggle sidebar antara mode lebar penuh <-> icon-only.
 * State disimpan di localStorage supaya tetap kepilih walau halaman di-refresh.
 */
document.addEventListener('DOMContentLoaded', function () {
    const sidebar   = document.getElementById('sidebar');
    const appShell  = document.querySelector('.app-shell');
    const toggleBtn = document.getElementById('sidebarCollapseBtn');

    if (!sidebar || !appShell || !toggleBtn) return;

    // Terapkan state tersimpan saat halaman dimuat
    const isCollapsed = localStorage.getItem('lt-sidebar-collapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('is-collapsed');
        appShell.classList.add('sidebar-is-collapsed');
    }

    toggleBtn.addEventListener('click', function () {
        const collapsed = sidebar.classList.toggle('is-collapsed');
        appShell.classList.toggle('sidebar-is-collapsed', collapsed);
        localStorage.setItem('lt-sidebar-collapsed', collapsed);
    });
});