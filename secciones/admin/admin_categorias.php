<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}
require_once __DIR__ . '/../../clases/ProductoDAO.php';
require_once __DIR__ . '/../../clases/Producto.php';
require_once __DIR__ . '/../../config/database.php';

// Eliminar categoría (mover esto antes de cualquier salida)
if (isset($_GET['eliminar'])) {
    $db = Database::getInstance();
    $id = intval($_GET['eliminar']);
    $db->query("DELETE FROM categoria WHERE id = ?", [$id]);
    header('Location: ?sec=admin/admin_categorias&msg=eliminado');
    exit;
}
// Esta página será incluida por index.php, que ya tiene el DAO.
// Las categorías se cargan en la lógica de admin_categorias en index.php.
?>
<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags-fill"></i> Gestionar Categorías</h2>
        <a href="?sec=admin/agregar_categoria" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Nueva Categoría</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle-fill"></i> 
            <?php
                $mensaje = '';
                switch ($_GET['msg']) {
                    case 'agregada':
                        $mensaje = 'Categoría agregada exitosamente.';
                        break;
                    case 'editada':
                        $mensaje = 'Categoría actualizada exitosamente.';
                        break;
                    case 'eliminada':
                        $mensaje = 'Categoría eliminada exitosamente.';
                        break;
                    case 'error':
                        $mensaje = 'Ocurrió un error. Inténtalo de nuevo.';
                        break;
                }
                echo htmlspecialchars($mensaje);
            ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover admin-table">
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col" class="col-descripcion">Descripción</th>
                    <th scope="col" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No se encontraron categorías.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td data-label="Nombre"><?= htmlspecialchars($categoria['nombre_categoria']) ?></td>
                            <td class="col-descripcion" data-label="Descripción"><?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></td>
                            <td class="text-end" data-label="Acciones">
                                <a href="?sec=admin/agregar_categoria&id=<?= $categoria['id'] ?>" class="btn btn-sm btn-info btn-tematico" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                <button type="button" class="btn btn-sm btn-danger btn-tematico" title="Eliminar" 
                                        onclick="confirmarEliminarCategoria(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nombre_categoria']) ?>')">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de confirmación para eliminar categoría -->
<div class="modal fade" id="modalEliminarCategoria" tabindex="-1" aria-labelledby="modalEliminarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarCategoriaLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger"></i> Confirmar eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar la categoría <strong id="nombreCategoriaEliminar"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <a href="#" id="btnConfirmarEliminarCategoria" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminarCategoria(id, nombre) {
    document.getElementById('nombreCategoriaEliminar').textContent = nombre;
    document.getElementById('btnConfirmarEliminarCategoria').href = '?sec=admin/admin_categorias&eliminar=' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEliminarCategoria'));
    modal.show();
}
</script> 