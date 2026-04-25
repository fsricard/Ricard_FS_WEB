<nav id="sidebar" class="sidebar">

    <!-- Inicio -->
    <a href="<?= asset('admin/dashboard.php') ?>">
        <i class="fa-solid fa-house icon-inicio"></i> Inicio
    </a>

    <!-- Frases -->
    <div class="submenu">
        <button class="submenu-toggle">
            <i class="fa-solid fa-hand-horns icon-frases"></i> Frases
            <i class="fa-solid fa-chevron-down flecha"></i>
        </button>

        <ul class="submenu-items">
            <li><a href="<?= asset('admin/frases.php') ?>"><i class="fa-solid fa-quote-left icon-frases-frases"></i> Frases</a></li>
            <li><a href="<?= asset('admin/frases_listado.php') ?>"><i class="fa-solid fa-list icon-frases-listado"></i> Listado</a></li>
            <li><a href="<?= asset('admin/loco_dice.php') ?>"><i class="fa-solid fa-face-grin-tongue-wink icon-frases-loco"></i> El Loco dice</a></li>
            <li><a href="<?= asset('admin/loco_dice_listado.php') ?>"><i class="fa-solid fa-scroll icon-frases-scroll"></i> El listado del Loco</a></li>
        </ul>
    </div>

    <!-- Documentos -->
    <div class="submenu">
        <button class="submenu-toggle">
            <i class="fa-solid fa-book-open icon-documentos"></i> Documentos
            <i class="fa-solid fa-chevron-down flecha"></i>
        </button>

        <ul class="submenu-items">
            <li><a href="<?= asset('admin/contacto.php') ?>"><i class="fa-solid fa-envelope icon-doc-contacto"></i> Contacto</a></li>
            <li><a href="<?= asset('admin/sobre_mi.php') ?>"><i class="fa-solid fa-user icon-doc-sobremi"></i> Sobre mí</a></li>
            <li><a href="<?= asset('admin/contacto_intro.php') ?>"><i class="fa-solid fa-envelope icon-doc-contacto"></i> Contacto intro</a></li>
            <li><a href="<?= asset('admin/sobre_mi_inicio.php') ?>"><i class="fa-solid fa-id-card icon-doc-sobremi-inicio"></i> Sobre mí inicio</a></li>
            <li><a href="<?= asset('admin/politica_de_privacidad.php') ?>"><i class="fa-solid fa-shield-halved icon-doc-privacidad"></i> Política de privacidad</a></li>
        </ul>
    </div>

    <!-- Blog -->
    <div class="submenu">
        <button class="submenu-toggle">
            <i class="fa-solid fa-blog icon-blog"></i> Blog
            <i class="fa-solid fa-chevron-down flecha"></i>
        </button>

        <ul class="submenu-items">
            <li><a href="<?= asset('admin/blog.php') ?>"><i class="fa-solid fa-pen-nib icon-blog-escribir"></i> Escribir</a></li>
            <li><a href="<?= asset('admin/articulos.php') ?>"><i class="fa-solid fa-newspaper icon-blog-articulos"></i> Artículos</a></li>
            <li><a href="<?= asset('admin/categorias.php') ?>"><i class="fa-solid fa-tags icon-blog-categorias"></i> Categorías</a></li>
            <li><a href="<?= asset('admin/blog_comentarios.php') ?>"><i class="fa-solid fa-comments icon-blog-comentarios"></i> Comentarios</a></li>
            <li><a href="<?= asset('admin/blog_papelera.php') ?>"><i class="fa-solid fa-trash icon-blog-papelera"></i> Artículos eliminados</a></li>
            <li><a href="<?= asset('admin/blog_comentarios_papelera.php') ?>"><i class="fa-solid fa-comment-slash icon-blog-comentarios-papelera"></i> Comentarios eliminados</a></li>
        </ul>
    </div>

    <!-- Base de datos -->
    <div class="submenu">
        <button class="submenu-toggle">
            <i class="fa-solid fa-database icon-bd"></i> Base de datos
            <i class="fa-solid fa-chevron-down flecha"></i>
        </button>

        <ul class="submenu-items">
            <li><a href="<?= asset('admin/logs.php') ?>"><i class="fa-solid fa-file-lines icon-bd-logs"></i> Logs</a></li>
            <li><a href="<?= asset('admin/usuarios.php') ?>"><i class="fa-solid fa-users icon-bd-usuarios"></i> Usuarios</a></li>
            <li><a href="<?= asset('admin/tablas_de_datos.php') ?>"><i class="fa-solid fa-table icon-bd-tablas"></i> Tablas de datos</a></li>
        </ul>
    </div>

    <!-- Logout -->
    <a href="<?= asset('admin/logout.php') ?>">
        <i class="fa-solid fa-right-from-bracket icon-logout"></i> Cerrar sesión
    </a>

</nav>