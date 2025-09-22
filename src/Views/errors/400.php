<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 400 - Solicitud Incorrecta</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />
</head>
<body class="bg-white flex min-h-screen">
    <section class="flex-1 flex items-center justify-center p-8">
        <div class="text-center max-w-md">
            <div class="mb-8">
                <span class="material-icons text-8xl text-red-400">error_outline</span>
            </div>

            <h1 class="text-6xl font-bold text-gray-800 mb-4">400</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Solicitud Incorrecta</h2>

            <p class="text-gray-600 mb-8 leading-relaxed">
                La solicitud que enviaste no es válida o está malformada.
                Por favor, verifica la URL e intenta nuevamente.
            </p>

            <div class="space-y-4">
                <a href="/panel"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <span class="material-icons mr-2">home</span>
                    Ir al Panel
                </a>

                <br>

                <button onclick="history.back()"
                        class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <span class="material-icons mr-2">arrow_back</span>
                    Volver Atrás
                </button>
            </div>

            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500">
                    Si el problema persiste, contacta al administrador del sistema.
                </p>
            </div>
        </div>
    </section>
</body>
</html>