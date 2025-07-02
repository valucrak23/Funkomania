<?php
require_once __DIR__ . '/../clases/ProductoDAO.php';


$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
$dao = new ProductoDAO();
$productos = $dao->obtenerTodos();
$total = 0;

function buscarProducto($productos, $id) {
    foreach ($productos as $p) {
        if ($p->getId() == $id) return $p;
    }
    return null;
}
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cart-fill"></i> Carrito de compras</h2>
        <a href="?sec=productos" class="btn btn-primary btn-tematico"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
    </div>

    <?php if (empty($carrito)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Tu carrito está vacío. 
            <a href="?sec=productos" class="alert-link">¡Explora nuestros productos!</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th class="col-imagen">Imagen</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($carrito as $id => $cantidad): 
                    $producto = buscarProducto($productos, $id);
                    if (!$producto) continue;
                    $subtotal = $producto->getPrecio() * $cantidad;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td class="col-imagen" data-label="Imagen">
                            <?php if ($producto->getImagen()): ?>
                                <img src="<?=htmlspecialchars($producto->getImagen())?>" 
                                     alt="<?=htmlspecialchars($producto->getNombre())?>" 
                                     class="img-admin-thumb">
                            <?php else: ?>
                                <div class="img-admin-thumb bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td data-label="Producto">
                            <strong><?=htmlspecialchars($producto->getNombre())?></strong>
                            <br><small class="text-muted col-descripcion"><?=htmlspecialchars($producto->getDescripcion())?></small>
                        </td>
                        <td data-label="Precio">$<?=number_format($producto->getPrecio(), 2)?></td>
                        <td data-label="Cantidad">
                            <span class="badge bg-primary fs-6"><?=intval($cantidad)?></span>
                        </td>
                        <td data-label="Subtotal"><strong>$<?=number_format($subtotal, 2)?></strong></td>
                        <td data-label="Acciones">
                            <div class="d-flex gap-2">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="quitar" value="<?=intval($id)?>">
                                    <button type="submit" class="btn btn-danger btn-sm btn-tematico" onclick="return confirm('¿Estás seguro de que quieres quitar este producto del carrito?')">
                                        <i class="bi bi-trash"></i> Quitar
                                    </button>
                                </form>
                                <a href="?sec=detalle&id=<?=intval($id)?>" class="btn btn-info btn-sm btn-tematico">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="4" class="text-end fs-5">Total:</th>
                        <th colspan="2" class="fs-4 text-success">$<?=number_format($total, 2)?></th>
                    </tr>
                </tfoot>
            </table>
            <!-- Total fuera de la tabla en móvil -->
            <div class="carrito-total-movil d-md-none text-end mt-3">
                <span class="fs-5">Total:</span>
                <span class="fs-4 text-success fw-bold">$<?=number_format($total, 2)?></span>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="?sec=productos" class="btn btn-primary btn-tematico">
                <i class="bi bi-arrow-left"></i> Seguir comprando
            </a>
            <button class="btn btn-success btn-tematico btn-lg" disabled>
                <i class="bi bi-credit-card"></i> Proceder al pago
            </button>
        </div>
    <?php endif; ?>
</div> 