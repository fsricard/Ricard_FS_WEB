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

$errores = [];
$exito = false;

// Obtener contenido actual
$stmt = $pdo->query("SELECT * FROM politica_privacidad LIMIT 1");
$politica = $stmt->fetch();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');

    if ($contenido === '') {
        $errores[] = "El contenido no puede estar vacío.";
    } else {
        try {
            if ($politica) {
                $stmt = $pdo->prepare("UPDATE politica_privacidad SET contenido = ? WHERE id = ?");
                $stmt->execute([$contenido, $politica['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO politica_privacidad (contenido) VALUES (?)");
                $stmt->execute([$contenido]);
            }
            $exito = true;
            $politica['contenido'] = $contenido;
        } catch (PDOException $e) {
            $errores[] = "Error al guardar: " . $e->getMessage();
        }
    }
}

$pagina = 'politica_de_privacidad';

include('../../includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Política de privacidad</h2>

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
                <label>Contenido legal:</label>
                <div id="editor-descripcion" class="quill-editor">
                    <?= !empty($politica['contenido']) ? $politica['contenido'] : '<p></p>' ?>
                </div>
                <textarea id="descripcion" name="contenido" class="editor-html" style="display:none;">
                        <?= htmlspecialchars($politica['contenido'] ?? '') ?>
                    </textarea>

                <button type="submit" id="btn-guardar" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </section>
</main>

<?php include('../../includes/footer.php');
