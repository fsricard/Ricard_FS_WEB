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

// Cargar artículos de la papelera papelera
$stmt = $pdo->query("
    SELECT * 
    FROM blog_papelera 
    ORDER BY fecha_eliminacion DESC
");
$papelera = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pagina = 'blog_papelera';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">

            <h2>Papelera de artículos</h2>

            <?php if (isset($_GET['restore']) && $_GET['restore'] === 'ok'): ?>
                <div class="alert alert-success" style="margin: 20px 0; padding: 12px; border-radius: 6px; background: #d4edda; color: #155724;">
                    <i class="fa-solid fa-circle-check"></i> Artículo restaurado correctamente
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'ok'): ?>
                <div class="alert alert-success" style="margin: 20px 0; padding: 12px; border-radius: 6px; background: #d4edda; color: #155724;">
                    <i class="fa-solid fa-circle-check"></i> Artículo eliminado definitivamente
                </div>
            <?php endif; ?>

            <?php if (empty($papelera)): ?>
                <p>No hay artículos en la papelera.</p>
            <?php else: ?>

                <table class="tabla-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Eliminado por</th>
                            <th>Fecha eliminación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($papelera as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>

                                <td>
                                    <?php if (!empty($item['imagen_destacada'])): ?>
                                        <img src="<?= asset('uploads/papelera/' . $item['imagen_destacada']) ?>"
                                            style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
                                    <?php else: ?>
                                        <span style="color:#aaa;">Sin imagen</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($item['titulo']) ?></td>

                                <td><?= htmlspecialchars($item['eliminado_por']) ?></td>

                                <td><?= date('d/m/Y H:i', strtotime($item['fecha_eliminacion'])) ?></td>

                                <td style="display:flex; gap:10px;">

                                    <!-- Restaurar -->
                                    <form action="blog_restore.php" method="POST" style="display:inline;" class="btn-base">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button class="btn update-user">
                                            <i class="fa-solid fa-rotate-left"></i> Restaurar
                                        </button>
                                    </form>

                                    <!-- Eliminar definitivamente -->
                                    <form action="blog_delete_forever.php" method="POST" style="display:inline;" class="btn-base">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button class="btn delete-user"
                                            onclick="return confirm('¿Eliminar definitivamente este artículo? Esta acción no se puede deshacer.');">
                                            <i class="fa-solid fa-skull-crossbones"></i> Eliminar
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

        </div>
    </section>
</main>

<?php include('../../includes/footer.php');
