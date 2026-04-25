<?php
if (!isset($articulo['id'])) {
    echo "<p>No se puede cargar el sistema de comentarios.</p>";
    return;
}

$articulo_id = $articulo['id'];

// Obtener todos los comentarios visibles
$stmt = $pdo->prepare("
    SELECT id, nombre, contenido, fecha_creacion, parent_id
    FROM blog_comentarios
    WHERE articulo_id = :id AND estado = 'visible'
    ORDER BY fecha_creacion ASC
");
$stmt->execute(['id' => $articulo_id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir a árbol
function construirArbolComentarios(array $comentarios): array {
    $map = [];
    $raiz = [];

    foreach ($comentarios as $c) {
        $c['hijos'] = [];
        $map[$c['id']] = $c;
    }

    foreach ($map as $id => &$c) {
        if (!empty($c['parent_id']) && isset($map[$c['parent_id']])) {
            $map[$c['parent_id']]['hijos'][] = &$c;
        } else {
            $raiz[] = &$c;
        }
    }

    return $raiz;
}

$comentarios_arbol = construirArbolComentarios($comentarios);

// Render recursivo
function renderComentario(array $c, int $articulo_id): void {
    ?>
    <div class="comentario" data-id="<?= $c['id'] ?>">

        <div class="comentario-contenido">
            <p class="comentario-nombre"><?= htmlspecialchars($c['nombre']) ?></p>
            <p class="comentario-fecha"><?= formatearFecha($c['fecha_creacion']) ?></p>
            
            <?= nl2br(htmlspecialchars($c['contenido'])) ?>
        </div>

        <button class="btn-diablillo" type="button"
                onclick="mostrarFormularioRespuesta(<?= (int)$c['id'] ?>)">
            Responder
        </button>

        <!-- Formulario de respuesta -->
        <form class="form-respuesta" id="form-respuesta-<?= $c['id'] ?>" style="display:none;">
            <input type="hidden" name="articulo_id" value="<?= $articulo_id ?>">
            <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">

            <label>Tu nombre</label>
            <input type="text" name="nombre" required>

            <label>Respuesta</label>
            <textarea name="contenido" rows="3" required></textarea>

            <button type="submit" class="btn-diablillo">
                🔥 Enviar comentario 🔥
            </button>
            <p class="comentario-msg"></p>
        </form>

        <?php if (!empty($c['hijos'])): ?>
            <div class="comentario-hijos">
                <?php foreach ($c['hijos'] as $hijo) renderComentario($hijo, $articulo_id); ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
}
?>

<section class="comentarios">

    <h3 class="comentarios-title">
        <i class="fa-duotone fa-comments"></i>
        Comentarios
    </h3>

    <div id="comentarios-lista">
        <?php if (empty($comentarios_arbol)): ?>
            <p class="sin-comentarios">Sé el primero en comentar.</p>
        <?php else: ?>
            <?php foreach ($comentarios_arbol as $c) renderComentario($c, $articulo_id); ?>
        <?php endif; ?>
    </div>

    <h4>Deja un comentario</h4>

    <form id="form-comentario" class="comentario-form">
        <input type="hidden" name="articulo_id" value="<?= $articulo_id ?>">
        <input type="hidden" name="parent_id" value="">

        <label>Tu nombre</label>
        <input type="text" name="nombre" required>

        <label>Comentario</label>
        <textarea name="contenido" rows="4" required></textarea>

        <button type="submit" class="btn-diablillo">🔥 Enviar comentario 🔥</button>
        <p id="comentario-msg" class="comentario-msg"></p>
    </form>

</section>

<script>
function mostrarFormularioRespuesta(id) {
    document.querySelectorAll(".form-respuesta").forEach(f => f.style.display = "none");
    const form = document.getElementById("form-respuesta-" + id);
    if (form) form.style.display = "block";
}

async function enviarFormulario(form, msgEl) {
    const data = new FormData(form);

    let res;
    try {
        res = await fetch("/ajax/comentarios_guardar.php", {
            method: "POST",
            body: data
        });
    } catch (e) {
        msgEl.textContent = "Error de conexión. Inténtalo de nuevo.";
        msgEl.className = "comentario-msg error";
        return;
    }

    let json;
    try {
        json = await res.json();
    } catch (e) {
        msgEl.textContent = "Respuesta inesperada del servidor.";
        msgEl.className = "comentario-msg error";
        return;
    }

    msgEl.textContent = json.message || "Operación completada.";
    msgEl.className = "comentario-msg " + (json.success ? "ok" : "error");

    if (json.success) {
        form.reset();
        // No recargamos la lista porque el comentario queda en 'oculto' hasta moderación
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const formPrincipal = document.getElementById("form-comentario");
    if (formPrincipal) {
        formPrincipal.addEventListener("submit", function(e) {
            e.preventDefault();
            enviarFormulario(this, document.getElementById("comentario-msg"));
        });
    }

    document.querySelectorAll(".form-respuesta").forEach(form => {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            const msg = this.querySelector(".comentario-msg");
            enviarFormulario(this, msg);
        });
    });
});
</script>