<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once(__DIR__ . '/../config/funciones.php');

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// --------------------------------------------------
// ACCIONES: Restaurar / Eliminar definitivamente
// --------------------------------------------------
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    // Obtener datos del comentario en papelera
    $stmt = $pdo->prepare("SELECT * FROM blog_comentarios_papelera WHERE id=?");
    $stmt->execute([$id]);
    $comentario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comentario) {

        // RESTAURAR
        if ($accion === 'restaurar') {
            $stmt = $pdo->prepare("
                INSERT INTO blog_comentarios (articulo_id, usuario_id, nombre, contenido, fecha_creacion, estado)
                VALUES (?, ?, ?, ?, NOW(), 'oculto')
            ");
            $stmt->execute([
                $comentario['comentario_id'],   // OJO: aquí guardamos el ID del artículo original
                $comentario['usuario_id'],
                $comentario['nombre'],
                $comentario['contenido']
            ]);

            // Eliminar de la papelera
            $stmt = $pdo->prepare("DELETE FROM blog_comentarios_papelera WHERE id=?");
            $stmt->execute([$id]);
        }

        // ELIMINAR DEFINITIVAMENTE
        if ($accion === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM blog_comentarios_papelera WHERE id=?");
            $stmt->execute([$id]);
        }
    }

    header("Location: blog_comentarios_papelera.php");
    exit;
}

// --------------------------------------------------
// PAGINACIÓN
// --------------------------------------------------
$por_pagina = 10;
$pagina_actual = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Contar total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_comentarios_papelera");
$stmt->execute();
$total = $stmt->fetchColumn();
$total_paginas = ceil($total / $por_pagina);

// Obtener registros
$stmt = $pdo->prepare("
    SELECT * FROM blog_comentarios_papelera
    ORDER BY fecha_eliminacion DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pagina='blog_comentarios_papelera';

include('includes/header.php');
?>

    <main>
        <section>
            <div class="container">
                <h2>Gestionar los comentarios del blog de la papelera de reciclaje</h2>

                <table class="tabla-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Artículo</th>
                            <th>Nombre</th>
                            <th>Comentario</th>
                            <th>Fecha eliminación</th>
                            <th>Eliminado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($comentarios as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= $c['comentario_id'] ?></td>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['contenido']) ?></td>
                            <td><?= $c['fecha_eliminacion'] ?></td>
                            <td><?= htmlspecialchars($c['eliminado_por']) ?></td>

                            <td>
                                <!-- Restaurar -->
                                <button class="btn update-user"
                                        onclick="window.location='?accion=restaurar&id=<?= $c['id'] ?>'">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurar
                                </button>

                                <!-- Eliminar definitivamente -->
                                <button class="btn delete-user"
                                        onclick="if(confirm('¿Eliminar este comentario definitivamente?')) window.location='?accion=eliminar&id=<?= $c['id'] ?>'">
                                    <i class="fa-solid fa-skull-crossbones"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- PAGINADOR -->
                <?php
                    $por_pagina = 10;
                    $pagina_actual = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
                    echo paginador($total, $por_pagina, $pagina_actual, $_GET, 'p');
                ?>
            </div>
        </section>
    </main>

<?php include('includes/footer.php');