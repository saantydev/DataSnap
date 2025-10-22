// Inicializar tema desde localStorage
function initTheme() {
    const saved = localStorage.getItem('tema');
    const root = document.documentElement;
    const temaSelect = document.getElementById('tema-select');
    
    if (saved === 'light' || saved === 'dark') {
        root.setAttribute('data-tema', saved);
        if (temaSelect) temaSelect.value = saved;
    } else {
        root.removeAttribute('data-tema');
        if (temaSelect) temaSelect.value = 'auto';
    }
}

// Manejar cambio de tema
function setupThemeSelector() {
    const temaSelect = document.getElementById('tema-select');
    if (temaSelect) {
        temaSelect.addEventListener('change', function(e) {
            const root = document.documentElement;
            const mode = e.target.value;
            
            if (mode === 'auto') {
                root.removeAttribute('data-tema');
                localStorage.removeItem('tema');
            } else {
                root.setAttribute('data-tema', mode);
                localStorage.setItem('tema', mode);
            }
        });
    }
}

// Inicializar tema al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initTheme();
    setupThemeSelector();
});