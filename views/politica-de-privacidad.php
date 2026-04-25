            <main class="layout-home">

                <section class="destacados">

                    <?php
                        $stmt = $pdo->query("
                            SELECT contenido, actualizado
                            FROM politica_privacidad
                            ORDER BY id DESC
                            LIMIT 1
                        ");

                        $politica = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <article class="destacado-block">
                        <h2 class="destacado-title">
                            <i class="fa-solid fa-user-secret"></i> Aviso legal:
                        </h2>

                        <div class="destacado-content">
                            <?= $politica['contenido'] ?>
                        </div>

                    </article>

                </section>

                <?php
                    if (!esSoloMovil()){
                        include('includes/aside.php');
                    }
                ?>

            </main>