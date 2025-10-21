// FadeIn solo cuando el elemento entra en pantalla
function activarFadeInAlVer() {
    const elementosFade = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('fade-in-activo')) {
                entry.target.classList.add('fade-in-activo');
            }
        });
    }, { threshold: 0.2 });

    elementosFade.forEach(el => observer.observe(el));
}

// Esperar a que la intro desaparezca para activar el fadeIn
setTimeout(() => {
    activarFadeInAlVer();
}, 2200); // Un poco después de la animación de intro


const introDiv = document.getElementById('intro-bienvenida');

// Verifica si la intro ya fue mostrada
const introMostrada = localStorage.getItem('introMostrada');

if (!introMostrada && introDiv) {
    const introLogo = introDiv.querySelector('.intro-logo');
    const introTexto = introDiv.querySelector('.intro-texto');

    // Aparecer logo con opacidad
    setTimeout(() => {
        introLogo.classList.add('visible');
    }, 100);

    // Mostrar texto y mover logo
    setTimeout(() => {
        introTexto.classList.add('visible');
        introLogo.classList.add('move-left');
    }, 700);

    // Ocultar intro después de 2 segundos
    setTimeout(() => {
        introDiv.style.opacity = 0;
        setTimeout(() => {
            introDiv.remove();
        }, 700);
    }, 2100);

    setTimeout(() => {
        introDiv.remove();
    }, 3600);

    // Guardar en localStorage que la intro ya se mostró
    localStorage.setItem('introMostrada', 'true');
} else if (introDiv) {
    // Si ya fue mostrada, eliminar el div inmediatamente
    introDiv.remove();
    activarFadeInAlVer();
}

const menuHamburguesa = document.getElementById('menu-hamburguesa');
const opcionesMenu = document.getElementById('opciones-menu-hamburguesa');

// Crear clase activa para mostrar el menú
menuHamburguesa.addEventListener('click', () => {
    opcionesMenu.classList.toggle('menu-activo');
});

// Cerrar el menú cuando se haga clic en un enlace
opcionesMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
        opcionesMenu.classList.remove('menu-activo');
    });
});

const temaSelect = document.getElementById('tema-select');
const root = document.documentElement;

const iconoTema = document.getElementById('tema-icon');
const iconAuto = document.getElementById('icon-auto');
const iconOscuro = document.getElementById('icon-oscuro');
const iconClaro = document.getElementById('icon-claro');

// Mostrar solo el icono correspondiente al tema
function actualizarIconoTema(valor) {
    iconAuto.style.display = 'none';
    iconOscuro.style.display = 'none';
    iconClaro.style.display = 'none';
    if (valor === 'auto') {
        iconAuto.style.display = 'inline';
    } else if (valor === 'dark') {
        iconOscuro.style.display = 'inline';
    } else if (valor === 'light') {
        iconClaro.style.display = 'inline';
    }
}

// Animación suave para el cambio de tema
function animateThemeChange() {
    root.style.transition = 'background-color 0.7s, color 0.7s';
    setTimeout(() => {
        root.style.transition = '';
    }, 800);
}

// Aplicar tema
function setTheme(mode) {
    animateThemeChange();
    actualizarIconoTema(mode);
    if (mode === 'auto') {
        root.removeAttribute('data-tema');
        localStorage.removeItem('tema');
    } else {
        root.setAttribute('data-tema', mode);
        localStorage.setItem('tema', mode);
    }
}

// Inicializar tema e icono
function initTheme() {
    const saved = localStorage.getItem('tema');
    let valor = 'auto';
    if (saved === 'light' || saved === 'dark') {
        root.setAttribute('data-tema', saved);
        temaSelect.value = saved;
        valor = saved;
    } else {
        root.removeAttribute('data-tema');
        temaSelect.value = 'auto';
        valor = 'auto';
    }
    actualizarIconoTema(valor);
}

temaSelect.addEventListener('change', e => {
    setTheme(e.target.value);
});

initTheme()