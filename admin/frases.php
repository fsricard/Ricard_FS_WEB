<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $frase = trim($_POST['frase'] ?? '');
    $autor = trim($_POST['autor'] ?? '');

    if (empty($frase)) {
        $mensaje = mostrarAlerta('La frase no puede estar vacía.', 'error');
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO sarcasmo_frases (frase, autor, activo)
            VALUES (:frase, :autor, 1)
        ");

        if ($stmt->execute([
            ':frase' => $frase,
            ':autor' => $autor ?: null
        ])) {
            $mensaje = mostrarAlerta('Frase sarcástica guardada correctamente.', 'success');
        } else {
            $mensaje = mostrarAlerta('Error al guardar la frase.', 'error');
        }
    }
}

$pagina = 'frases';

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Frases del Diablillo sarcástico</h2>

            <?= $mensaje ?>

            <form method="post" class="formulario">

                <label>Crear una nueva frasecilla sarcástica:</label>

                <div id="editor-descripcion" class="quill-editor"></div>

                <textarea id="descripcion" name="frase" class="editor-html" style="display:none;"></textarea>

                <label style="margin-top:20px;">Autor (opcional):</label>
                <input type="text" name="autor" class="input-text" placeholder="Ej: El Diablillo, Miriam, Ricard…">

                <button type="submit" id="btn-guardar" class="btn-primary" style="margin-top:20px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>

            </form>
        </div>
    </section>
</main>

<?php include('includes/footer.php'); ?>