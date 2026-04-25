<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Procesar creación de categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $nombre = trim($_POST['nombre']);
    $slug = trim($_POST['slug']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre) && !empty($slug)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, slug, descripcion) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $slug, $descripcion]);
    }
}

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener listado
$resultado = $pdo->query("SELECT * FROM categorias ORDER BY fecha_creacion DESC");

$pagina = 'categorias';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Nueva categoría</h2>

            <form method="post" action="categorias.php">
                <input type="hidden" name="accion" value="crear">

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="editor-descripcion">Descripción</label>
                    <div id="editor-descripcion" class="quill-editor"></div>
                    <textarea id="descripcion" name="descripcion" style="display:none;"></textarea>
                </div>

                <button type="submit" id="btn-guardar" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>

            <hr>

            <h2>Listado de categorías</h2>

            <table class="tabla-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = $resultado->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['nombre']) ?></td>
                            <td><?= htmlspecialchars($cat['slug']) ?></td>
                            <td><?= $cat['fecha_creacion'] ?></td>
                            <td class="acciones">
                                <form action="categorias_editar.php" method="get" class="btn-base">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button class="btn update-user">
                                        <i class="fa-solid fa-pen-to-square"></i> Editar
                                    </button>
                                </form>

                                <form action="categorias.php" method="get" class="btn-base"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría?')">
                                    <input type="hidden" name="eliminar" value="<?= $cat['id'] ?>">
                                    <button class="delete-user">
                                        <i class="fa-solid fa-skull-crossbones"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </section>
</main>

<script>
    // Script para validar el Slug
    document.addEventListener("DOMContentLoaded", function () {
        const slugInput = document.getElementById("slug");
        const aviso = document.createElement("div");
        aviso.style.marginTop = "5px";
        slugInput.parentNode.appendChild(aviso);

        slugInput.addEventListener("input", function () {
            const slug = slugInput.value.trim();
            const id = new URLSearchParams(window.location.search).get("id") || "";

            if (slug.length < 2) {
                aviso.textContent = "";
                return;
            }

            fetch(`ajax/validar_slug_categoria.php?slug=${encodeURIComponent(slug)}&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.existe) {
                        aviso.textContent = "⚠️ Este slug ya está en uso";
                        aviso.style.color = "var(--color-danger)";
                        slugInput.style.borderColor = "var(--color-danger)";
                    } else {
                        aviso.textContent = "✔ Slug disponible";
                        aviso.style.color = "var(--color-success)";
                        slugInput.style.borderColor = "var(--color-success)";
                    }
                });
        });
    });
</script>

<?php include('includes/footer.php'); ?>