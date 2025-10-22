<?php
/**
 * Componente Sidebar fijo para todas las vistas del panel
 * Uso: establecer $active = 'panel' | 'files' | 'history' | 'settings' antes de incluir este archivo.
 * Requiere: variable opcional $userData con ['username']
 * Ejemplo: <?php $active = 'panel'; include __DIR__ . '/partials/sidebar.php'; ?>
 */

if (!isset($active)) {
    $active = '';
}

// Función para generar elementos de navegación
function ds_nav_item($href, $label, $icon, $key, $active) {
    $isActive = ($active === $key);
    $class = $isActive ? 'nav-item nav-item-active' : 'nav-item nav-item-inactive';

    echo '<a href="' . htmlspecialchars($href) . '" class="' . $class . '" onclick="if(window.innerWidth < 768) { document.getElementById(\'sidebar\').classList.add(\'-translate-x-full\'); document.getElementById(\'overlay\').classList.add(\'hidden\'); }">
            <span class="nav-label">' . htmlspecialchars($label) . '</span>
            <span class="material-icons sidebar-icon">' . htmlspecialchars($icon) . '</span>
          </a>';
}
?>

<!-- Botón hamburguesa para móviles -->
<button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('overlay').classList.toggle('hidden')" class="fixed top-4 left-4 z-50 md:hidden mobile-menu-btn">
    <span class="material-icons">menu</span>
</button>

<!-- Overlay para cerrar menú en móviles -->
<div id="overlay" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

<aside id="sidebar" class="fixed top-0 left-0 w-72 h-screen p-4 flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out sidebar">
    <div class="sidebar-header">
        <div>
            <img src="/config/icons/logo-solo.svg" alt="Logo Datasnap" class="sidebar-logo">
        </div>
        <div class="sidebar-brand">
            <h1 class="sidebar-title">DATASNAP</h1>
            <p class="sidebar-subtitle">ORGANIZA A LA VELOCIDAD DE UN ZAP</p>
        </div>
    </div>

    <div class="user-info">
        <span class="material-icons">account_circle</span>
        <span class="user-name"><?php echo htmlspecialchars($userData['username'] ?? 'Usuario'); ?></span>
    </div>

    <nav class="sidebar-nav">
        <?php ds_nav_item('/panel', 'Subir Archivo', 'add', 'panel', $active); ?>
        <?php ds_nav_item('/files', 'Mis Bases de Datos', 'storage', 'files', $active); ?>
        <?php ds_nav_item('/chatbot', 'ChatBot', 'chat', 'chatbot', $active); ?>
        <?php ds_nav_item('/configuracion', 'Configuración', 'settings', 'settings', $active); ?>
        <div class="theme-selector">
            <span class="theme-label">Tema</span>
            <div class="tema-selector">
                <select id="tema-select" class="theme-select">
                    <option value="auto">Auto</option>
                    <option value="light">Claro</option>
                    <option value="dark">Oscuro</option>
                </select>
            </div>  
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="logout-container">
            <a class="logout-btn" href="/logout" title="Cerrar Sesión">
                <span class="material-icons">logout</span>
            </a>
        </div>
        <div class="footer-text">
            ©<?php echo date('Y'); ?> Datasnap | Todos los derechos reservados
        </div>
    </div>
</aside>

 