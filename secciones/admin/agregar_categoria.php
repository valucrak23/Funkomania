<?php
session_start();
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}
// Toda la lógica de carga y guardado se maneja en index.php
// Este archivo solo muestra el formulario.
// Las variables $editando, $categoria, y $errores vienen de index.php
?>
<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tag-fill"></i> <?= $editando ? 'Editar categoría' : 'Agregar nueva categoría' ?></h2>
        <a href="?sec=admin/admin_categorias" class="btn btn-secondary btn-tematico"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="?sec=admin/agregar_categoria<?= $editando ? '&id=' . htmlspecialchars($categoria['id']) : '' ?>" class="admin-form-container">
        <?php if ($editando): ?><input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id']) ?>"><?php endif; ?>
        
        <div class="mb-3">
            <label for="nombre" class="form-label"><i class="bi bi-tag-fill"></i> Nombre de la Categoría</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? ($categoria['nombre_categoria'] ?? '')) ?>">
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label"><i class="bi bi-card-text"></i> Descripción (Opcional)</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($_POST['descripcion'] ?? ($categoria['descripcion'] ?? '')) ?></textarea>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success btn-lg btn-tematico"><i class="bi bi-check-circle-fill"></i> <?= $editando ? 'Guardar cambios' : 'Agregar categoría' ?></button>
        </div>
    </form>
</div> 