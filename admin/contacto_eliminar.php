<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: contacto.php?error=ID_invalido");
    exit;
}

$id = intval($_GET['id']);

try {
    // Comprobar si existe
    $stmt = $pdo->prepare("SELECT id FROM mensajes_contacto WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        header("Location: contacto.php?error=No_existe");
        exit;
    }

    // Eliminar
    $delete = $pdo->prepare("DELETE FROM mensajes_contacto WHERE id = :id");
    $delete->execute([':id' => $id]);

    header("Location: contacto.php?ok=eliminado");
    exit;

} catch (PDOException $e) {
    die("Error al eliminar el mensaje: " . $e->getMessage());
}