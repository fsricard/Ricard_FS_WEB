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

// Restaurar imagen
$imagen_restaurada = null;

if (!empty($item['imagen_destacada'])) {
    $origen = __DIR__ . '/../uploads/papelera/' . $item['imagen_destacada'];
    $destino = __DIR__ . '/../uploads/blog/' . $item['imagen_destacada'];

    if (file_exists($origen)) {
        rename($origen, $destino);
        $imagen_restaurada = $item['imagen_destacada'];
    }
}

// Insertar de nuevo en blog_articulos
$stmtRestore = $pdo->prepare("
    INSERT INTO blog_articulos
    (titulo, slug, contenido, resumen, categoria_id, estado, usuario_id, imagen_destacada, fecha_creacion, fecha_actualizacion)
    VALUES
    (:titulo, :slug, :contenido, :resumen, :categoria_id, :estado, :usuario_id, :imagen_destacada, NOW(), NOW())
");

$stmtRestore->execute([
    ':titulo'           => $item['titulo'],
    ':slug'             => $item['slug'],
    ':contenido'        => $item['contenido'],
    ':resumen'          => $item['resumen'],
    ':categoria_id'     => $item['categoria_id'],
    // Lo restauramos siempre como borrador para revisión
    ':estado'           => 'borrador',
    ':usuario_id'       => $item['usuario_id'],
    ':imagen_destacada' => $imagen_restaurada
]);

// Nuevo ID del artículo restaurado
$nuevoArticuloId = $pdo->lastInsertId();

// -----------------------------
// Restaurar comentarios vinculados
// -----------------------------

// Obtenemos los comentarios de la papelera asociados a este registro de blog_papelera
$stmtComentariosPap = $pdo->prepare("
    SELECT * FROM blog_comentarios_papelera
    WHERE articulo_papelera_id = :articulo_papelera_id
");
$stmtComentariosPap->execute([':articulo_papelera_id' => $id]);
$comentariosPap = $stmtComentariosPap->fetchAll(PDO::FETCH_ASSOC);

if ($comentariosPap) {
    // Insertar de nuevo en blog_comentarios
    $stmtComentRestore = $pdo->prepare("
        INSERT INTO blog_comentarios
        (articulo_id, usuario_id, nombre, contenido, fecha_creacion, estado)
        VALUES
        (:articulo_id, :usuario_id, :nombre, :contenido, :fecha_creacion, :estado)
    ");

    foreach ($comentariosPap as $comentario) {
        $stmtComentRestore->execute([
            ':articulo_id'    => $nuevoArticuloId,          // OJO: apuntamos al nuevo ID
            ':usuario_id'     => $comentario['usuario_id'],
            ':nombre'         => $comentario['nombre'],
            ':contenido'      => $comentario['contenido'],
            // No lo teníamos en la papelera, así que podemos usar NOW() o añadir ese campo en el futuro
            ':fecha_creacion' => date('Y-m-d H:i:s'),
            ':estado'         => 'oculto'                  // Para que se moderen de nuevo
        ]);
    }

    // Borrar comentarios de la papelera
    $stmtDelComentariosPap = $pdo->prepare("
        DELETE FROM blog_comentarios_papelera
        WHERE articulo_papelera_id = :articulo_papelera_id
    ");
    $stmtDelComentariosPap->execute([':articulo_papelera_id' => $id]);
}

// Borrar el artículo de la papelera
$stmtDel = $pdo->prepare("DELETE FROM blog_papelera WHERE id = :id");
$stmtDel->execute([':id' => $id]);

// Registrar log
$logRuta = __DIR__ . '/../logs/blog_delete.log';
$usuarioId = $_SESSION['usuario_id'] ?? '0';
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'desconocido';
$fecha = date('Y-m-d H:i:s');

$logLinea = "[$fecha] Usuario: $usuarioNombre (ID $usuarioId) | Restauró artículo ID original: {$item['articulo_id']} -> Nuevo ID: $nuevoArticuloId | Título: {$item['titulo']}\n";
file_put_contents($logRuta, $logLinea, FILE_APPEND);

// Redirigir con mensaje
header("Location: blog_papelera.php?restore=ok");
exit;