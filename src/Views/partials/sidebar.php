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
    $base = 'flex items-center justify-between p-3 rounded-lg shadow-sm transition-colors nav-item';
    $class = $isActive ? 'nav-item-active' : 'nav-item-inactive';

    echo '<a href="' . htmlspecialchars($href) . '" class="' . $base . ' ' . $class . '">
            <span class="font-medium">' . htmlspecialchars($label) . '</span>
            <span class="material-icons sidebar-icon">' . htmlspecialchars($icon) . '</span>
          </a>';
}
?>

<!-- Botón hamburguesa para móviles -->
<button id="mobile-menu-btn" class="fixed top-4 left-4 z-50 md:hidden mobile-menu-btn">
    <span class="material-icons">menu</span>
</button>

<!-- Overlay para cerrar menú en móviles -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

<aside id="sidebar" class="fixed top-0 left-0 w-72 h-screen p-4 flex flex-col shadow-md z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out sidebar-bg">
    <div class="flex items-center mb-8">
        <div>
            <img src="/config/icons/logo-solo.svg" alt="Logo Datasnap" class="w-10 h-10">
        </div>
        <div class="ml-3">
            <h1 class="font-bold sidebar-title">DATASNAP</h1>
            <p class="text-xs sidebar-subtitle">ORGANIZA A LA VELOCIDAD DE UN ZAP</p>
        </div>
    </div>

    <div class="flex items-center space-x-2 px-4 py-2 mb-4 rounded-md shadow-sm user-info">
        <span class="material-icons">account_circle</span>
        <span class="font-medium"><?php echo htmlspecialchars($userData['username'] ?? 'Usuario'); ?></span>
    </div>

    <nav class="space-y-2">
        <?php ds_nav_item('/panel', 'Subir Archivo', 'add', 'panel', $active); ?>
        <?php ds_nav_item('/files', 'Mis Bases de Datos', 'storage', 'files', $active); ?>
        <?php ds_nav_item('/chatbot', 'ChatBot', 'chat', 'chatbot', $active); ?>
        <?php ds_nav_item('/configuracion', 'Configuración', 'settings', 'settings', $active); ?>
                <div class="flex items-center justify-between p-3 rounded-lg shadow-sm theme-selector">
            <span class="font-medium">Tema</span>
            <div class="tema-selector">
                <select id="tema-select" class="text-sm border rounded px-2 py-1 theme-select">
                    <option value="auto">Auto</option>
                    <option value="light">Claro</option>
                    <option value="dark">Oscuro</option>
                </select>
            </div>  
        </div>
    </nav>

    <div class="mt-auto pt-4 sidebar-footer">
        <div class="flex justify-around">
            <a class="logout-btn" href="/logout" title="Cerrar Sesión">
                <span class="material-icons">logout</span>
            </a>
        </div>
        <div class="p-4 text-center text-xs footer-text">
            ©<?php echo date('Y'); ?> Datasnap | Todos los derechos reservados
        </div>
    </div>
</aside>

<script>
// Control del menú hamburguesa
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');

function toggleSidebar() {
    sidebar.classList.toggle('-translate-x-full');
    sidebarOverlay.classList.toggle('hidden');
}

mobileMenuBtn?.addEventListener('click', toggleSidebar);
sidebarOverlay?.addEventListener('click', toggleSidebar);
</script> 