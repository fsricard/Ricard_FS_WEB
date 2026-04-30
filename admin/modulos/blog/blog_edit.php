<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Obtener ID del artículo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cargar artículo
$stmt = $pdo->prepare("SELECT * FROM blog_articulos WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$articulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$articulo) {
    die("Artículo no encontrado");
}

// Cargar categorías asignadas al artículo
$stmtCatArt = $pdo->prepare("
    SELECT categoria_id 
    FROM blog_articulo_categoria 
    WHERE articulo_id = :id
");
$stmtCatArt->execute([':id' => $id]);
$categoriasAsignadas = array_column($stmtCatArt->fetchAll(PDO::FETCH_ASSOC), 'categoria_id');

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = $_POST['descripcion'] ?? '';
    $estado      = $_POST['estado'] ?? 'borrador';
    $categoriasSeleccionadas = $_POST['categorias'] ?? [];

    // Regenerar slug si cambia el título
    $slugArticulo = $articulo['slug'];
    if ($titulo !== $articulo['titulo']) {
        $slugArticulo = generarSlug($titulo);
    }

    // Imagen destacada
    $imagen_destacada = $articulo['imagen_destacada'];

    if (!empty($_FILES['imagen']['name'])) {
        $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
        $rutaDestino = __DIR__ . '/../../../uploads/blog/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen_destacada = $nombreArchivo;

            // Borrar imagen anterior
            if (!empty($articulo['imagen_destacada'])) {
                @unlink(__DIR__ . '/../../../uploads/blog/' . $articulo['imagen_destacada']);
            }
        }
    }

    // Resumen automático
    $resumen = mb_substr(strip_tags($descripcion), 0, 200);

    // Actualizar artículo
    $stmtUpdate = $pdo->prepare("
        UPDATE blog_articulos
        SET titulo = :titulo,
            slug = :slug,
            contenido = :contenido,
            resumen = :resumen,
            estado = :estado,
            imagen_destacada = :imagen_destacada,
            fecha_actualizacion = NOW()
        WHERE id = :id
    ");

    $stmtUpdate->execute([
        ':titulo'           => $titulo,
        ':slug'             => $slugArticulo,
        ':contenido'        => $descripcion,
        ':resumen'          => $resumen,
        ':estado'           => $estado,
        ':imagen_destacada' => $imagen_destacada,
        ':id'               => $id
    ]);

    // Actualizar categorías (tabla puente)
    // 1. Borrar las actuales
    $pdo->prepare("DELETE FROM blog_articulo_categoria WHERE articulo_id = :id")
        ->execute([':id' => $id]);

    // 2. Insertar las nuevas
    $stmtInsertCat = $pdo->prepare("
        INSERT INTO blog_articulo_categoria (articulo_id, categoria_id)
        VALUES (:articulo_id, :categoria_id)
    ");

    foreach ($categoriasSeleccionadas as $catId) {
        $stmtInsertCat->execute([
            ':articulo_id' => $id,
            ':categoria_id' => intval($catId)
        ]);
    }

    // Redirigir con mensaje de éxito
    header("Location: blog_edit.php?id=$id&edit=ok");
    exit;
}

// Cargar categorías
$stmtCat = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Header
$pagina = 'articulos';
include('../../includes/header.php');

// Mensaje de éxito
if (isset($_GET['edit']) && $_GET['edit'] === 'ok') {
    echo '<div class="alert alert-success" style="margin: 20px 0; padding: 12px; border-radius: 6px; background: #d4edda; color: #155724;">
            <i class="fa-solid fa-circle-check"></i> Artículo actualizado correctamente
          </div>';
}

// Partial
include __DIR__ . "/includes/partials/articulos_editar.php";

// Footer
include('../../includes/footer.php');
