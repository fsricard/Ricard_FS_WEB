<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../../config/funciones.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$errores = [];
$exito = false;

// Obtener contenido actual
$stmt = $pdo->query("SELECT * FROM intro_contacto LIMIT 1");
$contacto_intro = $stmt->fetch();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');

    if ($contenido === '') {
        $errores[] = "El contenido no puede estar vacío.";
    } else {
        try {
            if ($contacto_intro) {
                $stmt = $pdo->prepare("UPDATE intro_contacto SET contenido = ? WHERE id = ?");
                $stmt->execute([$contenido, $contacto_intro['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO intro_contacto (contenido) VALUES (?)");
                $stmt->execute([$contenido]);
            }
            $exito = true;
            $contacto_intro['contenido'] = $contenido;
        } catch (PDOException $e) {
            $errores[] = "Error al guardar: " . $e->getMessage();
        }
    }
}

$pagina = 'contacto_intro';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Introducción de la pagina de contacto</h2>

            <?php if ($exito): ?>
                <p class="exito"><i class="fa-regular fa-check-double"></i> Cambios guardados correctamente.</p>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="errores">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="formulario">
                <label>Texto invocatorio:</label>
                <div id="editor-descripcion" class="quill-editor">
                    <?= !empty($contacto_intro['contenido']) ? $contacto_intro['contenido'] : '<p></p>' ?>
                </div>
                <textarea id="descripcion" name="contenido" class="editor-html" style="display:none;">
                        <?= htmlspecialchars($contacto_intro['contenido'] ?? '') ?>
                    </textarea>

                <button type="submit" id="btn-guardar" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </section>
</main>

<?php include('../../includes/footer.php');
