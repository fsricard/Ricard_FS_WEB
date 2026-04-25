<?php
// Seguridad: si no existe $articulo, no hacemos nada
if (!isset($articulo['id'])) {
    return;
}

$articulo_id = $articulo['id'];

// Obtener todas las categorías del artículo actual
$stmt = $pdo->prepare("
    SELECT categoria_id
    FROM blog_articulo_categoria
    WHERE articulo_id = :articulo_id
");
$stmt->execute([':articulo_id' => $articulo_id]);
$categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Si no tiene categorías, no hay relacionados
if (empty($categorias)) {
    $relacionados = [];
} else {

    // Crear placeholders dinámicos para el IN()
    $placeholders = implode(',', array_fill(0, count($categorias), '?'));

    // Buscar artículos relacionados por coincidencias de categorías (sin LIMIT)
    $sql = "
        SELECT 
            a.id, 
            a.titulo, 
            a.slug, 
            a.imagen_destacada,
            COUNT(*) AS coincidencias
        FROM blog_articulos a
        INNER JOIN blog_articulo_categoria ac 
            ON ac.articulo_id = a.id
        WHERE ac.categoria_id IN ($placeholders)
          AND a.id != ?
          AND a.estado = 'publicado'
        GROUP BY a.id
        ORDER BY coincidencias DESC, a.fecha_creacion DESC
    ";

    $stmt = $pdo->prepare($sql);

    // Parámetros: primero categorías, luego el ID del artículo actual
    $params = array_merge($categorias, [$articulo_id]);

    $stmt->execute($params);
    $relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔥 Aleatorizar resultados manteniendo relevancia
    if (!empty($relacionados)) {
        shuffle($relacionados);          // Mezcla aleatoria
        $relacionados = array_slice($relacionados, 0, 3); // Solo 3
    }
}
?>

<section class="articulos-relacionados">

    <h3 class="articulos-relacionados-title">
        <i class="fa-solid fa-pen-nib"></i>
        Artículos relacionados
    </h3>

    <div class="relacionados-grid">
        <?php if (!empty($relacionados)): ?>
            <?php foreach ($relacionados as $rel): ?>
                <a href="/articulo/<?php echo $rel['slug']; ?>" class="relacionado-item">

                    <div class="relacionado-img">
                        <?php
                            $img = $rel['imagen_destacada'];

                            // Si empieza por http, es una URL externa (Pexels)
                            if (preg_match('/^https?:\/\//i', $img)) {
                                $imgSrc = $img;
                            } else {
                                // Imagen local subida manualmente
                                $imgSrc = asset('/uploads/blog/' . $img);
                            }
                        ?>
                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($articulo['titulo']) ?>">
                    </div>

                    <h4 class="relacionado-titulo">
                        <?php echo htmlspecialchars($rel['titulo']); ?>
                    </h4>

                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-relacionados">No hay artículos relacionados todavía.</p>
        <?php endif; ?>
    </div>

</section>