<main>
    <section>
        <div class="container">
            <h2>Edición de artículo</h2>

            <form method="post" enctype="multipart/form-data">

                <!-- Título -->
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo"
                    value="<?= htmlspecialchars($articulo['titulo']) ?>" required>

                <!-- Categorías (selección múltiple) -->
                <label for="categoria">Categorías</label>
                <select name="categorias[]" id="categoria" multiple required>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= in_array($cat['id'], $categoriasAsignadas) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Pulsa CTRL o CMD para seleccionar varias</small>

                <!-- Contenido -->
                <label for="editor-descripcion">Contenido</label>
                <div id="editor-descripcion" class="quill-editor">
                    <?= $articulo['contenido'] ?>
                </div>
                <textarea id="descripcion" name="descripcion" style="display:none;"></textarea>

                <!-- Imagen destacada actual -->
                <label>Imagen destacada actual</label>
                <?php if (!empty($articulo['imagen_destacada'])): ?>
                    <?php
                        $img = $articulo['imagen_destacada'];

                        // Si empieza por http, es una URL externa (Pexels)
                        if (preg_match('/^https?:\/\//i', $img)) {
                            $imgSrc = $img;
                        } else {
                            // Imagen local subida manualmente
                            $imgSrc = asset('../uploads/blog/' . $img);
                        }
                    ?>
                    <img src="<?= $imgSrc ?>" 
                        class="thumb" style="width:120px; height:120px; object-fit:cover; border-radius:6px;">
                <?php else: ?>
                    <p>No hay imagen destacada</p>
                <?php endif; ?>

                <!-- Subir nueva imagen -->
                <label for="imagen">Subir nueva imagen destacada</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">

                <!-- Estado -->
                <label for="estado">Estado</label>
                <select name="estado" id="estado">
                    <option value="borrador"   <?= $articulo['estado'] === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    <option value="publicado"  <?= $articulo['estado'] === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                    <option value="archivado"  <?= $articulo['estado'] === 'archivado' ? 'selected' : '' ?>>Archivado</option>
                </select>

                <!-- Botón guardar -->
                <button type="submit" id="btn-guardar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>

                <!-- Botón volver -->
                <a href="articulos.php" class="btn btn-volver" style="margin-bottom: 15px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </form>
        </div>
    </section>
</main>