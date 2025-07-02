<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}
?>

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> <?= $editando ? 'Editar producto' : 'Agregar nuevo producto' ?></h2>
        <a href="?sec=admin/admin_productos" class="btn btn-secondary btn-tematico"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="?sec=admin/agregar_producto<?= $editando ? '&id=' . htmlspecialchars($producto->getId()) : '' ?>" enctype="multipart/form-data" class="admin-form-container">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?=htmlspecialchars($producto->getId())?>"><?php endif; ?>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label"><i class="bi bi-tag-fill"></i> Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required value="<?=htmlspecialchars($_POST['nombre'] ?? ($producto ? $producto->getNombre() : ''))?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="precio" class="form-label"><i class="bi bi-currency-dollar"></i> Precio</label>
                <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required value="<?=htmlspecialchars($_POST['precio'] ?? ($producto ? $producto->getPrecio() : ''))?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label"><i class="bi bi-card-text"></i> Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?=htmlspecialchars($_POST['descripcion'] ?? ($producto ? $producto->getDescripcion() : ''))?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="imagen" class="form-label"><i class="bi bi-image"></i> Imagen (solo PNG)</label>
                <input type="file" class="form-control" id="imagen" name="imagen" accept=".png,image/png">
                <?php if ($editando && $producto && $producto->getNombreImagen()): ?>
                    <div class="mt-2 d-flex align-items-center gap-3">
                        <img src="<?= htmlspecialchars($producto->getImagen()) ?>" alt="Imagen actual" class="img-admin-thumb">
                        <?php if ($producto->getNombreImagen() !== 'not_found.png'): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="eliminar_imagen" value="1" id="eliminar_imagen">
                            <label class="form-check-label" for="eliminar_imagen">Eliminar imagen</label>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="categoria_principal" class="form-label"><i class="bi bi-tag-fill"></i> Categoría Principal</label>
                <select class="form-select" id="categoria_principal" name="categoria_principal" required>
                    <option value="">Selecciona una categoría...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == ($_POST['categoria_principal'] ?? $categoria_principal_id)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="categoria_secundaria" class="form-label"><i class="bi bi-tags-fill"></i> Categoría Secundaria (Opcional)</label>
                <select class="form-select" id="categoria_secundaria" name="categoria_secundaria">
                    <option value="">-- Ninguna --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == ($_POST['categoria_secundaria'] ?? $categoria_secundaria_id)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success btn-lg btn-tematico"><i class="bi bi-check-circle-fill"></i> <?= $editando ? 'Guardar cambios' : 'Agregar producto' ?></button>
        </div>
    </form>
</div> 