<?php
// Función para restringir contenido solo para el rol "admin"
function tienePermiso(): bool {
    $rolesPermitidos = ['admin']; 
    
    return in_array($_SESSION['rol'], $rolesPermitidos, true);
}

// Función para detectar dispositos móviles
function esSoloMovil() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    return preg_match('/(android.*mobile|iphone|ipod|blackberry|windows phone|webos)/i', $ua);
}

// Función para imprimir los textos dinámicos en el header del BackEnd
function tituloPagina($pagina) {
    // Array asociativo de títulos
    $titulos = [
        'logs'                        => 'Registro de logs',
        'blog'                        => 'Escribir nuevo artículo',
        'frases'                      => 'Gestión de las frases sarcásticas',
        'sobre_mi'                    => 'Sobre mí',
        'contacto'                    => 'Mensajes de contacto',
        'usuarios'                    => 'Gestión de Usuarios',
        'articulos'                   => 'Listado de artículos',
        'dashboard'                   => 'Panel de Control',
        'loco_dice'                   => 'El Loco dice ...',
        'categorias'                  => 'Gestión de las categorías del blog',
        'blog_papelera'               => 'Papelera de reciclaje',
        'contacto_intro'              => 'Invocación al contacto',
        'frases_listado'              => 'Listado de las frases del Diablillo',
        'tablas_de_datos'             => 'Tablas de la base de datos',
        'editar_articulo'             => 'Editar artículos',
        'blog_comentarios'            => 'Comentarios de usuarios',
        'categorias_editar'           => 'Edición de la categoría',
        'tablas_de_datos_ver'         => 'Contenido de la tabla de la base datos',
        'politica_de_privacidad'      => 'Política de privacidad',
        'blog_comentarios_papelera'   => 'Papelera de reciclaje de comentarios'
    ];

    // Si existe en el array, devolvemos el título; si no, uno genérico
    return $titulos[$pagina] ?? 'Administración';
}

// Función para imprimir textos personalizados en "header.php" del FrontEnd
function mostrarTextoPersonalizado() {
    // Recupera la ruta desde la variable global
    $pagina = $GLOBALS['pagina_actual'] ?? '';

    // Define los textos personalizados
    $textos = [
        ''                          => 'Yo soy el Diablillo y usted ... no lo es',
        '404'                       => '¡¡Vaya por MÍ que vergüenza!!',
        'blog'                      => 'El blog del loco',
        'inicio'                    => 'Yo soy el Diablillo y Usted ... NO lo es',
        'contacto'                  => 'Contacta con el Diablillo',
        'articulo'                  => '¡¡Esto lo ha escrito una mente desquiciada!!',
        'sobre-mi'                  => 'Sobre el Diablillo',
        'categoria'                 => 'Una categoría del blog del loco',
        'categorias'                => 'Categorías del blog del loco',
        'politica-de-privacidad'    => 'Política de privacidad',
    ];

    // Imprime el texto correspondiente o uno por defecto
    echo $textos[$pagina] ?? 'Yo soy el Diablillo y usted ... no lo es';
}

// Función para mostrar el CopyRight en el footer
function CopyrightRicardFS($startYear = 2021) {
    $currentYear = date('Y');
    $yearDisplay = ($startYear == $currentYear) ? $currentYear : "$startYear – $currentYear";
    return "&copy; $yearDisplay El Diablillo - Todos los derechos reservados";
}

// Función para crear un sistema de paginación modular
function paginador($total_registros, $por_pagina, $pagina_actual, $filtros = [], $param_pagina = 'p') {

    $total_paginas = max(1, ceil($total_registros / $por_pagina));

    // No queremos arrastrar el parámetro de página en los filtros
    unset($filtros[$param_pagina]);

    // Construir query string con el resto de filtros
    $query = '';
    if (!empty($filtros)) {
        $query = '&' . http_build_query($filtros);
    }

    $html = '<div class="paginacion">';

    // Anterior
    if ($pagina_actual > 1) {
        $html .= '<a class="btn-pag" href="?' . $param_pagina . '=' . ($pagina_actual - 1) . $query . '">Anterior</a>';
    }

    // Números
    for ($i = 1; $i <= $total_paginas; $i++) {
        $activo = ($i == $pagina_actual) ? 'activo' : '';
        $html .= '<a class="btn-pag ' . $activo . '" href="?' . $param_pagina . '=' . $i . $query . '">' . $i . '</a>';
    }

    // Siguiente
    if ($pagina_actual < $total_paginas) {
        $html .= '<a class="btn-pag" href="?' . $param_pagina . '=' . ($pagina_actual + 1) . $query . '">Siguiente</a>';
    }

    $html .= '</div>';

    return $html;
}

