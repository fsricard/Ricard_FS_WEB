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

$mensaje = "";

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $frase = trim($_POST['frase'] ?? '');

    if (empty($frase)) {
        $mensaje = mostrarAlerta('La frase no puede estar vacía.', 'error');
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO loco_frases (frase, activo)
            VALUES (:frase, 1)
        ");

        if ($stmt->execute([
            ':frase' => $frase
        ])) {
            $mensaje = mostrarAlerta('Frase sarcástica guardada correctamente.', 'success');
        } else {
            $mensaje = mostrarAlerta('Error al guardar la frase.', 'error');
        }
    }
}

$pagina = 'loco_dice';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Frases del "Loco dice"</h2>

            <?= $mensaje ?>

            <form method="post" class="formulario">

                <label>Crear una nueva frase del Loco:</label>
                <textarea id="descripcion" name="frase"></textarea>

                <button type="submit" id="btn-guardar" class="btn-primary" style="margin-top:20px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>

            </form>
        </div>
    </section>
</main>

<?php include('../../includes/footer.php');
