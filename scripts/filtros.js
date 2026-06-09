document.addEventListener('DOMContentLoaded', function() {
    const filterBtn = document.getElementById('openFiltersBtn');
    const closeBtn = document.getElementById('closeFiltersBtn');
    const sidebar = document.getElementById('filterSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    // Abre a barra
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });
    }

    // Fecha no X
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }

    // Fecha se clicar no fundo escuro
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
});