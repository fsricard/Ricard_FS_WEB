                    <!-- Ultimos post -->
                    <?php
                        $stmt = $pdo->prepare("
                            SELECT titulo, slug, autor, imagen_destacada
                            FROM blog_articulos
                            WHERE estado = 'publicado'
                            ORDER BY fecha_creacion DESC
                            LIMIT 4
                        ");
                        $stmt->execute();

                        $ultimosPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <section class="sidebar-block ultimos-post">
                        <h3 class="sidebar-title">
                            <i class="fa-solid fa-clock-rotate-left"></i> Últimos posts
                        </h3>

                        <ul class="ultimos-post-list">
                            <?php foreach ($ultimosPosts as $post): ?>
                                <li class="ultimo-post-item">

                                    <a href="<?= asset('/articulo/' . $post['slug']) ?>" class="ultimo-post-link">

                                        <div class="ultimo-post-img">
                                            <?php
                                                $img = $post['imagen_destacada'];

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

                                        <div class="ultimo-post-info">
                                            <h4><?= htmlspecialchars($post['titulo']) ?></h4>
                                            <span class="leer-mas">
                                                Leer más <i class="fa-solid fa-arrow-right-long"></i>
                                            </span>
                                        </div>

                                    </a>

                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>