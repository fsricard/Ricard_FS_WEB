<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../../config/database.php';

header('Content-Type: application/json');

// Proteger acceso
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['table' => '<p>Acceso denegado.</p>', 'pagination' => '']);
    exit;
}

// Parámetros de paginación
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Total de usuarios
$totalStmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Usuarios de la página actual
$stmt = $pdo->prepare("SELECT id, nombre, correo, rol, creado_en 
                       FROM usuarios 
                       ORDER BY creado_en DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll();

// Renderizar tabla con edición inline
$table = '<table border="1" cellpadding="6">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Acciones</th>
</tr>';

foreach ($usuarios as $u) {
    $table .= "<tr>
        <td>{$u['id']}</td>
        <td><input type='text' value='".htmlspecialchars($u['nombre'])."' data-id='{$u['id']}' class='edit-nombre'></td>
        <td><input type='email' value='".htmlspecialchars($u['correo'])."' data-id='{$u['id']}' class='edit-correo'></td>
        <td>
            <select data-id='{$u['id']}' class='edit-rol'>
                <option value='admin' ".($u['rol']==='admin'?'selected':'').">Admin</option>
                <option value='visitante' ".($u['rol']==='visitante'?'selected':'').">Visitante</option>
            </select>
        </td>
        <td>
            <button class='btn update-user' data-id='{$u['id']}'>
                <i class='fa-solid fa-pen-to-square'></i> Actualizar
            </button>
            <button class='btn delete-user' data-id='{$u['id']}'>
                <i class='fa-solid fa-skull-crossbones'></i> Eliminar
            </button>
        </td>
    </tr>";
}
$table .= '</table>';

// Renderizar paginación
$pagination = '';
for ($i = 1; $i <= $totalPages; $i++) {
    $pagination .= "<a href='#' class='page-link' data-page='{$i}'>{$i}</a> ";
}

// Devolver JSON
echo json_encode([
    'table' => $table,
    'pagination' => $pagination
]);