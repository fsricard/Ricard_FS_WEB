<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

// Validar tabla
if (!isset($_GET['tabla']) || empty($_GET['tabla'])) {
    header("Location: tablas_de_datos.php");
    exit;
}

$tabla = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['tabla']); // Sanitizar

// Comprobar existencia
$stmt = $pdo->query("SHOW TABLES");
$tablasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array($tabla, $tablasDisponibles)) {
    $mensaje = mostrarAlerta("La tabla <strong>$tabla</strong> no existe.", "error");
    $tabla = null;
}

if ($tabla) {
    // Obtener columnas
    $columnas = $pdo->query("SHOW COLUMNS FROM `$tabla`")->fetchAll(PDO::FETCH_COLUMN);

    // Obtener registros
    $datos = $pdo->query("SELECT * FROM `$tabla`")->fetchAll(PDO::FETCH_ASSOC);
}

$pagina = 'tablas_de_datos_ver';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">

            <h2>
                Contenido de la tabla 
                <strong><?= htmlspecialchars($tabla) ?></strong>
            </h2>

            <?= $mensaje ?>

            <a href="tablas_de_datos.php" class="btn btn-volver" style="margin-bottom:15px;">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>

            <?php if ($tabla): ?>

                <table class="tabla-gestion">
                    <thead>
                        <tr>
                            <?php foreach ($columnas as $col): ?>
                                <th><?= htmlspecialchars($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($datos)): ?>
                            <tr>
                                <td colspan="<?= count($columnas) ?>" style="text-align:center;">
                                    No hay registros en esta tabla.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($datos as $fila): ?>
                                <tr>
                                    <?php foreach ($columnas as $col): ?>
                                        <td><?= htmlspecialchars($fila[$col]) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include('includes/footer.php'); ?>