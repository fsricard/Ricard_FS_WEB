<?php
require_once __DIR__ . '/../../../../config/database.php';

$limite = date('Y-m-d H:i:s', strtotime('-30 days'));

// 1. Obtener artículos a eliminar definitivamente
$stmt = $pdo->prepare("SELECT * FROM blog_papelera WHERE fecha_eliminacion < :limite");
$stmt->execute([':limite' => $limite]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($items as $item) {

    // Borrar imagen
    if (!empty($item['imagen_destacada'])) {
        $ruta = __DIR__ . '/../../../../uploads/papelera/' . $item['imagen_destacada'];
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }

    // Borrar registro
    $stmtDel = $pdo->prepare("DELETE FROM blog_papelera WHERE id = :id");
    $stmtDel->execute([':id' => $item['id']]);
}
