<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Error 404 - Datasnap</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/config/css/panel.css">
</head>
<body class="bg-white flex">
    <!-- Sidebar igual que en las otras vistas -->
    <aside class="w-72 bg-gray-100 min-h-screen p-4 flex flex-col">
        <div class="flex items-center mb-8">
            <div>
                <img src="/config/images/logo-solo.png" alt="Logo Datasnap" class="w-10 h-10">
            </div>
            <div>
                <h1 class="font-bold text-gray-800">DATASNAP</h1>
                <p class="text-xs text-gray-500">ORGANIZA A LA VELOCIDAD DE UN ZAP</p>
            </div>
        </div>
        <nav class="space-y-2">
            <a href="/" class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <span class="font-medium text-gray-700">Inicio</span>
                <span class="material-icons sidebar-icon text-gray-600">home</span>
            </a>
            <a href="/login" class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <span class="font-medium text-gray-700">Iniciar Sesión</span>
                <span class="material-icons sidebar-icon text-gray-600">login</span>
            </a>
            <a href="/register" class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                <span class="font-medium text-gray-700">Registrarse</span>
                <span class="material-icons sidebar-icon text-gray-600">person_add</span>
            </a>
        </nav>
    </aside>

    <!-- Contenido principal con mensaje de error -->
    <main class="flex-1 relative">
        <header class="flex justify-end items-center p-4 bg-white shadow-md">
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <span class="font-medium text-gray-700">Usuario</span>
                </div>
                <span class="material-icons text-gray-600 cursor-pointer">settings</span>
            </div>
        </header>

        <div class="flex flex-col items-center justify-center h-full -mt-16">
            <div class="w-full h-40 bg-[#26AAB5] absolute top-16"
                style="clip-path: ellipse(80% 50% at 50% 0%); z-index: 0;"></div>
            <div class="flex flex-col items-center justify-center z-10 text-center px-6">
                <span class="material-icons text-8xl text-gray-300 mb-4">error_outline</span>
                <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
                <h2 class="text-2xl font-medium text-gray-600 mb-4">Página no encontrada</h2>
                <p class="text-gray-500 mb-6 max-w-md">
                    La página que buscas no existe o ha sido movida.
                    Verifica la URL o regresa a la página principal.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <span class="material-icons mr-2">home</span>
                        Volver al inicio
                    </a>
                    <button onclick="history.back()" class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                        <span class="material-icons mr-2">arrow_back</span>
                        Página anterior
                    </button>
                </div>
            </div>
        </div>

        <!-- Indicaciones extras -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
            <button class="flex items-center justify-center bg-gray-800 text-white px-8 py-3 rounded-full shadow-lg text-lg">
                ¿Necesitas ayuda?
                <span class="material-icons ml-4 bg-gray-600 rounded-full p-1">keyboard_arrow_up</span>
            </button>
        </div>
    </main>

    <script>
        // Animación de las waves decorativas
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar waves decorativas si no existen
            if (!document.querySelector('.wave1')) {
                const mainElement = document.querySelector('main');
                const waves = `
                    <section class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="wave wave1"></div>
                        <div class="wave wave2"></div>
                        <div class="wave wave3"></div>
                        <div class="wave wave4"></div>
                    </section>
                `;
                mainElement.insertAdjacentHTML('afterbegin', waves);
            }
        });
    </script>
</body>
</html>