// Manejo del tema (modo oscuro/claro)
document.addEventListener('DOMContentLoaded', function() {
    const temaSelect = document.getElementById('tema-select');
    
    const temaGuardado = localStorage.getItem('tema') || 'auto';
    if (temaSelect) {
        temaSelect.value = temaGuardado;
        aplicarTema(temaGuardado);

        temaSelect.addEventListener('change', function() {
            const nuevoTema = this.value;
            localStorage.setItem('tema', nuevoTema);
            aplicarTema(nuevoTema);
        });
    }
    
    function aplicarTema(tema) {
        const body = document.body;
        
        if (tema === 'dark') {
            body.setAttribute('data-theme', 'dark');
        } else if (tema === 'light') {
            body.removeAttribute('data-theme');
        } else { // auto
            const esModoOscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (esModoOscuro) {
                body.setAttribute('data-theme', 'dark');
            } else {
                body.removeAttribute('data-theme');
            }
        }
    }
    
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
        if (temaSelect && temaSelect.value === 'auto') {
            aplicarTema('auto');
        }
    });
});