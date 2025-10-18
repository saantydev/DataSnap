// Cargar y aplicar tema guardado inmediatamente
function initThemeImmediately() {
    const guardado = localStorage.getItem('tema');
    const root = document.documentElement;

    if (guardado === 'light' || guardado === 'dark') {
        root.setAttribute('data-tema', guardado);
    } else {
        root.removeAttribute('data-tema');
    }
}

initThemeImmediately();