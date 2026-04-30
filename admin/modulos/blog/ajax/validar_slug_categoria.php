<?php
require_once __DIR__ . '/../../../../config/database.php';

// Validar parámetro
if (!isset($_GET['slug'])) {
    echo json_encode(['error' => 'Slug no proporcionado']);
    exit;
}

$slug = trim($_GET['slug']);
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta: si estamos editando, excluimos el ID actual
if ($id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categorias WHERE slug = ? AND id != ?");
    $stmt->bind_param("si", $slug, $id);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categorias WHERE slug = ?");
    $stmt->bind_param("s", $slug);
}

$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

echo json_encode([
    'existe' => $total > 0
]);
