<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

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

// Cargar artículo
$stmt = $pdo->prepare("SELECT * FROM blog_articulos WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$articulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$articulo) {
    die("Artículo no encontrado");
}

// Mover imagen a la papelera
$nuevaRutaImagen = null;

if (!empty($articulo['imagen_destacada'])) {
    $origen = __DIR__ . '/../uploads/blog/' . $articulo['imagen_destacada'];
    $destino = __DIR__ . '/../uploads/papelera/' . $articulo['imagen_destacada'];

    if (file_exists($origen)) {
        rename($origen, $destino);
        $nuevaRutaImagen = $articulo['imagen_destacada'];
    }
}

// Guardar artículo en la papelera
$stmtPap = $pdo->prepare("
    INSERT INTO blog_papelera 
    (articulo_id, titulo, slug, contenido, resumen, categoria_id, estado, usuario_id, imagen_destacada, fecha_eliminacion, eliminado_por)
    VALUES
    (:articulo_id, :titulo, :slug, :contenido, :resumen, :categoria_id, :estado, :usuario_id, :imagen_destacada, NOW(), :eliminado_por)
");

$stmtPap->execute([
    ':articulo_id'      => $articulo['id'],
    ':titulo'           => $articulo['titulo'],
    ':slug'             => $articulo['slug'],
    ':contenido'        => $articulo['contenido'],
    ':resumen'          => $articulo['resumen'],
    ':categoria_id'     => $articulo['categoria_id'],
    ':estado'           => $articulo['borrador'],
    ':usuario_id'       => $articulo['usuario_id'],
    ':imagen_destacada' => $nuevaRutaImagen,
    ':eliminado_por'    => $_SESSION['usuario'] ?? 'desconocido'
]);

// ID del artículo en la papelera
$articulo_papelera_id = $pdo->lastInsertId();

// Obtener comentarios asociados a este artículo
$stmtComentarios = $pdo->prepare("SELECT * FROM blog_comentarios WHERE articulo_id = :articulo_id");
$stmtComentarios->execute([':articulo_id' => $id]);
$comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);

if ($comentarios) {
    // Preparar insert en blog_comentarios_papelera
    $stmtComentPap = $pdo->prepare("
        INSERT INTO blog_comentarios_papelera
        (comentario_id, articulo_papelera_id, articulo_id, usuario_id, nombre, contenido, fecha_eliminacion, eliminado_por)
        VALUES
        (:comentario_id, :articulo_papelera_id, :articulo_id, :usuario_id, :nombre, :contenido, NOW(), :eliminado_por)
    ");

    foreach ($comentarios as $comentario) {
        $stmtComentPap->execute([
            ':comentario_id'        => $comentario['id'],
            ':articulo_papelera_id' => $articulo_papelera_id,
            ':articulo_id'          => $id,
            ':usuario_id'           => $comentario['usuario_id'],
            ':nombre'               => $comentario['nombre'],
            ':contenido'            => $comentario['contenido'],
            ':eliminado_por'        => $_SESSION['usuario'] ?? 'desconocido'
        ]);
    }

    // Borrar comentarios originales de la tabla blog_comentarios
    $stmtDelComentarios = $pdo->prepare("DELETE FROM blog_comentarios WHERE articulo_id = :articulo_id");
    $stmtDelComentarios->execute([':articulo_id' => $id]);
}

// Borrar el artículo de la tabla blog_articulos
$stmtDel = $pdo->prepare("DELETE FROM blog_articulos WHERE id = :id");
$stmtDel->execute([':id' => $id]);

// Registrar log
$logRuta = __DIR__ . '/../logs/blog_delete.log';
$usuarioId = $_SESSION['usuario_id'] ?? '0';
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'desconocido';
$fecha = date('Y-m-d H:i:s');

$logLinea = "[$fecha] Usuario: $usuarioNombre (ID $usuarioId) | Eliminó artículo ID: $id | Título: {$articulo['titulo']}\n";
file_put_contents($logRuta, $logLinea, FILE_APPEND);

// Redirigir con mensaje
header("Location: articulos.php?delete=ok");
exit;