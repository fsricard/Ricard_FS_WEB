<?php
// Opciones de seguridad
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Añadimos protección CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Tiempo máximo de inactividad (ejemplo: 30 minutos)
define('SESSION_TIMEOUT', 1800); // 1800 segundos = 30 min

// Función de logs
function logSessionEvent($evento, $usuario = null) {
    $fecha = date('Y-m-d H:i:s');
    $ip    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $linea = "[$fecha] [$ip] [$usuario] $evento" . PHP_EOL;
    file_put_contents(__DIR__ . '/../../logs/session.log', $linea, FILE_APPEND);
}

// Verificar expiración de sesión
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    // Si ha pasado el tiempo, destruimos la sesión
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: index.php?expired=1");
    exit;
}

// Actualizamos la marca de actividad
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID de sesión en login
function secureSessionRegenerate() {
    session_regenerate_id(true);
}

// Cerrar sesión manualmente
function secureSessionDestroy() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}