<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../config/funciones.php');
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="<?= asset('img/favicon.ico') ?>">
        <link rel="shortcut icon" href="<?= asset('img/favicon.ico') ?>" type="image/x-icon">
        
        <!-- FontAwesome 7.0.1 CSS -->
        <link href="<?= asset('/css/fontawesome/css/brands.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/chisel-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/duotone.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/duotone-light.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/duotone-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/duotone-thin.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/etch-solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/fontawesome.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/jelly-duo-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/jelly-fill-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/jelly-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/light.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/notdog-duo-solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/notdog-solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-duotone-light.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-duotone-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-duotone-solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-duotone-thin.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-light.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/sharp-thin.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/slab-press-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/slab-regular.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/solid.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/svg.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/svg-with-js.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/thin.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/thumbprint-light.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/v4-font-face.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/v4-shims.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/v5-font-face.css') ?>" rel="stylesheet" />
        <link href="<?= asset('/css/fontawesome/css/whiteboard-semibold.css') ?>" rel="stylesheet" />

        <!-- Fuentes de Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Kings&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Kings&family=Twinkle+Star&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="<?= asset('/css/style.css') ?>" />
        
        <title><?php mostrarTextoPersonalizado(); ?></title>
    </head>

    <body class="preset-infernal-suave preset-demonio-desatado preset-ritual-oscuro">
    
        <header class="header-container">
        
            <div class="header-content">
                <div class="logo">
                    <img src="<?= asset('/img/logo_0001.png') ?>" alt="Logotipo de el Diablillo" />
                </div>

                <nav class="menu-desktop">
                    <ul>
                        <li><a href="<?= asset('/') ?>"><i class="fa-solid fa-house"></i> Inicio</a></li>
                        <li><a href="<?= asset('/blog') ?>"><i class="fa-solid fa-pen-nib"></i> Blog</a></li>
                        <li><a href="<?= asset('/contacto') ?>"><i class="fa-solid fa-envelope"></i> Contacto</a></li>
                        <li><a href="<?= asset('/sobre-mi') ?>"><i class="fa-solid fa-user"></i> Sobre mí</a></li>
                    </ul>
                </nav>

                <?php
                    if (esSoloMovil()){
                        require_once __DIR__ . '/menu_responsive.php';
                    }
                ?>

            </div>
            
            <div class="titulo-header">
                <h1><?php mostrarTextoPersonalizado(); ?></h1>
            </div>

        </header>