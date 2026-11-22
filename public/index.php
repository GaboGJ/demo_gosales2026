<?php
// 1. Cargar la configuración global (conexión BD, sesiones, constantes)
require_once __DIR__ . '/../config/config.php';

// 2. Obtener y limpiar la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/auth_controller/index';

// Eliminar 'index.php' o 'index.php/' del inicio de la URL si existe
$url = preg_replace('/^index\.php\/?/', '', $url);
$url = rtrim($url, '/');

// Si la URL quedó vacía tras limpiar, asignamos la ruta por defecto
if (empty($url)) {
    $url = 'auth/auth_controller/index';
}

$url = explode('/', $url);

// --- REGLAS DE ENRUTAMIENTO INTELIGENTE ---
if (count($url) == 1 && !empty($url[0])) {
    // Caso 1 parámetro: ej. 'dashboard' -> carpeta 'dashboard', controlador 'dashboard_controller', acción 'index'
    $folder     = $url[0];
    $controller = $url[0] . '_controller'; 
    $action     = 'index';
} elseif (count($url) == 2) {
    $folder = $url[0];
    
    if (strpos($url[1], '_controller') !== false) {
        // Caso 2 parámetros con _controller: ej. 'auth/auth_controller'
        $controller = $url[1];
        $action     = 'index';
    } else {
        // Caso 2 parámetros de acción corta: ej. 'encomiendas/new'
        $controller = $url[0] . '_controller';
        $action     = $url[1];
    }
} else {
    // Caso 3 o más parámetros: ej. 'auth/auth_controller/index'
    $folder     = !empty($url[0]) ? $url[0] : 'auth';               
    $controller = !empty($url[1]) ? $url[1] : 'auth_controller';      
    $action     = !empty($url[2]) ? $url[2] : 'index';                
}

// Formatear el nombre de la clase (Asegura capitalización estándar)
$controllerClassName = ucfirst(str_replace(' ', '', $controller));

// 3. Ruta física del archivo usando __DIR__ para el servidor 
$archivoControlador = __DIR__ . '/../app/controllers/' . $folder . '/' . $controllerClassName . '.php';

// Si no existe con mayúscula inicial, probar con el nombre exacto enviado (minúsculas)
if (!file_exists($archivoControlador)) {
    $archivoControlador = __DIR__ . '/../app/controllers/' . $folder . '/' . $controller . '.php';
}

// 4. Verificar si el archivo del controlador existe
if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    
    // Intentar instanciar la clase (probar con minúscula o mayúscula inicial)
    $nombreClase = class_exists($controllerClassName) ? $controllerClassName : (class_exists($controller) ? $controller : null);

    if ($nombreClase) {
        $controllerInstance = new $nombreClase();
        
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            http_response_code(404);
            echo "<h1>Error 404</h1><p>La acción '{$action}' solicitada no existe en el controlador '{$nombreClase}'.</p>";
        }
    } else {
        http_response_code(404);
        echo "<h1>Error 404</h1><p>La clase del controlador '{$controllerClassName}' no está definida correctamente.</p>";
    }
} else {
    http_response_code(404);
    echo "<h1>Error 404</h1><p>El archivo del controlador no fue encontrado en la ruta: <strong>{$archivoControlador}</strong></p>";
}
?>