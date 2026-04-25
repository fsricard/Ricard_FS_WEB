<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');

// PAGINACIÓN
$por_pagina = 100;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina = max($pagina, 1);
$offset = ($pagina - 1) * $por_pagina;

// Total de categorías
$sql_total = $pdo->query("SELECT COUNT(*) AS total FROM categorias");
$total_categorias = $sql_total->fetch(PDO::FETCH_ASSOC)['total'];
$total_paginas = ceil($total_categorias / $por_pagina);

// OBTENER CATEGORÍAS
$sql = $pdo->prepare("SELECT id, nombre, slug, descripcion 
                     FROM categorias 
                     ORDER BY nombre ASC 
                     LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', (int)$por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$sql->execute();
$categorias = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="layout-home">

    <section class="destacados">

        <article class="destacado-block">
            <h2 class="destacado-title">
                <i class="fa-solid fa-tags"></i> Categorías del blog
            </h2>

            <div class="destacado-content">

                <?php if (empty($categorias)): ?>
                    <p>No hay categorías disponibles.</p>
                <?php else: ?>

                    <?php foreach ($categorias as $cat): ?>
                        <div class="categoria-item">
                            <h3 class="categoria-title">
                                <a href="categoria/<?= $cat['slug'] ?>" data-text="<?= htmlspecialchars($cat['nombre']) ?>">
                                    <i class="fa-solid fa-chevron-right"></i> <?= htmlspecialchars($cat['nombre']) ?>
                                </a>
                            </h3>

                            <p class="categoria-descripcion">
                                <?= $cat['descripcion'] ?>
                            </p>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </article>

    </section>

    <?php
        if (!esSoloMovil()){
            include('includes/aside.php');
        }
    ?>

</main>