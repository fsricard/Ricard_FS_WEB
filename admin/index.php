<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$error = '';
$expiredMsg = isset($_GET['expired']) ? 'Tu sesión ha expirado por inactividad. Vuelve a iniciar sesión.' : '';

// Inicializar contador de intentos
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['lock_until'])) {
    $_SESSION['lock_until'] = 0;
}

// Si está bloqueado por demasiados intentos
if (time() < $_SESSION['lock_until']) {
    $error = 'Demasiados intentos fallidos. Espera unos minutos antes de volver a intentarlo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $nombre = trim($_POST['nombre'] ?? '');
    $clave  = $_POST['clave'] ?? '';
    $csrf   = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
        $error = 'Solicitud no válida. Inténtalo de nuevo.';
    } elseif ($nombre === '' || $clave === '') {
        $error = 'Por favor, introduce tu usuario y contraseña.';
    } else {
        if (login($nombre, $clave)) {
            secureSessionRegenerate();
            $_SESSION['login_attempts'] = 0; // reset al éxito
            logSessionEvent("Login correcto", $_SESSION['usuario_nombre']);
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['login_attempts']++;
            logSessionEvent("Login fallido", $nombre);
            if ($_SESSION['login_attempts'] >= 5) {
                // Bloqueo de 5 minutos tras 5 intentos fallidos
                $_SESSION['lock_until'] = time() + 300;
                $error = 'Has superado el número máximo de intentos. Espera 5 minutos.';
            } else {
                $error = 'Usuario o contraseña incorrectos. Intento ' . $_SESSION['login_attempts'] . ' de 5.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso | Admin</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <main class="login-container">
        <form method="post" class="login-form" autocomplete="off" novalidate>
            <h1>Acceso al panel</h1>

            <?php if ($expiredMsg): ?>
                <p class="alert alert-warning"><?= htmlspecialchars($expiredMsg) ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <label for="nombre">Usuario</label>
            <input type="text" id="nombre" name="nombre" required autofocus>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave" required>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>