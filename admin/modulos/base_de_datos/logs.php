<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Si no está logueado, redirigimos al login
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$pagina = 'logs';

include('includes/header.php');
?>
<main>
    <section>
        <h2>Registro de actividad</h2>

        <!-- Filtros -->
        <form id="filtrosLogs" class="filtros">
            <label>Usuario:</label>
            <input type="text" name="usuario" placeholder="Ej: Diablillo">

            <label>Acción:</label>
            <select name="accion">
                <option value="">Todos</option>
                <option value="Login correcto">Login correcto</option>
                <option value="Logout">Logout</option>
            </select>

            <label>Desde:</label>
            <input type="date" name="desde">

            <label>Hasta:</label>
            <input type="date" name="hasta">

            <button type="submit"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <button type="button" id="resetFiltros"><i class="fa-solid fa-rotate-left"></i> Limpiar filtros</button>
        </form>

        <div id="logs-table"></div>
        <div id="pagination"></div>
    </section>
</main>

<script>
function loadLogs(page = 1) {
    let filtros = $("#filtrosLogs").serialize();
    $.ajax({
        url: "ajax/logs_list.php",
        type: "GET",
        dataType: "json",
        data: filtros + "&page=" + page,
        success: function(response) {
            $("#logs-table").html(response.table);
            $("#pagination").html(response.pagination);
        },
        error: function() {
            $("#logs-table").html("<p>Error al cargar logs.</p>");
        }
    });
}

$(document).ready(function(){
    loadLogs();

    $("#filtrosLogs").on("submit", function(e){
        e.preventDefault();
        loadLogs();
    });

    $("#resetFiltros").on("click", function(){
        $("#filtrosLogs")[0].reset();
        loadLogs();
    });

    $(document).on("click", ".page-link", function(e){
        e.preventDefault();
        let page = $(this).data("page");
        loadLogs(page);
    });
});
</script>