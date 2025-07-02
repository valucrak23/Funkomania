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

// Eliminar producto (mover esto antes de cualquier salida)
if (isset($_GET['eliminar'])) {
    $db = Database::getInstance();
    $dao = new ProductoDAO();
    $id = intval($_GET['eliminar']);
    // Borra de la tabla pivote primero
    $db->query("DELETE FROM producto_categoria WHERE producto_id = ?", [$id]);
    // Borra el producto
    $dao->eliminar($id);
    header('Location: ?sec=admin/admin_productos&msg=eliminado');
    exit;
}

$dao = new ProductoDAO();
$db = Database::getInstance();

// Búsqueda por nombre
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if ($busqueda !== '') {
    $resultados = $db->query("SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
        FROM productos p
        LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
        LEFT JOIN categoria c ON pc.categoria_id = c.id
        WHERE p.Nombre LIKE ?
        GROUP BY p.id
        ORDER BY p.Nombre", ["%$busqueda%"])->fetchAll();

    $productos = [];
    foreach ($resultados as $row) {
        $productos[] = new Producto(
            $row['id'],
            $row['Nombre'],
            $row['Descripcion'],
            $row['precio'],
            $row['categorias'],
            0, // stock (si lo usas)
            null, // fecha (si lo usas)
            $row['imagen']
        );
    }
} else {
    $productos = $dao->obtenerTodos();
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-box-seam"></i> Administrar productos</h2>
        <a href="?sec=admin/agregar_producto" class="btn btn-success"><i class="bi bi-plus-circle"></i> Agregar nuevo</a>
    </div>

    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="hidden" name="sec" value="admin/admin_productos">
            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre de producto..." value="<?=htmlspecialchars($busqueda)?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            <?php if ($busqueda): ?>
                <a href="?sec=admin/admin_productos" class="btn btn-secondary">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($msg === 'eliminado'): ?>
        <div class="alert alert-success">Producto eliminado correctamente.</div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Categorías</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron productos.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= $p->getId() ?></td>
                        <td><?= htmlspecialchars($p->getNombre()) ?></td>
                        <td><?= htmlspecialchars(substr($p->getDescripcion(), 0, 80)) . '...' ?></td>
                        <td>$<?= number_format($p->getPrecio(), 2) ?></td>
                        <td><?= htmlspecialchars($p->getCategorias()) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($p->getImagen()) ?>" alt="<?= htmlspecialchars($p->getNombre()) ?>" class="img-admin-thumb">
                        </td>
                        <td>
                            <a href="?sec=admin/agregar_producto&id=<?= $p->getId() ?>" class="btn btn-sm btn-info btn-tematico" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                            <a href="?sec=admin/admin_productos&eliminar=<?= $p->getId() ?>" class="btn btn-sm btn-danger btn-tematico" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 