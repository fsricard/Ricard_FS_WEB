<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
if ($cat_id <= 0) {
    echo "Categoría no válida.";
    exit;
}

$porPagina = 3;
$pagina = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($pagina - 1) * $porPagina;

// Total de artículos en esta categoría
$stmtTotal = $pdo->prepare("
    SELECT COUNT(DISTINCT a.id) AS total
    FROM blog_articulos a
    INNER JOIN blog_articulo_categoria ac ON ac.articulo_id = a.id
    WHERE a.estado = 'publicado'
      AND ac.categoria_id = :cat_id
");
$stmtTotal->execute(['cat_id' => $cat_id]);
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
        u.nombre AS autor
    FROM blog_articulos a
    INNER JOIN blog_articulo_categoria ac ON ac.articulo_id = a.id
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.estado = 'publicado'
      AND ac.categoria_id = :cat_id
    ORDER BY a.fecha_creacion DESC
    LIMIT :offset, :limit
");

$stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
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

        <p class="blog-preview-extracto">
            <?= $extracto ?>
        </p>

        <p class="blog-preview-meta">
            <i class="fa-duotone fa-user"></i>
            Escrito por <span><?= htmlspecialchars($articulo['autor']) ?></span>
            el <span><?= $fecha ?></span>
        </p>

        <!-- CATEGORÍAS INFERNALES -->
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

            <?php if ($pagina > 1): ?>
                <a href="#" class="pagina-link" data-page="<?= $pagina - 1 ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="#" class="pagina-link <?= $i == $pagina ? 'activa' : '' ?>"
                   data-page="<?= $i ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($pagina < $totalPaginas): ?>
                <a href="#" class="pagina-link" data-page="<?= $pagina + 1 ?>">Siguiente</a>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>