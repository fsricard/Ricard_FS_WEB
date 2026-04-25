<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

// Validar parámetro
if (!isset($_GET['tabla']) || empty($_GET['tabla'])) {
    header("Location: tablas_de_datos.php");
    exit;
}

$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla']); // Sanitizar

// Obtener lista de tablas
$stmt = $pdo->query("SHOW TABLES");
$tablasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Comprobar existencia
if (!in_array($tabla, $tablasDisponibles)) {
    $mensaje = mostrarAlerta("La tabla <strong>$tabla</strong> no existe.", "error");
} 
// Proteger tabla usuarios
elseif ($tabla === 'usuarios') {
    $mensaje = mostrarAlerta("La tabla <strong>usuarios</strong> está protegida y no puede vaciarse.", "warning");
} 
else {
    // Vaciar tabla
    try {
        $pdo->exec("TRUNCATE TABLE `$tabla`");
        $mensaje = mostrarAlerta("La tabla <strong>$tabla</strong> ha sido vaciada correctamente.", "success");
    } catch (Exception $e) {
        $mensaje = mostrarAlerta("Error al vaciar la tabla <strong>$tabla</strong>.", "error");
    }
}

// Guardar mensaje en sesión para mostrarlo después de redirigir
$_SESSION['mensaje_tablas'] = $mensaje;

// Volver al listado
header("Location: tablas_de_datos.php");
exit;