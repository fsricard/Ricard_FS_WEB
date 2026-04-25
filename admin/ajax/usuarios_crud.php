<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['message'=>'Acceso denegado']);
    exit;
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

if ($action === 'crear') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $clave  = password_hash($_POST['clave'], PASSWORD_BCRYPT);
    $rol    = $_POST['rol'];
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?,?,?,?)");
    $stmt->execute([$nombre,$correo,$clave,$rol]);
    echo json_encode(['message'=>'Usuario creado']);
}
elseif ($action === 'editar') {
    $id = (int)$_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol    = $_POST['rol'];
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id=?");
    $stmt->execute([$nombre,$correo,$rol,$id]);
    echo json_encode(['message'=>'Usuario actualizado']);
}
elseif ($action === 'eliminar') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['message'=>'Usuario eliminado']);
}