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
                        </td>
                        <td data-label="Precio">$<?=number_format($producto->getPrecio(), 2)?></td>
                        <td data-label="Cantidad">
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= $cantidad > 1 ? '?sec=carrito&modificar_cantidad=' . intval($id) . '&accion=disminuir' : '#' ?>" 
                                   class="btn btn-sm btn-outline-secondary btn-tematico <?= $cantidad <= 1 ? 'disabled' : '' ?>"
                                   <?= $cantidad <= 1 ? 'onclick="return false;"' : '' ?>>
                                    <i class="bi bi-dash"></i>
                                </a>
                                <span class="badge bg-primary fs-6 px-3"><?=intval($cantidad)?></span>
                                <a href="<?= $cantidad < 3 ? '?sec=carrito&modificar_cantidad=' . intval($id) . '&accion=aumentar' : '#' ?>" 
                                   class="btn btn-sm btn-outline-secondary btn-tematico <?= $cantidad >= 3 ? 'disabled' : '' ?>"
                                   <?= $cantidad >= 3 ? 'onclick="return false;"' : '' ?>>
                                    <i class="bi bi-plus"></i>
                                </a>
                            </div>
                        </td>
                        <td data-label="Subtotal"><strong>$<?=number_format($subtotal, 2)?></strong></td>
                        <td data-label="Acciones">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger btn-sm btn-tematico" 
                                        onclick="confirmarQuitarProducto(<?=intval($id)?>, '<?=htmlspecialchars($producto->getNombre())?>')">
                                    <i class="bi bi-trash"></i> Quitar
                                </button>
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
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="?sec=checkout" class="btn btn-success btn-tematico btn-lg">
                    <i class="bi bi-credit-card"></i> Proceder al pago
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-success btn-tematico btn-lg" 
                        onclick="mostrarModalLoginCheckout()">
                    <i class="bi bi-credit-card"></i> Proceder al pago
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de login requerido para checkout -->
<div class="modal fade" id="modalLoginCheckout" tabindex="-1" aria-labelledby="modalLoginCheckoutLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLoginCheckoutLabel">
                    <i class="bi bi-lock-fill text-warning"></i> Login requerido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Para proceder al pago, necesitas iniciar sesión.</p>
                <p class="text-muted small">Una vez que inicies sesión, podrás completar tu compra.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <a href="?sec=auth/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarModalLoginCheckout() {
    const modal = new bootstrap.Modal(document.getElementById('modalLoginCheckout'));
    modal.show();
}

function confirmarQuitarProducto(id, nombre) {
    document.getElementById('nombreProductoQuitar').textContent = nombre;
    document.getElementById('btnConfirmarQuitarProducto').href = '?sec=carrito&quitar=' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('modalQuitarProducto'));
    modal.show();
}
</script>

<!-- Modal de confirmación para quitar producto del carrito -->
<div class="modal fade" id="modalQuitarProducto" tabindex="-1" aria-labelledby="modalQuitarProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQuitarProductoLabel">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Confirmar eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres quitar <strong id="nombreProductoQuitar"></strong> del carrito?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <a href="#" id="btnConfirmarQuitarProducto" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Quitar
                </a>
            </div>
        </div>
    </div>
</div> 