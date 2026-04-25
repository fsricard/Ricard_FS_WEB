            <main class="layout-home">

                <section class="destacados">

                    <?php
                        $stmt = $pdo->query("
                            SELECT contenido, actualizado
                            FROM sobre_mi
                            ORDER BY id DESC
                            LIMIT 1
                        ");

                        $sobreMi = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <article class="destacado-block sobre-mi">
                        <h2 class="destacado-title">
                            <i class="fa-solid fa-user"></i> Sobre el Diablillo
                        </h2>

                        <div class="sobre-mi-preview">
                            <div class="sobre-mi-texto destacado-content">
                                <?= $sobreMi['contenido'] ?>
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