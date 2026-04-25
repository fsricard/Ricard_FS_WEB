                    <main class="layout-home">

                        <section class="destacados">

                            <article class="destacado-block articulo-detalle">

                                <?php
                                    // El router te pasa el slug, por ejemplo: articulo/mi-articulo
                                    $slug = $slug ?? null;

                                    $stmt = $pdo->prepare("
                                        SELECT id, titulo, contenido, autor, fecha_creacion, categoria_id, fecha_actualizacion, imagen_destacada
                                        FROM blog_articulos
                                        WHERE slug = :slug
                                        LIMIT 1
                                    ");
                                    $stmt->execute(['slug' => $slug]);
                                    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if (!$articulo) {
                                        echo "<p>Artículo no encontrado.</p>";
                                        return;
                                    }

                                    $articulo['fecha_formateada'] = formatearFecha($articulo['fecha_creacion']);
                                ?>

                                <!-- TÍTULO -->
                                <h2 class="destacado-title">
                                    <?= htmlspecialchars($articulo['titulo']) ?>
                                </h2>

                                <!-- IMAGEN DESTACADA -->
                                <?php if (!empty($articulo['imagen_destacada'])): ?>
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
                                <?php endif; ?>

                                <!-- CONTENIDO -->
                                <div class="destacado-content articulo-enlace">
                                    <?= $articulo['contenido'] ?>
                                </div>

                                <!-- META -->
                                <p class="articulo-meta">
                                    <i class="fa-duotone fa-user"></i>
                                    Escrito por <span><?= htmlspecialchars($articulo['autor']) ?></span>
                                    el <span><?= $articulo['fecha_formateada'] ?></span>
                                </p>

                                <?= mostrarCategoriasArticulo($pdo, $articulo['id']) ?>

                            </article>

                            <?php include('includes/articulos_relacionados.php'); ?>

                            <?php include('includes/comentarios.php'); ?>

                        </section>

                        <?php
                            if (!esSoloMovil()){
                                include('includes/aside.php');
                            }
                        ?>

                    </main>