<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once(__DIR__ . '/../../../config/funciones.php');

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Si no es admin, podemos redirigir a otra página o mostrar error
if (!isAdmin()) {
    die("Acceso denegado. No tienes permisos suficientes.");
}

$pagina = 'usuarios';

include('../../includes/header.php');
?>

<main>
    <section>
        <h2>Crear nuevo usuario</h2>
        <form id="crearUsuarioForm" method="post" action="ajax/usuarios_crud.php">
            <label><i class="fa-regular fa-id-card"></i> Nombre:</label>
            <input type="text" name="nombre" required>

            <label><i class="fa-regular fa-envelope"></i> Correo:</label>
            <input type="email" name="correo" required>

            <label><i class="fa-solid fa-key"></i> Contraseña:</label>
            <input type="password" name="clave" required>

            <label><i class="fa-solid fa-user-shield"></i> Rol:</label>
            <select name="rol" required>
                <option value="admin"><i class="fa-solid fa-user-tie"></i> Admin</option>
                <option value="visitante"><i class="fa-regular fa-user"></i> Visitante</option>
            </select>

            <button type="submit"><i class="fa-solid fa-plus"></i> Crear</button>
        </form>
    </section>

    <section>
        <h2>Lista de usuarios</h2>
        <div id="usuarios-table"></div>
        <div id="pagination"></div>
    </section>
</main>

<script>
    // Función para cargar usuarios con AJAX
    function loadUsuarios(page = 1) {
        $.ajax({
            url: "ajax/usuarios_list.php",
            type: "GET",
            dataType: "json",
            data: {
                page: page
            },
            success: function(response) {
                $("#usuarios-table").html(response.table);
                $("#pagination").html(response.pagination);
            },
            error: function() {
                $("#usuarios-table").html("<p>Error al cargar usuarios.</p>");
            }
        });
    }

    $(document).ready(function() {
        // Cargar usuarios al inicio
        loadUsuarios();

        // Crear usuario vía AJAX
        $("#crearUsuarioForm").on("submit", function(e) {
            e.preventDefault();
            $.post("ajax/usuarios_crud.php", $(this).serialize(), function(resp) {
                alert(resp.message);
                loadUsuarios(); // refrescar tabla
            }, "json");
        });

        // Delegar eventos de paginación
        $(document).on("click", ".page-link", function(e) {
            e.preventDefault();
            let page = $(this).data("page");
            loadUsuarios(page);
        });
    });

    // Función para cargar usuarios con AJAX
    function loadUsuarios(page = 1) {
        $.ajax({
            url: "ajax/usuarios_list.php",
            type: "GET",
            dataType: "json",
            data: {
                page: page
            },
            success: function(response) {
                $("#usuarios-table").html(response.table);
                $("#pagination").html(response.pagination);
            },
            error: function() {
                $("#usuarios-table").html("<p>Error al cargar usuarios.</p>");
            }
        });
    }

    $(document).ready(function() {
        // Cargar usuarios al inicio
        loadUsuarios();

        // Crear usuario vía AJAX
        $("#crearUsuarioForm").on("submit", function(e) {
            e.preventDefault();
            $.post("ajax/usuarios_crud.php", $(this).serialize() + "&action=crear", function(resp) {
                alert(resp.message);
                loadUsuarios(); // refrescar tabla
            }, "json");
        });

        // Delegar eventos de paginación
        $(document).on("click", ".page-link", function(e) {
            e.preventDefault();
            let page = $(this).data("page");
            loadUsuarios(page);
        });

        // Delegar evento de actualización
        $(document).on("click", ".update-user", function() {
            let id = $(this).data("id");
            let nombre = $(".edit-nombre[data-id='" + id + "']").val();
            let correo = $(".edit-correo[data-id='" + id + "']").val();
            let rol = $(".edit-rol[data-id='" + id + "']").val();

            $.post("ajax/usuarios_crud.php", {
                action: "editar",
                id: id,
                nombre: nombre,
                correo: correo,
                rol: rol
            }, function(resp) {
                alert(resp.message);
                loadUsuarios();
            }, "json");
        });

        // Delegar evento de eliminación
        $(document).on("click", ".delete-user", function() {
            if (!confirm("¿Eliminar usuario?")) return;
            let id = $(this).data("id");

            $.post("ajax/usuarios_crud.php", {
                action: "eliminar",
                id: id
            }, function(resp) {
                alert(resp.message);
                loadUsuarios();
            }, "json");
        });
    });
</script>
</body>

</html>