/**
 * DataSnap - Sistema de gestión de archivos
 * JavaScript principal para detección de modo y funcionalidades globales
 */

// Detección y manejo del modo oscuro/claro
class ThemeManager {
    constructor() {
        this.init();
    }

    init() {
        this.detectSystemTheme();
        this.setupThemeListeners();
    }

    detectSystemTheme() {
        const savedTheme = localStorage.getItem('datasnap-theme');
        
        if (savedTheme) {
            this.setTheme(savedTheme);
        } else {
            // Detectar preferencia del sistema
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.setTheme(prefersDark ? 'dark' : 'light');
        }
    }

    setTheme(theme) {
        document.documentElement.setAttribute('data-tema', theme);
        localStorage.setItem('datasnap-theme', theme);
        
        // Disparar evento personalizado para otros componentes
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme } 
        }));
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-tema');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }

    setupThemeListeners() {
        // Escuchar cambios en las preferencias del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('datasnap-theme')) {
                this.setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
}

// Utilidades globales
class DataSnapUtils {
    static formatFileSize(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    static showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${this.getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        // Agregar estilos si no existen
        this.ensureNotificationStyles();
        
        document.body.appendChild(notification);
        
        // Auto-remover
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }
        }, duration);
    }

    static getNotificationIcon(type) {
        const icons = {
            error: '❌',
            success: '✅',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    static ensureNotificationStyles() {
        if (!document.querySelector('#notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: var(--color-fondo);
                    color: var(--color-texto);
                    border-radius: 12px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    min-width: 300px;
                    z-index: 1000;
                    animation: slideInRight 0.3s ease-out;
                    border-left: 4px solid var(--color-primario);
                    border: 1px solid rgba(var(--color-primario-rgb), 0.2);
                }
                .notification-error { border-left-color: #ef4444; }
                .notification-success { border-left-color: #10b981; }
                .notification-warning { border-left-color: #f59e0b; }
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    color: var(--color-texto-secundario);
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .notification-close:hover {
                    color: var(--color-texto);
                }
                @keyframes slideInRight {
                    from { opacity: 0; transform: translateX(100%); }
                    to { opacity: 1; transform: translateX(0); }
                }
                @keyframes slideOutRight {
                    from { opacity: 1; transform: translateX(0); }
                    to { opacity: 0; transform: translateX(100%); }
                }
            `;
            document.head.appendChild(styles);
        }
    }

    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Inicialización global
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gestor de temas
    window.themeManager = new ThemeManager();
    
    // Hacer utilidades disponibles globalmente
    window.DataSnapUtils = DataSnapUtils;
    
    // Configurar eventos globales
    setupGlobalEvents();
});

function setupGlobalEvents() {
    // Manejar errores globales
    window.addEventListener('error', function(e) {
        console.error('Error global:', e.error);
        DataSnapUtils.showNotification('Ha ocurrido un error inesperado', 'error');
    });

    // Manejar promesas rechazadas
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Promesa rechazada:', e.reason);
        DataSnapUtils.showNotification('Error de conexión', 'error');
    });
}

// Exportar para uso en otros archivos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ThemeManager, DataSnapUtils };
}