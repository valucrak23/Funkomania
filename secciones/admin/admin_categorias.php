<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}
require_once __DIR__ . '/../../clases/ProductoDAO.php';
require_once __DIR__ . '/../../clases/CategoriaDAO.php';
require_once __DIR__ . '/../../clases/Producto.php';
require_once __DIR__ . '/../../config/database.php';

// Cargar categorías
$categoriaDAO = new CategoriaDAO();
$categorias = $categoriaDAO->obtenerTodas();

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
        <?php
            $mensaje = '';
            $tipo_alert = 'alert-success';
            $icono = 'bi-check-circle-fill';
            
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
                case 'no_eliminar_con_productos':
                    $mensaje = 'No se puede eliminar la categoría porque tiene productos asignados. Primero debes quitar los productos de esta categoría.';
                    $tipo_alert = 'alert-danger';
                    $icono = 'bi-exclamation-triangle-fill';
                    break;
                case 'no_eliminar_protegida':
                    $mensaje = 'No se puede eliminar esta categoría porque es una categoría del sistema protegida.';
                    $tipo_alert = 'alert-warning';
                    $icono = 'bi-shield-exclamation-fill';
                    break;
                case 'error':
                    $mensaje = 'Ocurrió un error. Inténtalo de nuevo.';
                    $tipo_alert = 'alert-danger';
                    $icono = 'bi-exclamation-triangle-fill';
                    break;
            }
        ?>
        <div class="alert <?= $tipo_alert ?>" role="alert">
            <i class="bi <?= $icono ?>"></i> 
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover admin-table">
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col" class="col-descripcion">Descripción</th>
                    <th scope="col">Productos</th>
                    <th scope="col" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            <p>No se encontraron categorías.</p>
                            <a href="?sec=admin/crear_categorias_ejemplo" class="btn btn-primary btn-sm">
                                <i class=bibi-plus-circle"></i> Crear categorías de ejemplo
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <?php 
                        $dao = new ProductoDAO();
                        $tiene_productos = $dao->categoriaTieneProductos($categoria['id']);
                        $es_protegida = $categoriaDAO->esProtegida($categoria['id']);
                        $productos_categoria = $tiene_productos ? $dao->obtenerProductosPorCategoria($categoria['id']) : [];
                        ?>
                        <tr>
                            <td data-label="Nombre">
                                <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                                <?php if ($es_protegida): ?>
                                    <span class="badge bg-primary ms-2">
                                        <i class="bi bi-shield-fill"></i> Sistema
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="col-descripcion" data-label="Descripción"><?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></td>
                            <td data-label="Productos">
                                <?php if ($tiene_productos): ?>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        <?= count($productos_categoria) ?> producto(s) asignado(s)
                                    </span>
                                    <div class="productos-lista mt-1">
                                        <?php 
                                        $productos_mostrados = array_slice($productos_categoria, 0, 5);
                                        $total_productos = count($productos_categoria);
                                        ?>
                                        <span class="productos-preview" id="preview-<?= $categoria['id'] ?>">
                                            <?php foreach ($productos_mostrados as $index => $producto): ?>
                                                <?= htmlspecialchars($producto->getNombre()) ?><?= $index < count($productos_mostrados) - 1 ? ', ' : '' ?>
                                            <?php endforeach; ?>
                                            <?php if ($total_productos > 5): ?>
                                                <span class="text-muted">y más...</span>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-info expandir-productos" data-categoria="<?= $categoria['id'] ?>">
                                                    <i class="bi bi-chevron-down"></i> Ver todos
                                                </button>
                                            <?php endif; ?>
                                        </span>
                                        <?php if ($total_productos > 5): ?>
                                            <span class="productos-completos d-none" id="completos-<?= $categoria['id'] ?>">
                                                <?php foreach ($productos_categoria as $index => $producto): ?>
                                                    <?= htmlspecialchars($producto->getNombre()) ?><?= $index < $total_productos - 1 ? ', ' : '' ?>
                                                <?php endforeach; ?>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-secondary contraer-productos" data-categoria="<?= $categoria['id'] ?>">
                                                    <i class="bi bi-chevron-up"></i> Ocultar
                                                </button>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Sin productos
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" data-label="Acciones">
                                <?php if ($es_protegida): ?>
                                    <!-- Sin botones para categoría protegida -->
                                <?php elseif ($tiene_productos): ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-tematico disabled" title="No se puede eliminar - tiene productos asignados">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <a href="?sec=admin/agregar_categoria&id=<?= $categoria['id'] ?>" class="btn btn-sm btn-info btn-tematico" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-danger btn-tematico" title="Eliminar" 
                                            onclick="confirmarEliminarCategoria(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nombre_categoria']) ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <a href="?sec=admin/agregar_categoria&id=<?= $categoria['id'] ?>" class="btn btn-sm btn-info btn-tematico" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.expandir-productos').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var cat = btn.getAttribute('data-categoria');
            document.getElementById('preview-' + cat).style.display = 'none';
            document.getElementById('completos-' + cat).classList.remove('d-none');
        });
    });
    document.querySelectorAll('.contraer-productos').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var cat = btn.getAttribute('data-categoria');
            document.getElementById('completos-' + cat).classList.add('d-none');
            document.getElementById('preview-' + cat).style.display = '';
        });
    });
});
</script> 