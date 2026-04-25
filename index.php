<?php
// ==========================================
//  ROUTER PRINCIPAL DEL FRONTEND
// ==========================================

// Sanitizar parámetros
$view  = isset($_GET['view'])  ? trim($_GET['view'])  : 'inicio';
$slug  = isset($_GET['slug'])  ? trim($_GET['slug'])  : null;
$extra = isset($_GET['extra']) ? trim($_GET['extra']) : null;

// ==========================================
//  LISTA DE VISTAS PERMITIDAS
// ==========================================

$rutas_validas = [
    'blog',
    'inicio',
    'contacto',
    'articulo',
    'sobre-mi',
    'categoria',
    'categorias',
    'politica-de-privacidad'
];

// Si la vista no existe → 404
if (!in_array($view, $rutas_validas)) {
    http_response_code(404);
    $GLOBALS['pagina_actual'] = '404';
    $view = '404';
}

// Define página actual para el header y el footer
$GLOBALS['pagina_actual'] = $view;

// ==========================================
//  CARGAR HEADER
// ==========================================
require_once __DIR__ . '/includes/header.php';

// ==========================================
//  LÓGICA DEL ROUTER
// ==========================================

// ---------------------------
//  PÁGINA DE INICIO
// ---------------------------
if ($view === 'inicio') {
    require __DIR__ . '/views/inicio.php';
}

// ---------------------------
//  BLOG LISTADO
//  /blog
// ---------------------------
elseif ($view === 'blog' && !$slug) {
    require __DIR__ . '/views/blog.php';
}

// ---------------------------
//  BLOG ARTÍCULO
//  /blog/mi-articulo
//  /blog/mi-articulo/slug
// ---------------------------
elseif ($view === 'blog' && $slug) {
    $articulo_slug = $slug;
    $articulo_extra = $extra; // opcional
    require __DIR__ . '/views/articulo.php';
}

// ---------------------------
//  ARTÍCULO INDIVIDUAL
//  /articulo/mi-slug
// ---------------------------
elseif ($view === 'articulo' && $slug) {
    $articulo_slug = $slug;
    require __DIR__ . '/views/articulo.php';
}

// ---------------------------
//  CONTACTO
//  /contacto
// ---------------------------
elseif ($view === 'contacto') {
    require __DIR__ . '/views/contacto.php';
}

// ---------------------------
//  SOBRE MI
//  /sobre-mi
// ---------------------------
elseif ($view === 'sobre-mi') {
    require __DIR__ . '/views/sobre-mi.php';
}

// ---------------------------
//  CATEGORIAS
//  /categorias
// ---------------------------
elseif ($view === 'categorias') {
    require __DIR__ . '/views/categorias.php';
}

// ---------------------------
//  CATEGORIA INDIVIDUAL
//  /categoria
// ---------------------------
elseif ($view === 'categoria' && $slug) {
    $categoria_slug = $slug;
    require __DIR__ . '/views/categoria.php';
}

// ---------------------------
//  POLITICA DE PRIVACIDAD
//  /politica-de-privacidad
// ---------------------------
elseif ($view === 'politica-de-privacidad') {
    require __DIR__ . '/views/politica-de-privacidad.php';
}

// ---------------------------
//  404
// ---------------------------
else {
    require __DIR__ . '/views/404.php';
}

// ==========================================
//  CARGAR FOOTER
// ==========================================
require_once __DIR__ . '/includes/footer.php';