<?php
$mensaje_ok = null;
$mensaje_error = null;

if (isset($_POST['enviar'])) {

    $nombre  = trim($_POST['nombre']);
    $email   = trim($_POST['email']);
    $asunto  = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);

    if ($nombre !== "" && $email !== "" && $asunto !== "" && $mensaje !== "") {

        try {
            $stmt = $pdo->prepare("
                INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje)
                VALUES (:nombre, :email, :asunto, :mensaje)
            ");

            $stmt->execute([
                ':nombre'  => $nombre,
                ':email'   => $email,
                ':asunto'  => $asunto,
                ':mensaje' => $mensaje
            ]);

            $mensaje_ok = "Tu mensaje ha sido invocado correctamente. El Diablillo lo revisará cuando deje de quemar cosas.";
        
        } catch (Exception $e) {
            $mensaje_error = "Algo salió mal en el inframundo. Inténtalo más tarde.";
        }

    } else {
        $mensaje_error = "Todos los campos son obligatorios, mortal.";
    }
}
?>

<main class="layout-home">

    <section class="destacados">

        <article class="destacado-block">
            <h2 class="destacado-title">
                <i class="fa-solid fa-fire-flame-curved"></i> Contacta con el Diablillo
            </h2>

            <?php
                $stmt = $pdo->query("
                    SELECT contenido, actualizado
                    FROM intro_contacto
                    ORDER BY id DESC
                    LIMIT 1
                ");

                $intro_contacto = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>

            <div class="destacado-content">
                <?= $intro_contacto['contenido'] ?>
            </div>

            <div class="destacado-content">

                <?php if(isset($mensaje_ok)): ?>
                    <div class="alert alert-ok">
                        <?= $mensaje_ok ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($mensaje_error)): ?>
                    <div class="alert alert-error">
                        <?= $mensaje_error ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="form-diablillo">

                    <div class="form-group">
                        <label for="nombre">Tu nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Tu email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="asunto">Asunto</label>
                        <input type="text" id="asunto" name="asunto" required>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="6" required></textarea>
                    </div>

                    <button type="submit" name="enviar" class="btn-diablillo">
                        Invocar al Diablillo 🔥
                    </button>

                </form>

            </div>

        </article>

    </section>

    <?php
        if (!esSoloMovil()){
            include('includes/aside.php');
        }
    ?>

</main>