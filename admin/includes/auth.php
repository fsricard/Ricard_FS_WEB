<?php
require_once __DIR__ . '/../../config/database.php';

// Iniciar sesión de usuario
function login($nombre, $clave) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ?");
    $stmt->execute([$nombre]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($clave, $usuario['clave'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        return true;
    }
    return false;
}

// Comprobar si hay sesión activa
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

// Comprobar si el usuario es admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['usuario_rol'] === 'admin';
}