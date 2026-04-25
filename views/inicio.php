            <main class="layout-home">

                <section class="destacados">

                    <!-- SOBRE MÍ -->
                    <?php
                        $stmt = $pdo->query("
                            SELECT contenido, actualizado
                            FROM sobre_mi_inicio
                            ORDER BY id DESC
                            LIMIT 1
                        ");

                        $sobreMi = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <article class="destacado-block sobre-mi">
                        <h2 class="destacado-title">
                            <i class="fa-solid fa-user"></i> Sobre mí
                        </h2>

                        <div class="sobre-mi-preview">
                            <div class="sobre-mi-texto">
                                <?= $sobreMi['contenido'] ?>
                            </div>

                            <a href="<?= asset('/sobre-mi') ?>" class="sobre-mi-link">
                                Conóceme mejor <i class="fa-solid fa-arrow-right-long"></i>
                            </a>
                        </div>
                    </article>

                    <!-- BLOG -->
                    <?php
                        $stmt = $pdo->query("
                            SELECT id, titulo, contenido, autor, fecha_creacion, slug
                            FROM blog_articulos
                            WHERE estado = 'publicado'
                            ORDER BY fecha_creacion DESC
                            LIMIT 1
                        ");

                        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
                        $articulo['extracto'] = generarExtracto($articulo['contenido']);
                        $articulo['fecha_formateada'] = formatearFecha($articulo['fecha_creacion']);
                    ?>

                    <article class="destacado-block blog">
                        <h2 class="destacado-title">
                            <i class="fa-solid fa-pen-nib"></i> Último artículo
                        </h2>

                        <div class="blog-preview">

                            <h3 class="blog-preview-title">
                                <?= $articulo['titulo'] ?>
                            </h3>

                            <p class="blog-preview-extracto">
                                <?= $articulo['extracto'] ?>
                            </p>

                            <p class="blog-preview-meta">
                                <i class="fa-duotone fa-user"></i>
                                Escrito por <span><?= $articulo['autor'] ?></span>
                                el <span><?= $articulo['fecha_formateada'] ?></span>
                            </p>

                            <?= mostrarCategoriasArticulo($pdo, $articulo['id']) ?>

                            <a href="<?= asset('/articulo/' . $articulo['slug']) ?>" class="blog-preview-link">
                                Seguir leyendo <i class="fa-solid fa-arrow-right-long"></i>
                            </a>

                        </div>
                    </article>

                    <!-- FRASES -->
                    <article class="destacado-block frases">
                        <h2 class="destacado-title">
                            <i class="fa-solid fa-fire"></i> Frase del día
                        </h2>

                        <div class="frase-dia">
                            <p class="frase-texto">
                                <?= obtenerFraseDelDia(); ?>
                            </p>

                            <div class="frase-icono">
                                <i class="fa-duotone fa-solid fa-face-angry-horns"></i>
                            </div>
                        </div>
                    </article>

                </section>

                <?php
                    if (!esSoloMovil()){
                        include('includes/aside.php');
                    }
                ?>

            </main>