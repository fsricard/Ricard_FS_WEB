<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/funciones.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$pagina = 'articulos';

// Cargar categorías para el filtro
$stmtCat = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

include('includes/header.php');
?>

<main>
    <section>
        <div class="container">
            <h2>Artículos del blog</h2>

            <!-- Filtros -->
            <form id="form-filtros">

                <label for="filtro-titulo">Filtrar por título</label>
                <input type="text" id="filtro-titulo" name="titulo" placeholder="Título del artículo">

                <label for="filtro-usuario">Filtrar por autor</label>
                <input type="text" id="filtro-usuario" name="usuario" placeholder="Nombre del autor">

                <label for="filtro-fecha">Filtrar por fecha</label>
                <input type="date" id="filtro-fecha" name="fecha">

                <!-- NUEVO FILTRO POR CATEGORÍA -->
                <label for="filtro-categoria">Filtrar por categoría</label>
                <select id="filtro-categoria" name="categoria">
                    <option value="">-- Todas --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="button" id="btn-filtrar">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>

                <button type="button" id="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                </button>
            </form>

            <?php
                if (isset($_GET['delete']) && $_GET['delete'] === 'ok'):
                    echo mostrarAlerta('Artículo eliminado correctamente.', 'warning');
                endif;
            ?>

            <!-- Tabla -->
            <table class="table table-striped" id="tabla-articulos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Miniatura</th>
                        <th>Título</th>
                        <th>Categorías</th>
                        <th>Autor</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div id="pagination"></div>
        </div>
    </section>
</main>

<script>
const csrfToken = "<?= $_SESSION['csrf_token'] ?>";

function cargarArticulos(pagina = 1) {
    const titulo = document.getElementById('filtro-titulo').value;
    const usuario = document.getElementById('filtro-usuario').value;
    const fecha = document.getElementById('filtro-fecha').value;
    const categoria = document.getElementById('filtro-categoria').value;

    fetch('ajax/articulos_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ titulo, usuario, fecha, categoria, pagina })
    })
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector('#tabla-articulos tbody');
        tbody.innerHTML = '';

        data.articulos.forEach(art => {
            const categorias = art.categorias.length
                ? art.categorias.join(', ')
                : '';

            tbody.innerHTML += `
                <tr>
                    <td>${art.id}</td>

                    <td>
                        <img src="${
                            art.imagen_destacada && /^https?:\/\//i.test(art.imagen_destacada)
                                ? art.imagen_destacada
                                : '<?= asset("/uploads/blog/") ?>' + (art.imagen_destacada ?? '')
                        }" class="thumb">
                    </td>
                    
                    <td>${art.titulo}</td>
                    <td>${categorias}</td>
                    <td>${art.autor ?? ''}</td>
                    <td>${new Date(art.fecha_creacion).toLocaleString()}</td>
                    <td>${art.estado}</td>
                    <td>
                        <a href="blog_edit.php?id=${art.id}" class="btn update-user">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </a>

                        <form action="blog_delete.php" method="POST" style="display:inline;" class="btn-base">
                            <input type="hidden" name="id" value="${art.id}">
                            <input type="hidden" name="csrf_token" value="${csrfToken}">
                            <button type="submit" class="delete-user"
                                onclick="return confirm('¿Seguro que quieres eliminar este artículo?');">
                                <i class="fa-solid fa-skull-crossbones"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            `;
        });

        // PAGINACIÓN
        const pagDiv = document.getElementById('pagination');
        pagDiv.innerHTML = '';

        for (let i = 1; i <= data.totalPaginas; i++) {
            pagDiv.innerHTML += `<a href="#" class="page-link" data-page="${i}">${i}</a>`;
        }

        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                cargarArticulos(this.dataset.page);
            });
        });
    });
}

document.getElementById('btn-filtrar').addEventListener('click', () => cargarArticulos(1));

document.getElementById('btn-reset').addEventListener('click', function() {
    document.getElementById('filtro-titulo').value = '';
    document.getElementById('filtro-usuario').value = '';
    document.getElementById('filtro-fecha').value = '';
    document.getElementById('filtro-categoria').value = '';
    cargarArticulos(1);
});

cargarArticulos();
</script>

<?php include('includes/footer.php');