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

// -----------------------------
// ACCIONES (aprobar, ocultar, eliminar)
// -----------------------------
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if ($accion === 'aprobar') {
        $stmt = $pdo->prepare("UPDATE blog_comentarios SET estado='visible' WHERE id=?");
        $stmt->execute([$id]);
    }

    if ($accion === 'ocultar') {
        $stmt = $pdo->prepare("UPDATE blog_comentarios SET estado='oculto' WHERE id=?");
        $stmt->execute([$id]);
    }

    if ($accion === 'eliminar') {
        // Obtener datos del comentario antes de eliminarlo
        $stmt = $pdo->prepare("SELECT * FROM blog_comentarios WHERE id=?");
        $stmt->execute([$id]);
        $comentario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comentario) {
            // Insertar en papelera
            $stmt = $pdo->prepare("
                INSERT INTO blog_comentarios_papelera 
                (comentario_id, articulo_papelera_id, usuario_id, nombre, contenido, fecha_eliminacion, eliminado_por)
                VALUES (?, ?, ?, ?, ?, NOW(), ?)
            ");
            $stmt->execute([
                $comentario['id'],
                0, // No sabemos si el artículo está en papelera, así que 0 por defecto
                $comentario['usuario_id'],
                $comentario['nombre'],
                $comentario['contenido'],
                $_SESSION['usuario_id']
            ]);

            // Eliminar comentario original
            $stmt = $pdo->prepare("DELETE FROM blog_comentarios WHERE id=?");
            $stmt->execute([$id]);
        }
    }

    header("Location: blog_comentarios.php");
    exit;
}

// -----------------------------
// FILTROS
// -----------------------------
$filtro_nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';
$filtro_fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

$where = " WHERE 1=1 ";

if ($filtro_nombre !== '') {
    $where .= " AND nombre LIKE :nombre ";
}

if ($filtro_fecha !== '') {
    $where .= " AND DATE(fecha_creacion) = :fecha ";
}

if ($filtro_estado !== '') {
    $where .= " AND estado = :estado ";
}

// -----------------------------
// PAGINACIÓN
// -----------------------------
$por_pagina = 10;
$pagina_actual = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Contar total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_comentarios $where");
if ($filtro_nombre !== '') $stmt->bindValue(':nombre', "%$filtro_nombre%");
if ($filtro_fecha !== '') $stmt->bindValue(':fecha', $filtro_fecha);
if ($filtro_estado !== '') $stmt->bindValue(':estado', $filtro_estado);
$stmt->execute();
$total = $stmt->fetchColumn();

$total_paginas = ceil($total / $por_pagina);

// Obtener comentarios
$sql = "SELECT * FROM blog_comentarios $where ORDER BY fecha_creacion DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);

if ($filtro_nombre !== '') $stmt->bindValue(':nombre', "%$filtro_nombre%");
if ($filtro_fecha !== '') $stmt->bindValue(':fecha', $filtro_fecha);
if ($filtro_estado !== '') $stmt->bindValue(':estado', $filtro_estado);

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
$stmt->execute();

$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pagina='blog_comentarios';

include('includes/header.php');
?>

    <main>
        <section>
            <div class="container">
                <h2>Gestionar los comentarios de los usuarios del blog</h2>

                <!-- FILTROS -->
                <form method="GET" class="filtros">
                    <input type="text" name="nombre" placeholder="Filtrar por nombre" value="<?= htmlspecialchars($filtro_nombre) ?>">
                    <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">

                    <select name="estado">
                        <option value="">-- Estado --</option>
                        <option value="visible" <?= $filtro_estado === 'visible' ? 'selected' : '' ?>>Aprobado</option>
                        <option value="oculto" <?= $filtro_estado === 'oculto' ? 'selected' : '' ?>>Oculto</option>
                    </select>

                    <button type="submit">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                    <button type="button" onclick="window.location='blog_comentarios.php'">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                    </button>
                </form>

                <!-- LISTADO -->
                <table class="tabla-admin">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Artículo</th>
                            <th>Nombre</th>
                            <th>Comentario</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($comentarios as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= $c['articulo_id'] ?></td>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><?= htmlspecialchars($c['contenido']) ?></td>
                            <td><?= $c['fecha_creacion'] ?></td>
                            <td><?= $c['estado'] ?></td>
                            <td>
                                <?php if ($c['estado'] === 'oculto'): ?>
                                    <button class="btn btn-success"
                                            onclick="window.location='?accion=aprobar&id=<?= $c['id'] ?>'">
                                        <i class="fa-solid fa-check-circle"></i> Aprobar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-warning"
                                            onclick="window.location='?accion=ocultar&id=<?= $c['id'] ?>'">
                                        <i class="fa-solid fa-eye-slash"></i> Ocultar
                                    </button>
                                <?php endif; ?>

                                <button class="btn delete-user"
                                        onclick="if(confirm('¿Seguro que deseas eliminar este comentario?')) window.location='?accion=eliminar&id=<?= $c['id'] ?>'">
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