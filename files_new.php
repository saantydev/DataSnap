<?php
/**
 * /files combinado - Sin cache y con optimización por ID
 */

session_start();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

$userData = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'Usuario',
    'email' => $_SESSION['email'] ?? ''
];

// Procesar optimización si viene action=optimizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'optimizar') {
    $input = json_decode(file_get_contents("php://input"), true);
    $archivoId = $input['id'] ?? null;

    if ($archivoId) {
        require_once 'controllers/FileController.php'; // Ajusta según tu estructura
        $controller = new \Controllers\FileController();
        $result = $controller->optimizarArchivo($archivoId);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "ID no enviado"]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Mis Bases de Datos - Datasnap</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
</head>

<body class="bg-white flex">
<aside class="w-72 bg-gray-100 min-h-screen p-4 flex flex-col">
    <!-- Sidebar -->
    <div class="flex items-center mb-8">
        <div><img src="config/icons/logo-solo.svg" alt="Logo Datasnap"></div>
        <div>
            <h1 class="font-bold text-gray-800">DATASNAP</h1>
            <p class="text-xs text-gray-500">ORGANIZA A LA VELOCIDAD DE UN ZAP</p>
        </div>
    </div>

    <div class="flex items-center px-4 py-2 text-gray-600 mb-6">
        <img alt="User avatar" class="w-10 h-10 rounded-full mr-3"
             src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJqC-zVvqfengqsRcwZ9_pp-XI9RXGHksUvAJAv8W48FsNJ2ihn6fvfCWBlfW8Ws28Zf5yB107QaTUZeGujN9V86iQWfiJAh5TM47IGyEKnLCTY-Z__OmGkb-0Mc1o8UWEdlUTs_GoxIaEcOYP_1iHKbkTCgogKQgg3It_AFGpFfaaQP4fsY10ZgXP4aYqLYZarLcfowMjn27i1iHl2Tj-PVmZHA6SZYKDSOQs1oAcBb3rsOg25yfR6ED06YjcDH931Yi-5kqd-os" />
        <span class="font-medium text-gray-700"><?php echo htmlspecialchars($userData['username']); ?></span>
    </div>

    <nav class="space-y-2">
        <a href="/panel" class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
            <span class="font-medium text-gray-700">Subir Archivo</span>
            <span class="material-icons sidebar-icon text-gray-600">add</span>
        </a>
        <div class="flex items-center justify-between bg-blue-100 p-3 rounded-lg shadow-sm">
            <span class="font-medium text-blue-700">Mis Bases de Datos</span>
            <span class="material-icons sidebar-icon text-blue-600">storage</span>
        </div>
        <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
            <span class="font-medium text-gray-700">Historial</span>
            <span class="material-icons sidebar-icon text-gray-600">history</span>
        </div>
        <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
            <span class="font-medium text-gray-700">Configuración</span>
            <span class="material-icons sidebar-icon text-gray-600">settings</span>
        </div>
    </nav>

    <div class="mt-auto">
        <div class="p-4 border-t border-gray-200">
            <div class="flex justify-around">
                <a class="text-gray-500 hover:text-gray-800" href="/logout" title="Cerrar Sesión">
                    <span class="material-icons">logout</span>
                </a>
            </div>
        </div>
        <div class="p-4 text-center text-xs text-gray-500 border-t border-gray-200">
            ©2025 Datasnap | Todos los derechos reservados
        </div>
    </div>
</aside>

<main class="flex-1 relative">
    <div class="p-6">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Archivos Subidos</h2>
                <p class="text-gray-600 mt-1">Gestiona tus bases de datos y archivos optimizados</p>
            </div>

            <div class="p-6">
                <div id="archivosContainer" class="space-y-4">
                    <div class="text-center py-12">
                        <span class="material-icons text-6xl text-gray-300">storage</span>
                        <h3 class="text-xl font-medium text-gray-600 mt-4">No hay archivos subidos</h3>
                        <p class="text-gray-500 mt-2">Sube tu primer archivo para comenzar</p>
                        <a href="/panel" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mt-4">
                            <span class="material-icons mr-2">add</span>
                            Subir Archivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Función para cargar archivos
