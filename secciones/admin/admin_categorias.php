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
                    <th scope="col">Descripción</th>
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
                            <td><?= htmlspecialchars($categoria['nombre_categoria']) ?></td>
                            <td><?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></td>
                            <td class="text-end">
                                <a href="?sec=admin/agregar_categoria&id=<?= $categoria['id'] ?>" class="btn btn-sm btn-info btn-tematico" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                <a href="?sec=admin/admin_categorias&eliminar=<?= $categoria['id'] ?>" class="btn btn-sm btn-danger btn-tematico" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta categoría?')"><i class="bi bi-trash-fill"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 