<?php
require_once __DIR__ . '/includes/session.php';
logSessionEvent("Logout", $_SESSION['usuario_nombre'] ?? 'guest');
secureSessionDestroy();
header("Location: index.php");
exit;