// Función para obtener la frase sarcástica del día en la pagina de inicio del FrontEnd
function obtenerFraseDelDia() {
    global $pdo;

    // Día actual como número (20250127)
    $dia = date('Ymd');

    // Intentamos ver si ya se guardó una frase para hoy
    $stmt = $pdo->prepare("SELECT frase FROM sarcasmo_frases_dia WHERE fecha = ?");
    $stmt->execute([$dia]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        return $resultado['frase'];
    }

    // Si no existe, elegimos una frase aleatoria
    $stmt = $pdo->query("SELECT frase FROM sarcasmo_frases ORDER BY RAND() LIMIT 1");
    $frase = $stmt->fetchColumn();

    // Guardamos la frase para hoy
    $stmt = $pdo->prepare("INSERT INTO sarcasmo_frases_dia (fecha, frase) VALUES (?, ?)");
    $stmt->execute([$dia, $frase]);

    return $frase;
}

// Función para generar los extractos de los artículos
function generarExtracto($texto, $maxPalabras = 150) {
    // Permitimos span e i para conservar los iconos
    $texto = strip_tags($texto, '<span><i>');

    $palabras = explode(' ', $texto);

    if (count($palabras) <= $maxPalabras) {
        return implode(' ', $palabras);
    }

    return implode(' ', array_slice($palabras, 0, $maxPalabras)) . '...';
}

// Función para extraer fecha formateada
function formatearFecha($fecha) {
    $date = new DateTime($fecha);

    $formatter = new IntlDateFormatter(
        'es_ES',
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        'Europe/Madrid',
        IntlDateFormatter::GREGORIAN,
        "d 'de' MMMM 'de' yyyy"
    );

    return $formatter->format($date);
}

// Función para las frases cortas del Loco infernal
function loco_frase_random(PDO $pdo): string {
    $stmt = $pdo->query("
        SELECT frase 
        FROM loco_frases 
        WHERE activo = 1 
        ORDER BY RAND() 
        LIMIT 1
    ");

    return $stmt->fetchColumn() ?: 'El Diablillo está mudo hoy...';
}

// Función para el sistema de mensajes de alerta modular
function mostrarAlerta(string $mensaje, string $tipo = 'success'): string {
    $tipos = [
        'success' => [
            'icon' => 'fa-circle-check',
            'color' => 'var(--color-success)',
            'bg'    => 'var(--bg-success)'
        ],
        'error' => [
            'icon' => 'fa-circle-xmark',
            'color' => 'var(--color-danger)',
            'bg'    => 'var(--bg-danger)'
        ],
        'info' => [
            'icon' => 'fa-circle-info',
            'color' => 'var(--color-info)',
            'bg'    => 'var(--bg-info)'
        ],
        'warning' => [
            'icon' => 'fa-triangle-exclamation',
            'color' => 'var(--color-warning)',
            'bg'    => 'var(--bg-warning)'
        ]
    ];

    $t = $tipos[$tipo] ?? $tipos['success'];

    return '
        <div class="alerta-global" 
             style="
                background:' . $t['bg'] . ';
                border-left:4px solid ' . $t['color'] . ';
                color:' . $t['color'] . ';
             ">
            <i class="fa-solid ' . $t['icon'] . '"></i>
            ' . htmlspecialchars($mensaje) . '
        </div>
    ';
}

// Función para crear rutas absolutas
function base_url(): string
{
    // Detectar protocolo
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https://'
        : 'http://';

    // Host (dominio + puerto)
    $host = $_SERVER['HTTP_HOST'];

    // Ruta absoluta del proyecto
    $projectPath = realpath(__DIR__ . '/..');

    // Ruta absoluta del DOCUMENT_ROOT
    $rootPath = realpath($_SERVER['DOCUMENT_ROOT']);

    // Calcular subcarpeta correctamente
    $subcarpeta = str_replace('\\', '/', $projectPath);
    $rootPath   = str_replace('\\', '/', $rootPath);

    $subcarpeta = str_replace($rootPath, '', $subcarpeta);

    // Asegurar que empieza con "/"
    $subcarpeta = '/' . ltrim($subcarpeta, '/');

    // Asegurar que NO termina con "/"
    return rtrim($protocolo . $host . $subcarpeta, '/');
}

// Genera rutas absolutas correctas para assets.
function asset(string $ruta): string
{
    // Asegura que base_url() NO termina con "/"
    $base = rtrim(base_url(), '/');

    // Asegura que la ruta SÍ empieza con "/"
    $ruta = '/' . ltrim($ruta, '/');

    return $base . $ruta;
}

// Función para guardar el slug de los artículos limpio
function generarSlug(string $texto): string {
    // Quitar etiquetas y espacios extremos
    $texto = trim(strip_tags($texto));

    // Convertir a UTF-8 si aplica (depende de tu entorno)
    if (function_exists('mb_convert_encoding')) {
        $texto = mb_convert_encoding($texto, 'UTF-8', mb_detect_encoding($texto, 'UTF-8, ISO-8859-1', true));
    }

    // Sustituir acentos y caracteres comunes
    $map = [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','Ü'=>'U',
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u'
    ];
    $texto = strtr($texto, $map);

    // Pasar a minúsculas
    $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);

    // Reemplazar cualquier cosa que no sea a-z, 0-9 con guiones
    $texto = preg_replace('/[^a-z0-9]+/u', '-', $texto);

    // Colapsar múltiples guiones y recortar
    $texto = preg_replace('/-+/', '-', $texto);
    $texto = trim($texto, '-');

    // Prevención de vacío
    return $texto !== '' ? $texto : 'articulo';
}

