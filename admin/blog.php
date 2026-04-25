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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo      = trim($_POST['titulo']);
    $descripcion = $_POST['descripcion'];
    $categoriasSeleccionadas = $_POST['categorias'] ?? []; // AHORA ES ARRAY
    $usuario_id  = $_SESSION['usuario_id'];

    // Validación básica
    if (!empty($titulo) && !empty($descripcion) && !empty($categoriasSeleccionadas)) {

        // Generar slug único
        $slug = generarSlugUnico($pdo, $titulo, 'blog_articulos', 'slug');

        // Insertar artículo (sin categoría, porque ahora van en tabla puente)
        $stmt = $pdo->prepare("
            INSERT INTO blog_articulos 
            (titulo, slug, contenido, resumen, fecha_creacion, usuario_id, estado)
            VALUES (:titulo, :slug, :contenido, :resumen, NOW(), :usuario_id, 'borrador')
        ");

        $stmt->execute([
            ':titulo'     => $titulo,
            ':slug'       => $slug,
            ':contenido'  => $descripcion,
            ':resumen'    => substr(strip_tags($descripcion), 0, 300),
            ':usuario_id' => $usuario_id
        ]);

        // ID del artículo recién creado
        $articulo_id = $pdo->lastInsertId();

        // Insertar categorías en tabla puente
        $stmtCat = $pdo->prepare("
            INSERT INTO blog_articulo_categoria (articulo_id, categoria_id)
            VALUES (:articulo_id, :categoria_id)
        ");

        foreach ($categoriasSeleccionadas as $catId) {
            $stmtCat->execute([
                ':articulo_id' => $articulo_id,
                ':categoria_id' => intval($catId)
            ]);
        }

        // Procesar imagen destacada si se subió
        if (!empty($_FILES['imagen']['name'])) {

            $uploadDir = __DIR__ . '/../uploads/blog/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $nombreOriginal = basename($_FILES['imagen']['name']);
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreFinal = $articulo_id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($nombreOriginal, PATHINFO_FILENAME)) . '.' . $extension;
            $rutaFinal = $uploadDir . $nombreFinal;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                // Guardar nombre en la BD
                $stmtImg = $pdo->prepare("UPDATE blog_articulos SET imagen_destacada = :imagen WHERE id = :id");
                $stmtImg->execute([
                    ':imagen' => $nombreFinal,
                    ':id'     => $articulo_id
                ]);
            }
        }

        registrarLog("Artículo creado: {$titulo} (ID {$articulo_id})", 'INFO', $usuario_id, 'admin/blog.php');
        header("Location: blog.php?msg=creado");
        exit;

    } else {
        $error = "Debes completar título, contenido y al menos una categoría.";
    }
}

// Recuperar categorías
$stmtCat = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$pagina = 'blog';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Nuevo artículo</h2>

            <?php
                if (!empty($error)):
                    echo mostrarAlerta($error, 'warning');
                endif;
            ?>

            <form method="post" action="blog.php" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" required>
                </div>

                <!-- CAMBIO: selección múltiple -->
                <div class="form-group">
                    <label for="categoria">Categorías</label>
                    <select name="categorias[]" id="categoria" class="form-control" multiple required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>Pulsa CTRL o CMD para seleccionar varias</small>
                </div>

                <div class="form-group">
                    <label for="editor-descripcion">Contenido</label>
                    <div id="editor-descripcion" class="quill-editor"></div>
                    <textarea id="descripcion" name="descripcion" style="display:none;"></textarea>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen destacada</label>
                    <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
                </div>

                <button type="submit" id="btn-guardar" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>

            </form>
        </div>
    </section>
</main>

<?php include('includes/footer.php');