<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Filtros
$where = [];
$params = [];

// Nombre
if (!empty($_GET['nombre'])) {
    $where[] = "nombre LIKE :nombre";
    $params[':nombre'] = "%" . $_GET['nombre'] . "%";
}

// Email
if (!empty($_GET['email'])) {
    $where[] = "email LIKE :email";
    $params[':email'] = "%" . $_GET['email'] . "%";
}

// Día
if (!empty($_GET['dia'])) {
    $where[] = "DAY(fecha) = :dia";
    $params[':dia'] = intval($_GET['dia']);
}

// Mes
if (!empty($_GET['mes'])) {
    $where[] = "MONTH(fecha) = :mes";
    $params[':mes'] = intval($_GET['mes']);
}

// Año
if (!empty($_GET['anio'])) {
    $where[] = "YEAR(fecha) = :anio";
    $params[':anio'] = intval($_GET['anio']);
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Expotación del archivo ".PDF"
if (isset($_GET['exportar_pdf']) && $_GET['exportar_pdf'] == 1) {

    require_once __DIR__ . '/../../../includes/fpdf/fpdf.php';

    try {
        $sql_export = "SELECT * FROM mensajes_contacto $where_sql ORDER BY fecha DESC";
        $stmt_export = $pdo->prepare($sql_export);

        foreach ($params as $key => $value) {
            $stmt_export->bindValue($key, $value);
        }

        $stmt_export->execute();
        $datos = $stmt_export->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al exportar PDF: " . $e->getMessage());
    }

    //   CLASE EXTENDIDA PARA PIE
    class PDF_MC_Table extends FPDF
    {

        public $logo;
        public $colorPrimario = [33, 37, 41]; // Gris oscuro corporativo
        public $colorSecundario = [230, 230, 230];

        function Header()
        {
            // Logo
            if ($this->logo) {
                $this->Image($this->logo, 10, 8, 30);
            }

            // Título
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(33, 37, 41);
            $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Exportación de mensajes de contacto'), 0, 1, 'C');

            // Fecha
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha de exportación: ' . date('d/m/Y H:i:s')), 0, 1, 'C');

            $this->Ln(20);
        }

        function Footer()
        {
            // Posición a 1.5 cm del final
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 10);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'C');
        }

        // MultiCell por columnas
        function Row($data, $widths)
        {
            $nb = 0;
            for ($i = 0; $i < count($data); $i++) {
                $nb = max($nb, $this->NbLines($widths[$i], $data[$i]));
            }
            $h = 6 * $nb;

            // Salto de página si es necesario
            if ($this->GetY() + $h > $this->PageBreakTrigger) {
                $this->AddPage($this->CurOrientation);
            }

            // Dibujar celdas
            for ($i = 0; $i < count($data); $i++) {
                $w = $widths[$i];
                $x = $this->GetX();
                $y = $this->GetY();

                $this->Rect($x, $y, $w, $h);
                $this->MultiCell($w, 6, $data[$i], 0, 'L');
                $this->SetXY($x + $w, $y);
            }

            $this->Ln($h);
        }

        function NbLines($w, $txt)
        {
            $cw = &$this->CurrentFont['cw'];
            if ($w == 0) {
                $w = $this->w - $this->rMargin - $this->x;
            }
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace("\r", '', $txt);
            $nb = strlen($s);
            if ($nb > 0 && $s[$nb - 1] == "\n") {
                $nb--;
            }
            $sep = -1;
            $i = 0;
            $j = 0;
            $l = 0;
            $nl = 1;
            while ($i < $nb) {
                $c = $s[$i];
                if ($c == "\n") {
                    $i++;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                    continue;
                }
                if ($c == ' ') {
                    $sep = $i;
                }
                $l += $cw[$c];
                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j) {
                            $i++;
                        }
                    } else {
                        $i = $sep + 1;
                    }
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                } else {
                    $i++;
                }
            }
            return $nl;
        }
    }

    //   CREAR PDF
    $pdf = new PDF_MC_Table('L', 'mm', 'A4');
    $pdf->logo = __DIR__ . '/../../../img/logo_0001.png';
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Encabezado de tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(33, 37, 41);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Cell(12, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'ID'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nombre'), 1, 0, 'C', true);
    $pdf->Cell(50, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Email'), 1, 0, 'C', true);
    $pdf->Cell(40, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Asunto'), 1, 0, 'C', true);
    $pdf->Cell(110, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Mensaje'), 1, 0, 'C', true);
    $pdf->Cell(30, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fecha'), 1, 1, 'C', true);

    // Anchos de columnas
    $widths = [12, 35, 50, 40, 110, 30];

    // Contenido
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);

    foreach ($datos as $fila) {
        $pdf->Row([
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['id']),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['nombre']),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['email']),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['asunto']),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['mensaje']),
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $fila['fecha'])
        ], $widths);
    }

    // Descargar PDF
    $nombre_pdf = "mensajes_contacto_" . date('Y-m-d_H-i-s') . ".pdf";
    $pdf->Output('D', $nombre_pdf);
    exit;
}

