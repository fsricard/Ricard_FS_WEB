<?php
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../../config/funciones.php');

$locoFrase = loco_frase_random($pdo);
?>
                    
<section class="sidebar-block loco-infernal">
    <h3 class="sidebar-title">
        <i class="fa-solid fa-face-grin-tongue-wink"></i> El loco dice...
    </h3>

    <div class="loco-container">
        <div class="loco-avatar">
            <img src="<?= asset('/img/logo_0001.png') ?>" alt="Logo Diablillo">
        </div>

        <p class="loco-frase">
            <?= htmlspecialchars($locoFrase) ?>
        </p>
    </div>
</section>