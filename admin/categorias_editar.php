<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id = intval($_GET['id']);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $nombre = trim($_POST['nombre']);
    $slug = trim($_POST['slug']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre) && !empty($slug)) {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, slug = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$nombre, $slug, $descripcion, $id]);

        header("Location: categorias_editar.php?id=" . $id . "&editado=1");
        exit;
    }
}

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header("Location: categorias.php");
    exit;
}

$pagina = 'categorias_editar';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Editar categoría</h2>

            <?php
                if (isset($_GET['editado']) && $_GET['editado'] == 1) {
                    echo mostrarAlerta('Cambios guardados correctamente', 'success');
                }
            ?>

            <form method="post" action="categorias_editar.php?id=<?= $id ?>">
                <input type="hidden" name="accion" value="actualizar">

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input 
                        type="text" 
                        name="nombre" 
                        id="nombre" 
                        class="form-control" 
                        value="<?= htmlspecialchars($categoria['nombre']) ?>" 
                        required>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="slug" 
                        class="form-control" 
                        value="<?= htmlspecialchars($categoria['slug']) ?>" 
                        required>
                </div>

                <div class="form-group">
                    <label for="editor-descripcion">Descripción</label>

                    <!-- Editor visual -->
                    <div id="editor-descripcion" class="quill-editor">
                        <?= $categoria['descripcion'] ?>
                    </div>

                    <!-- Campo oculto donde guardamos el HTML final -->
                    <textarea id="descripcion" name="descripcion" style="display:none;">
                        <?= htmlspecialchars($categoria['descripcion']) ?>
                    </textarea>
                </div>

                <button type="submit" id="btn-guardar" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>

                <!-- Botón volver -->
                <a href="categorias.php" class="btn btn-volver" style="margin-bottom: 15px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </form>

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

<?php include('includes/footer.php');