// Paginación
$por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Contar total filtrado
try {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes_contacto $where_sql");
    $total_stmt->execute($params);
    $total_mensajes = $total_stmt->fetchColumn();
} catch (PDOException $e) {
    die("Error al contar los mensajes: " . $e->getMessage());
}

$total_paginas = ceil($total_mensajes / $por_pagina);

// Obtener mensajes filtrados
try {
    $sql = "SELECT * FROM mensajes_contacto $where_sql ORDER BY fecha DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);

    // Bind dinámico
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $mensajes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al recuperar los mensajes: " . $e->getMessage());
}

$pagina = 'contacto';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Mensajes de Contacto</h2>

            <!-- Filtros -->
            <form method="GET" class="filtros-form">
                <div class="filtro">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                </div>

                <div class="filtro">
                    <label>Email:</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                </div>

                <div class="filtro">
                    <label>Día:</label>
                    <input type="number" name="dia" min="1" max="31" value="<?= htmlspecialchars($_GET['dia'] ?? '') ?>">
                </div>

                <div class="filtro">
                    <label>Mes:</label>
                    <input type="number" name="mes" min="1" max="12" value="<?= htmlspecialchars($_GET['mes'] ?? '') ?>">
                </div>

                <div class="filtro">
                    <label>Año:</label>
                    <input type="number" name="anio" min="2000" max="2100" value="<?= htmlspecialchars($_GET['anio'] ?? '') ?>">
                </div>

                <button type="submit">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                <button type="button" id="resetFiltros">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                </button>
                <button type="submit" name="exportar_pdf" value="1" class="btn-exportar-pdf">
                    <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                </button>
            </form>

            <?php if (isset($_GET['ok']) && $_GET['ok'] === 'eliminado'): ?>
                <?php $mensaje = mostrarAlerta('Mensaje eliminado correctamente.', 'warning'); ?>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert error">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <!-- Tabla de resultados -->
            <?php if (empty($mensajes)): ?>
                <p>No hay mensajes que coincidan con los filtros.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Asunto</th>
                            <th>Mensaje</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mensajes as $msg): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['fecha']) ?></td>
                                <td><?= htmlspecialchars($msg['nombre']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td><?= htmlspecialchars($msg['asunto']) ?></td>
                                <td><?= nl2br(htmlspecialchars($msg['mensaje'])) ?></td>
                                <td>
                                    <a href="contacto_eliminar.php?id=<?= $msg['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este mensaje?');">
                                        <button class="btn delete-user"><i class="fa-solid fa-skull-crossbones"></i> Eliminar</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginador -->
                <?= paginador($total_mensajes, $por_pagina, $pagina_actual, $_GET, 'pagina'); ?>

            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    // Script para el botón Limpiar filtros
    document.getElementById('resetFiltros').addEventListener('click', function() {
        window.location.href = 'contacto.php';
    });
</script>

<?php include('../../includes/footer.php');
