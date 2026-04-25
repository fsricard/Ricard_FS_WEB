<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

// Validar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token inválido");
}

// Validar ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    die("ID inválido");
}

// Cargar artículo desde la papelera
$stmt = $pdo->prepare("SELECT * FROM blog_papelera WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Artículo no encontrado en la papelera");
}

// Borrar imagen definitivamente
if (!empty($item['imagen_destacada'])) {
    $ruta = __DIR__ . '/../uploads/papelera/' . $item['imagen_destacada'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// ---------------------------------------------
// ELIMINAR COMENTARIOS ASOCIADOS DEFINITIVAMENTE
// ---------------------------------------------
$stmtDelComentarios = $pdo->prepare("
    DELETE FROM blog_comentarios_papelera
    WHERE articulo_papelera_id = :articulo_papelera_id
");
$stmtDelComentarios->execute([':articulo_papelera_id' => $id]);

// Borrar registro de la papelera
$stmtDel = $pdo->prepare("DELETE FROM blog_papelera WHERE id = :id");
$stmtDel->execute([':id' => $id]);

// Registrar log
$logRuta = __DIR__ . '/../logs/blog_delete.log';
$usuarioId = $_SESSION['usuario_id'] ?? '0';
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'desconocido';
$fecha = date('Y-m-d H:i:s');

$logLinea = "[$fecha] Usuario: $usuarioNombre (ID $usuarioId) | Eliminó DEFINITIVAMENTE artículo ID original: {$item['articulo_id']} | Título: {$item['titulo']}\n";
file_put_contents($logRuta, $logLinea, FILE_APPEND);

// Redirigir con mensaje
header("Location: blog_papelera.php?deleted=ok");
exit;