async function cargarArchivos() {
    try {
        const res = await fetch('/files/list', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        const data = await res.json();
        if (data.success) mostrarArchivos(data.archivos || []);
        else mostrarError(data.message || 'Error al cargar archivos');
    } catch(e) { mostrarError('Error de conexión'); }
}

// Función para mostrar archivos
function mostrarArchivos(archivos) {
    const container = document.getElementById('archivosContainer');
    if (!archivos || archivos.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <span class="material-icons text-6xl text-gray-300">storage</span>
                <h3 class="text-xl font-medium text-gray-600 mt-4">No hay archivos subidos</h3>
                <p class="text-gray-500 mt-2">Sube tu primer archivo para comenzar</p>
                <a href="/panel" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 mt-4">
                    <span class="material-icons mr-2">add</span>
                    Subir Archivo
                </a>
            </div>`;
        return;
    }

    container.innerHTML = archivos.map(a => `
        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <span class="material-icons text-blue-600">${getFileIcon(a.nombre || a.ruta)}</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-800">${a.nombre || 'Archivo sin nombre'}</h4>
                        <p class="text-sm text-gray-500">Subido: ${new Date(a.fecha_subida).toLocaleDateString('es-ES')}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(a.estado)}">${getStatusText(a.estado)}</span>
                    ${a.estado === 'original' ? `<button onclick="optimizarArchivo(${a.id})" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center"><span class="material-icons mr-1">auto_fix_high</span>Optimizar</button>` : a.estado === 'optimizado' ? `<button onclick="descargarArchivo(${a.id})" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center"><span class="material-icons mr-1">download</span>Descargar</button>` : ''}
                    <button onclick="eliminarArchivo(${a.id}, '${a.nombre || 'este archivo'}')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center"><span class="material-icons mr-1">delete</span>Eliminar</button>
                </div>
            </div>
        </div>
    `).join('');
}

function mostrarError(msg) {
    document.getElementById('archivosContainer').innerHTML = `
        <div class="text-center py-12">
            <span class="material-icons text-6xl text-red-300">error</span>
            <h3 class="text-xl font-medium text-red-600 mt-4">Error</h3>
            <p class="text-red-500 mt-2">${msg}</p>
            <button onclick="cargarArchivos()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reintentar</button>
        </div>`;
}

function getFileIcon(filename) {
    if (!filename) return 'insert_drive_file';
    const ext = filename.split('.').pop().toLowerCase();
    switch (ext) {
        case 'csv': return 'table_chart';
        case 'json': return 'data_object';
        case 'sql': return 'database';
        default: return 'insert_drive_file';
    }
}

function getStatusClass(estado) {
    switch(estado) {
        case 'original': return 'bg-yellow-100 text-yellow-800';
        case 'pendiente': return 'bg-orange-100 text-orange-800';
        case 'optimizado': return 'bg-green-100 text-green-800';
        case 'borrado': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText(estado) {
    switch(estado) {
        case 'original': return 'Original';
        case 'pendiente': return 'Procesando';
        case 'optimizado': return 'Optimizado';
        case 'borrado': return 'Eliminado';
        default: return estado;
    }
}

// Optimizar archivo mediante POST con action=optimizar
async function optimizarArchivo(fileId) {
    try {
        const res = await fetch('/files?action=optimizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: fileId })
        });
        const data = await res.json();
        if (data.success) {
            alert('Archivo enviado a optimización correctamente');
            cargarArchivos();
        } else alert('Error: ' + data.message);
    } catch(e) {
        alert('Error de conexión');
    }
}

function descargarArchivo(fileId) { window.location.href = `/files/download?id=${fileId}`; }

async function eliminarArchivo(fileId, fileName) {
    if (!confirm(`¿Estás seguro de que quieres eliminar "${fileName}"?`)) return;
    try {
        const res = await fetch(`/files/delete?id=${fileId}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        if (data.success) { alert('Archivo eliminado correctamente'); cargarArchivos(); }
        else alert('Error: ' + data.message);
    } catch(e) { alert('Error de conexión'); }
}

document.addEventListener('DOMContentLoaded', cargarArchivos);
</script>
</body>
</html>
