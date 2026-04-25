                <!-- Bloque categorías -->
                <?php
                    $stmt = $pdo->query("
                        SELECT nombre, slug
                        FROM categorias
                        ORDER BY nombre ASC
                    ");

                    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <section class="sidebar-block categorias">
                    <h3 class="sidebar-title">
                        <i class="fa-solid fa-tags"></i> Categorías
                    </h3>

                    <ul class="sidebar-list categorias-list">
                        <?php foreach ($categorias as $cat): ?>
                            <li>
                                <a href="<?= asset('/categoria/' . $cat['slug']) ?>">
                                    <i class="fa-solid fa-chevron-right"></i>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>