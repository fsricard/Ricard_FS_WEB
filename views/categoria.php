<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

$slug = $_GET['slug'] ?? null;

// Obtener categoría por slug
$stmtCat = $pdo->prepare("SELECT id, nombre, descripcion FROM categorias WHERE slug = :slug LIMIT 1");
$stmtCat->execute(['slug' => $slug]);
$categoria = $stmtCat->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    echo "<p>La categoría no existe.</p>";
    exit;
}

$porPagina = 3;
$pagina = 1;
$offset = 0;

// Total de artículos en esta categoría
$stmtTotal = $pdo->prepare("
    SELECT COUNT(DISTINCT a.id) AS total
    FROM blog_articulos a
    INNER JOIN blog_articulo_categoria ac ON ac.articulo_id = a.id
    WHERE a.estado = 'publicado'
      AND ac.categoria_id = :cat_id
");
$stmtTotal->execute(['cat_id' => $categoria['id']]);
$total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($total / $porPagina);

// Artículos iniciales
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.titulo,
        a.contenido,
        a.fecha_creacion,
        a.slug,
        u.nombre AS autor
    FROM blog_articulos a
    INNER JOIN blog_articulo_categoria ac ON ac.articulo_id = a.id
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'publicado'
      AND ac.categoria_id = :cat_id
    ORDER BY a.fecha_creacion DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':cat_id', $categoria['id'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->execute();
$articulosIniciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="layout-home">

    <section class="destacados">

        <article class="destacado-block">
            <h2 class="destacado-title">
                <i class="fa-solid fa-tags"></i> 
                <?= htmlspecialchars($categoria['nombre']) ?>
            </h2>

            <div class="destacado-content" data-catid="<?= $categoria['id'] ?>">

                <?php foreach ($articulosIniciales as $articulo): ?>
                    <?php
                        $extracto = generarExtracto($articulo['contenido']);
                        $fecha = formatearFecha($articulo['fecha_creacion']);
                    ?>

                    <div class="blog-preview" style="margin: 30px 0;">
                        <h3 class="blog-preview-title">
                            <?= htmlspecialchars($articulo['titulo']) ?>
                        </h3>

                        <p class="blog-preview-extracto">
                            <?= $extracto ?>
                        </p>

                        <p class="blog-preview-meta">
                            <i class="fa-duotone fa-user"></i>
                            Escrito por <span><?= htmlspecialchars($articulo['autor']) ?></span>
                            el <span><?= $fecha ?></span>
                        </p>

                        <?= mostrarCategoriasArticulo($pdo, $articulo['id']) ?>

                        <a href="<?= asset('/articulo/' . $articulo['slug']) ?>" class="blog-preview-link">
                            Seguir leyendo <i class="fa-solid fa-arrow-right-long"></i>
                        </a>
                    </div>

                    <hr>
                <?php endforeach; ?>

                <!-- PAGINACIÓN ESTÁTICA -->
                <?php if ($totalPaginas > 1): ?>
                    <div class="paginacion-botones">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a href="#" class="pagina-link <?= $i == 1 ? 'activa' : '' ?>" data-page="<?= $i ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            </div>

        </article>

    </section>

    <?php
        if (!esSoloMovil()){
            include('includes/aside.php');
        }
    ?>

</main>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function cargarArticulos(page = 1) {
        const contenedor = document.querySelector(".destacado-content");
        const catId = contenedor.dataset.catid;

        fetch(`/ajax/blog_listado_categoria.php?cat_id=${catId}&page=${page}`)
            .then(res => res.text())
            .then(html => {
                contenedor.innerHTML = html;

                // Reasignar eventos a los nuevos botones
                document.querySelectorAll(".pagina-link").forEach(btn => {
                    btn.addEventListener("click", function (e) {
                        e.preventDefault();
                        cargarArticulos(this.dataset.page);
                    });
                });
            });
    }

    // Cargar la primera página
    cargarArticulos();
});
</script>