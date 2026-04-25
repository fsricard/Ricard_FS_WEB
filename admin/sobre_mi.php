<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once(__DIR__ . '/../config/funciones.php');

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$errores = [];
$exito = false;

// Obtener contenido actual
$stmt = $pdo->query("SELECT * FROM sobre_mi LIMIT 1");
$sobre_mi = $stmt->fetch();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');

    if ($contenido === '') {
        $errores[] = "El contenido no puede estar vacío.";
    } else {
        try {
            if ($sobre_mi) {
                $stmt = $pdo->prepare("UPDATE sobre_mi SET contenido = ? WHERE id = ?");
                $stmt->execute([$contenido, $sobre_mi['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO sobre_mi (contenido) VALUES (?)");
                $stmt->execute([$contenido]);
            }
            $exito = true;
            $sobre_mi['contenido'] = $contenido;
        } catch (PDOException $e) {
            $errores[] = "Error al guardar: " . $e->getMessage();
        }
    }
}

$pagina='sobre_mi';

include('includes/header.php');
?>

    <main>
        <section>
            <div class="container">
                <h2>Gestionar texto de la pagina "Sobre mí"</h2>

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
                    <label>Presentación de el Diablillo:</label>
                    <div id="editor-descripcion" class="quill-editor">
                        <?= !empty($sobre_mi['contenido']) ? $sobre_mi['contenido'] : '<p></p>' ?>
                    </div>
                    <textarea id="descripcion" name="contenido" class="editor-html" style="display:none;">
                        <?= htmlspecialchars($sobre_mi['contenido'] ?? '') ?>
                    </textarea>

                    <button type="submit" id="btn-guardar" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </form>
            </div>
        </section>
    </main>

<?php include('includes/footer.php');