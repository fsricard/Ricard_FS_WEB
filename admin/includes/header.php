<?php
require_once(__DIR__ . '/../../config/funciones.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tituloPagina($pagina ?? '') ?></title>

    <!-- FontAwesome 7.0.1 CSS -->
    <link href="<?= asset('css/fontawesome/css/brands.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/chisel-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/duotone.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/duotone-light.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/duotone-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/duotone-thin.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/etch-solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/fontawesome.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/jelly-duo-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/jelly-fill-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/jelly-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/light.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/notdog-duo-solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/notdog-solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-duotone-light.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-duotone-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-duotone-solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-duotone-thin.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-light.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/sharp-thin.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/slab-press-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/slab-regular.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/solid.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/svg.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/svg-with-js.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/thin.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/thumbprint-light.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/v4-font-face.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/v4-shims.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/v5-font-face.css') ?>" rel="stylesheet" />
    <link href="<?= asset('css/fontawesome/css/whiteboard-semibold.css') ?>" rel="stylesheet" />

    <!-- Fuentes de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kings&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kings&family=Twinkle+Star&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('admin/css/style.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Estilos y script de Quill -->
    <link rel="stylesheet" href="<?= asset('admin/css/diablillo.snow.css') ?>">
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
</head>
<body data-pagina="<?= $pagina ?>" data-tabla="<?= $tabla ?>" class="preset-infernal-suave">
    <header>
        <div class="logo">
            <h1><?= tituloPagina($pagina ?? '') ?></h1>
        </div>
        <button id="menu-toggle" class="menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <?php include('sidebar.php'); ?>

    </header>

    <script>
        // Script para el menú móvil
        $(document).ready(function(){
            $("#menu-toggle").on("click", function(){
                $("#sidebar").toggleClass("active");
            });

            // Cerrar menú al hacer clic en un enlace (opcional)
            $("#sidebar a").on("click", function(){
                if ($(window).width() <= 768) {
                    $("#sidebar").removeClass("active");
                }
            });
        });

        // Script para abrir el menú desplegable
        document.querySelector('.submenu-toggle').addEventListener('click', function() {
            const submenu = this.parentElement;
            submenu.classList.toggle('open');

            const items = submenu.querySelector('.submenu-items');
            if (submenu.classList.contains('open')) {
                items.style.maxHeight = items.scrollHeight + "px";
            } else {
                items.style.maxHeight = 0;
            }
        });
    </script>