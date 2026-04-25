<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

$porPagina = esSoloMovil() ? 3 : 5;
$pagina = 1;
$offset = 0;

// Total de artículos
$stmtTotal = $pdo->query("
    SELECT COUNT(*) AS total
    FROM blog_articulos
    WHERE estado = 'publicado'
");
$total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($total / $porPagina);

// Artículos iniciales
$stmt = $pdo->prepare("
    SELECT id, titulo, contenido, autor, fecha_creacion, slug, imagen_destacada
    FROM blog_articulos
    WHERE estado = 'publicado'
    ORDER BY fecha_creacion DESC
    LIMIT :offset, :limit
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->execute();
$articulosIniciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="layout-home">

    <section class="destacados">

        <article class="destacado-block blog">
            <h2 class="destacado-title">
                <i class="fa-solid fa-pen-nib"></i> Todas mis locuras
            </h2>

            <div class="destacado-content" id="blog-listado">

                <?php foreach ($articulosIniciales as $articulo): ?>
                    <?php
                        $extracto = generarExtracto($articulo['contenido']);
                        $fecha = formatearFecha($articulo['fecha_creacion']);
                    ?>

                    <div class="blog-preview" style="margin: 30px 0;">
                        <h3 class="blog-preview-title">
                            <?= htmlspecialchars($articulo['titulo']) ?>
                        </h3>

                        <!-- IMAGEN DESTACADA -->
                        <?php
                            $img = $articulo['imagen_destacada'];

                            // Si empieza por http, es una URL externa (Pexels)
                            if (preg_match('/^https?:\/\//i', $img)) {
                                $imgSrc = $img;
                            } else {
                                // Imagen local subida manualmente
                                $imgSrc = asset('/uploads/blog/' . $img);
                            }
                        ?>
                        <div class="articulo-imagen">
                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($articulo['titulo']) ?>">
                        </div>

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

            <div id="blog-paginacion" class="paginacion">
                <!-- Aquí AJAX cargará los botones -->
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
    // Script para cargar el listado de artículos
    document.addEventListener("DOMContentLoaded", function() {
        cargarPagina(1);
    });

    function cargarPagina(pagina) {
        fetch("<?= asset('/ajax/blog_listado.php?page=') ?>" + pagina)
            .then(res => res.text())
            .then(html => {
                console.log("HTML recibido:", html); // DEBUG

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                document.querySelector("#blog-listado").innerHTML =
                    doc.querySelector("#resultado-articulos").innerHTML;

                document.querySelector("#blog-paginacion").innerHTML =
                    doc.querySelector("#resultado-paginacion").innerHTML;

                document.querySelectorAll(".pagina-link").forEach(btn => {
                    btn.addEventListener("click", function(e) {
                        e.preventDefault();
                        cargarPagina(this.dataset.page);
                    });
                });
            });
    }
</script>