<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

// ---------------------------------------------------------
// ACCIONES: activar, ocultar, eliminar
// ---------------------------------------------------------
if (isset($_GET['accion'], $_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['accion'] === 'activar') {
        $stmt = $pdo->prepare("UPDATE loco_frases SET activo = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $mensaje = mostrarAlerta('Frase activada correctamente.', 'success');
    }

    if ($_GET['accion'] === 'ocultar') {
        $stmt = $pdo->prepare("UPDATE loco_frases SET activo = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $mensaje = mostrarAlerta('Frase ocultada.', 'warning');
    }

    if ($_GET['accion'] === 'eliminar') {
        $stmt = $pdo->prepare("DELETE FROM loco_frases WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $mensaje = mostrarAlerta('Frase eliminada definitivamente.', 'warning');
    }
}

// ---------------------------------------------------------
// FILTROS
// ---------------------------------------------------------
$filtro_estado = $_GET['estado'] ?? '';
$filtro_desde  = $_GET['desde'] ?? '';
$filtro_hasta  = $_GET['hasta'] ?? '';

$where = [];
$params = [];

// Estado
if ($filtro_estado !== '' && ($filtro_estado === '0' || $filtro_estado === '1')) {
    $where[] = "activo = :estado";
    $params[':estado'] = $filtro_estado;
}

// Fecha desde
if (!empty($filtro_desde)) {
    $where[] = "creado_en >= :desde";
    $params[':desde'] = $filtro_desde . " 00:00:00";
}

// Fecha hasta
if (!empty($filtro_hasta)) {
    $where[] = "creado_en <= :hasta";
    $params[':hasta'] = $filtro_hasta . " 23:59:59";
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ---------------------------------------------------------
// PAGINACIÓN
// ---------------------------------------------------------
$por_pagina = 10;
$pagina_actual = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Total de registros
$stmt = $pdo->prepare("SELECT COUNT(*) FROM loco_frases $where_sql");
$stmt->execute($params);
$total_registros = $stmt->fetchColumn();

$total_paginas = ceil($total_registros / $por_pagina);

// Obtener frases
$sql = "SELECT * FROM loco_frases $where_sql ORDER BY creado_en DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);

$stmt->execute();
$frases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pagina = 'loco_dice_listado';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Gestión de las frases del "Loco dice"</h2>

            <?= $mensaje ?>

            <!-- FILTROS -->
            <form method="get" class="filtros">
                <div class="fila">
                    <div>
                        <label>Desde:</label>
                        <input type="date" name="desde" value="<?= htmlspecialchars($filtro_desde) ?>">
                    </div>

                    <div>
                        <label>Hasta:</label>
                        <input type="date" name="hasta" value="<?= htmlspecialchars($filtro_hasta) ?>">
                    </div>

                    <div>
                        <label>Estado:</label>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="1" <?= $filtro_estado === '1' ? 'selected' : '' ?>>Activos</option>
                            <option value="0" <?= $filtro_estado === '0' ? 'selected' : '' ?>>Ocultos</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit">
                            <i class="fa-solid fa-filter"></i> Filtrar
                        </button>
                        <button type="button" onclick="window.location='loco_dice_listado.php'">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            </form>

            <!-- LISTADO -->
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Frase</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($frases as $f): ?>
                        <tr>
                            <td><?= $f['id'] ?></td>
                            <td><?= $f['frase'] ?></td>
                            <td><?= $f['creado_en'] ?></td>
                            <td>
                                <?= $f['activo'] ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-warning">Oculto</span>' ?>
                            </td>
                            <td>
                                <?php if ($f['activo']): ?>
                                    <button class="btn btn-warning"
                                            onclick="window.location='?accion=ocultar&id=<?= $f['id'] ?>'">
                                        <i class="fa-solid fa-eye-slash"></i> Ocultar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-success"
                                            onclick="window.location='?accion=activar&id=<?= $f['id'] ?>'">
                                        <i class="fa-solid fa-check-circle"></i> Aprobar
                                    </button>
                                <?php endif; ?>

                                <button class="btn delete-user"
                                        onclick="if(confirm('¿Eliminar frase definitivamente?')) window.location='?accion=eliminar&id=<?= $f['id'] ?>'">
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
                echo paginador($total_registros, $por_pagina, $pagina_actual, $_GET, 'p');
            ?>

        </div>
    </section>
</main>

<?php include('includes/footer.php'); ?>