<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

$porPagina = esSoloMovil() ? 3 : 5;
$pagina = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($pagina - 1) * $porPagina;

// Total de artículos
$stmtTotal = $pdo->query("
    SELECT COUNT(*) AS total
    FROM blog_articulos
    WHERE estado = 'publicado'
");
$total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($total / $porPagina);

// Artículos paginados
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.titulo,
        a.contenido,
        a.fecha_creacion,
        a.slug,
        a.imagen_destacada,
        u.nombre AS autor
    FROM blog_articulos a
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'publicado'
    ORDER BY a.fecha_creacion DESC
    LIMIT :offset, :limit
");

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
$stmt->execute();

$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="resultado-articulos">
<?php foreach ($articulos as $articulo): ?>
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
</div>

<div id="resultado-paginacion">
    <?php if ($totalPaginas > 1): ?>
        <div class="paginacion-botones">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="#" class="pagina-link <?= $i == $pagina ? 'activa' : '' ?>" data-page="<?= $i ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>