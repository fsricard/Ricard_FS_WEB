<?php
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$titulo    = $_POST['titulo'] ?? '';
$usuario   = $_POST['usuario'] ?? '';
$fecha     = $_POST['fecha'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$pagina    = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;

$porPagina = 5;
$offset = ($pagina - 1) * $porPagina;

// Base SQL
$sqlBase = "
    FROM blog_articulos a
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    LEFT JOIN blog_articulo_categoria ac ON ac.articulo_id = a.id
    LEFT JOIN categorias c ON c.id = ac.categoria_id
    WHERE 1=1
";

$params = [];

// Filtros
if (!empty($titulo)) {
    $sqlBase .= " AND a.titulo LIKE :titulo";
    $params[':titulo'] = "%$titulo%";
}
if (!empty($usuario)) {
    $sqlBase .= " AND u.nombre LIKE :usuario";
    $params[':usuario'] = "%$usuario%";
}
if (!empty($fecha)) {
    $sqlBase .= " AND DATE(a.fecha_creacion) = :fecha";
    $params[':fecha'] = $fecha;
}
if (!empty($categoria)) {
    $sqlBase .= " AND c.id = :categoria";
    $params[':categoria'] = $categoria;
}

// Total registros
$stmtTotal = $pdo->prepare("SELECT COUNT(DISTINCT a.id) " . $sqlBase);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);

// Consulta final
$sqlFinal = "
    SELECT 
        a.id,
        a.titulo,
        a.fecha_creacion,
        a.estado,
        a.imagen_destacada,
        u.nombre AS autor,
        GROUP_CONCAT(c.nombre ORDER BY c.nombre SEPARATOR ', ') AS categorias
    " . $sqlBase . "
    GROUP BY a.id
    ORDER BY a.fecha_creacion DESC
    LIMIT $offset, $porPagina
";

$stmt = $pdo->prepare($sqlFinal);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir categorías a array
$articulos = [];
foreach ($rows as $row) {
    $row['categorias'] = $row['categorias']
        ? explode(', ', $row['categorias'])
        : [];
    $articulos[] = $row;
}

echo json_encode([
    'articulos' => $articulos,
    'totalPaginas' => $totalPaginas
]);
