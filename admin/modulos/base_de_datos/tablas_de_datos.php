<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Mostrar todas las tablas de la base de datos
$stmt = $pdo->query("SHOW TABLES");
$tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

$mensaje = mostrarAlerta('', '');

$pagina = 'tablas_de_datos';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Gestión de las tablas de la base de datos ... ¡¡CUIDADO DIABLILLO!!</h2>

            <?php
            if (isset($_SESSION['mensaje_tablas'])) {
                echo $_SESSION['mensaje_tablas'];
                unset($_SESSION['mensaje_tablas']);
            }
            ?>

            <table class="tabla-gestion">
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th>Registros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tablas as $tabla): ?>

                        <?php
                        // Contar registros
                        $count = $pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();

                        // Clase especial para la tabla usuarios
                        $claseFila = ($tabla === 'usuarios') ? 'fila-critica' : '';
                        ?>

                        <tr class="<?= $claseFila ?>">
                            <td><?= htmlspecialchars($tabla) ?></td>
                            <td><?= $count ?></td>
                            <td>
                                <a href="tablas_de_datos_ver.php?tabla=<?= urlencode($tabla) ?>" class="btn btn-ver">
                                    <i class="fa-solid fa-eye"></i> Ver contenido
                                </a>

                                <?php if ($tabla !== 'usuarios'): ?>
                                    <a href="tablas_de_datos_vaciar.php?tabla=<?= urlencode($tabla) ?>"
                                        class="btn btn-peligro"
                                        onclick="return confirm('¿Seguro que quieres vaciar esta tabla?');">
                                        <i class="fa-solid fa-trash"></i> Vaciar
                                    </a>
                                <?php else: ?>
                                    <span class="protegida">
                                        <i class="fa-solid fa-lock"></i> Protegida
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </section>
</main>

<?php include('../../includes/footer.php');