// Garantiza un slug único en la tabla indicada, añadiendo sufijos -2, -3, ...
function generarSlugUnico(PDO $pdo, string $texto, string $tabla, string $campo = 'slug', string $idCampo = 'id', ?int $excluirId = null): string {
    $base = generarSlug($texto);
    $slug = $base;
    $contador = 2;

    $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$campo} = :slug";
    if ($excluirId !== null) {
        $sql .= " AND {$idCampo} <> :excluir";
    }
    $stmt = $pdo->prepare($sql);

    while (true) {
        $params = [':slug' => $slug];
        if ($excluirId !== null) {
            $params[':excluir'] = $excluirId;
        }
        $stmt->execute($params);
        $existe = (int)$stmt->fetchColumn() > 0;

        if (!$existe) {
            return $slug;
        }
        $slug = "{$base}-{$contador}";
        $contador++;
    }
}

// Registra eventos en un archivo de log con bloqueo de escritura.
function registrarLog(string $mensaje, string $nivel = 'INFO', ?int $usuarioId = null, ?string $modulo = null): void {
    // Ruta del log (ajusta si ya tienes una ruta centralizada)
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/app.log';

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $fecha = date('Y-m-d H:i:s');
    $ip = obtenerIpCliente();
    $usuarioId = $usuarioId ?? (isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : 0);
    $modulo = $modulo ?? obtenerModuloActual();

    // Limpieza básica del mensaje para evitar saltos extraños
    $mensaje = str_replace(["\r", "\n"], ' ', $mensaje);

    $linea = sprintf("[%s] %s | %d | %s | %s | %s\n", $fecha, strtoupper($nivel), $usuarioId, $ip, $modulo, $mensaje);

    // Escritura con bloqueo para evitar condiciones de carrera
    $fh = @fopen($logFile, 'a');
    if ($fh) {
        @flock($fh, LOCK_EX);
        @fwrite($fh, $linea);
        @flock($fh, LOCK_UN);
        @fclose($fh);
    }
}

// IP del cliente considerando cabeceras comunes de proxies.
function obtenerIpCliente(): string {
    $candidatas = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];
    foreach ($candidatas as $key) {
        if (!empty($_SERVER[$key])) {
            // Tomar la primera IP si hay lista separada por comas
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

// Determina el "módulo actual" para logging según el script.
function obtenerModuloActual(): string {
    $script = isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : 'cli';
    return $script;
}

// Función universal para obtener las categorías de los artículos
function obtenerCategoriasArticulo(PDO $pdo, int $articulo_id): array {
    $stmt = $pdo->prepare("
        SELECT c.id, c.nombre, c.slug
        FROM categorias c
        INNER JOIN blog_articulo_categoria ac ON ac.categoria_id = c.id
        WHERE ac.articulo_id = :id
        ORDER BY c.nombre ASC
    ");
    $stmt->execute([':id' => $articulo_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función universal para mostral las categorías de los artículos
function mostrarCategoriasArticulo(PDO $pdo, int $articulo_id): string {

    // 1. Obtener categorías del artículo
    $stmt = $pdo->prepare("
        SELECT c.id, c.nombre, c.slug
        FROM categorias c
        INNER JOIN blog_articulo_categoria ac ON ac.categoria_id = c.id
        WHERE ac.articulo_id = :id
        ORDER BY c.nombre ASC
    ");
    $stmt->execute([':id' => $articulo_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($categorias)) {
        return ''; // Sin categorías, no mostramos nada
    }

    // 2. Iconos personalizados por categoría
    $iconos = [
        'Sentimientos' => 'fa-solid fa-heart-crack',
        'Silencio'     => 'fa-solid fa-face-zipper',
        'Dolor'        => 'fa-solid fa-swords',
        'Soledad'      => 'fa-solid fa-user-large-slash'
    ];

    // 3. Construcción del HTML completo
    $html = '<div class="fun-categorias">';

    foreach ($categorias as $cat) {
        $nombre = htmlspecialchars($cat['nombre']);
        $slug   = htmlspecialchars($cat['slug']);
        $url    = asset('/categoria/' . $slug);

        // Icono asignado o icono genérico
        $icono = $iconos[$nombre] ?? 'fa-solid fa-tag';

        $html .= "
            <a href=\"{$url}\" class=\"cat-chip\">
                <i class=\"{$icono}\"></i> {$nombre}
            </a>
        ";
    }

    $html .= '</div>';

    return $html;
}