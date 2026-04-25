<?php
header('Content-Type: application/json');

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

$nombre = trim($_POST['nombre'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$articulo_id = intval($_POST['articulo_id'] ?? 0);

if (!$articulo_id || $nombre === '' || $contenido === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos son obligatorios.'
    ]);
    exit;
}

$parent_id = $_POST['parent_id'] !== "" ? intval($_POST['parent_id']) : null;

$stmt = $pdo->prepare("
    INSERT INTO blog_comentarios (articulo_id, parent_id, nombre, contenido, estado)
    VALUES (:articulo_id, :parent_id, :nombre, :contenido, 'oculto')
");

$stmt->execute([
    'articulo_id' => $articulo_id,
    'parent_id' => $parent_id,
    'nombre' => $nombre,
    'contenido' => $contenido
]);

echo json_encode([
    'success' => true,
    'message' => 'Comentario enviado. Será visible cuando sea aprobado.',
    'data' => [
        'nombre' => htmlspecialchars($nombre),
        'contenido' => nl2br(htmlspecialchars($contenido)),
        'fecha' => formatearFecha(date('Y-m-d H:i:s'))
    ]
]);