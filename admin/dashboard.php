<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once(__DIR__ . '/../config/funciones.php');

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$pagina='dashboard';

include('includes/header.php');
?>

    <main>
        <section>
            <h2>Resumen del sistema</h2>
            <p>Este es tu panel de control. Aquí podrás gestionar usuarios, revisar logs y configurar tu blog íntimo.</p>
        </section>
    </main>
</body>
</html>