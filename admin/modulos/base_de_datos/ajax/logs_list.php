<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['table' => '<p>Acceso denegado.</p>', 'pagination' => '']);
    exit;
}

$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$file = __DIR__ . '/../../../../logs/session.log';
if (!file_exists($file)) {
    echo json_encode(['table' => '<p>No hay registros.</p>', 'pagination' => '']);
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines = array_reverse($lines); // últimos primero

// Filtros
$filtroUsuario = $_GET['usuario'] ?? '';
$filtroAccion  = $_GET['accion'] ?? '';
$filtroDesde   = $_GET['desde'] ?? '';
$filtroHasta   = $_GET['hasta'] ?? '';

if ($filtroUsuario || $filtroAccion || $filtroDesde || $filtroHasta) {
    $lines = array_filter($lines, function($line) use ($filtroUsuario, $filtroAccion, $filtroDesde, $filtroHasta) {
        // Extraer datos
        preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches);
        if (!$matches) return false;

        $fecha   = $matches[1];
        $ip      = $matches[2];
        $usuario = $matches[3];
        $accion  = $matches[4];

        $match = true;

        if ($filtroUsuario && stripos($usuario, $filtroUsuario) === false) {
            $match = false;
        }
        if ($filtroAccion && $accion !== $filtroAccion) {
            $match = false;
        }
        if ($filtroDesde && $fecha < $filtroDesde." 00:00:00") {
            $match = false;
        }
        if ($filtroHasta && $fecha > $filtroHasta." 23:59:59") {
            $match = false;
        }

        return $match;
    });
    $lines = array_values($lines); // reindexar
}

$total = count($lines);
$totalPages = ceil($total / $perPage);

$offset = ($page - 1) * $perPage;
$pageLines = array_slice($lines, $offset, $perPage);

// Renderizar tabla
$table = '<table>
<tr>
    <th>Fecha</th>
    <th>IP</th>
    <th>Usuario</th>
    <th>Acción</th>
</tr>';

foreach ($pageLines as $line) {
    // Ejemplo: [2025-12-16 16:06:17] [::1] [Diablillo] Login correcto
    preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches);
    if ($matches) {
        $fecha   = $matches[1];
        $ip      = $matches[2];
        $usuario = htmlspecialchars($matches[3]);
        $accion  = $matches[4];
        $table .= "<tr>
            <td>$fecha</td>
            <td>$ip</td>
            <td>$usuario</td>
            <td>$accion</td>
        </tr>";
    }
}
$table .= '</table>';

// Paginación
$pagination = '';
for ($i = 1; $i <= $totalPages; $i++) {
    $pagination .= "<a href='#' class='page-link' data-page='{$i}'>{$i}</a> ";
}

echo json_encode([
    'table' => $table,
    'pagination' => $pagination
]);