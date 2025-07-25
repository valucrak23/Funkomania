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

    <?php 
    $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
    $orden_id = isset($_GET['orden_id']) ? intval($_GET['orden_id']) : 0;
    
    if (empty($carrito)): 
        if ($msg === 'compra_exitosa' && $orden_id > 0): ?>
            <!-- Mensaje de confirmación de compra exitosa -->
            <div class="alert alert-success border-success text-center mb-4 success-message" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 2px solid #28a745; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem; animation: bounce 1s ease-in-out;"></i>
                </div>
                <h3 class="text-success mb-3 fw-bold">¡Compra Realizada con Éxito!</h3>
                <p class="mb-4 fs-5">Tu pedido ha sido procesado correctamente y está siendo preparado.</p>
                
                <div class="alert alert-light border d-inline-block mb-4" style="background: rgba(255,255,255,0.8);">
                    <div class="row text-start">
                        <div class="col-md-6">
                            <strong class="text-primary">Número de orden:</strong><br>
                            <span class="fs-4 fw-bold text-success">#<?= $orden_id ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-primary">Fecha:</strong><br>
                            <span class="fs-5"><?= date('d/m/Y H:i') ?></span><br>
                            <strong class="text-primary">Estado:</strong><br>
                            <span class="fs-5 text-success">Procesado</span>
                        </div>
                    </div>
                </div>
                
                <p class="text-muted mb-4">
                    <i class="bi bi-envelope-check"></i> 
                    Recibirás un email de confirmación con los detalles de tu pedido.
                </p>
                
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="?sec=productos" class="btn btn-primary btn-lg btn-tematico">
                        <i class="bi bi-shop"></i> Seguir Comprando
                    </a>
                    <a href="?sec=historial" class="btn btn-outline-primary btn-lg btn-tematico">
                        <i class="bi bi-clock-history"></i> Ver Mi Historial
                    </a>
                </div>
            </div>
            
            <style>
            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% {
                    transform: translateY(0);
                }
                40% {
                    transform: translateY(-10px);
                }
                60% {
                    transform: translateY(-5px);
                }
            }
            
            .success-message {
                animation: slideInDown 0.5s ease-out;
            }
            
            @keyframes slideInDown {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            </style>
            
            <script>
            // Mostrar mensaje de éxito con efecto
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.querySelector('.alert-success');
                if (successMessage) {
                    successMessage.classList.add('success-message');
                    
                    // Hacer scroll suave hacia el mensaje
                    successMessage.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Agregar efecto de confeti virtual (partículas)
                    createConfetti();
                }
            });
            
            // Función para crear efecto de confeti
            function createConfetti() {
                const colors = ['#28a745', '#20c997', '#17a2b8', '#6f42c1', '#e83e8c'];
                const confettiCount = 50;
                
                for (let i = 0; i < confettiCount; i++) {
                    setTimeout(() => {
                        const confetti = document.createElement('div');
                        confetti.style.position = 'fixed';
                        confetti.style.left = Math.random() * 100 + 'vw';
                        confetti.style.top = '-10px';
                        confetti.style.width = '8px';
                        confetti.style.height = '8px';
                        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                        confetti.style.borderRadius = '50%';
                        confetti.style.pointerEvents = 'none';
                        confetti.style.zIndex = '9999';
                        confetti.style.animation = 'confettiFall 3s linear forwards';
                        
                        document.body.appendChild(confetti);
                        
                        // Remover confeti después de la animación
                        setTimeout(() => {
                            if (confetti.parentNode) {
                                confetti.parentNode.removeChild(confetti);
                            }
                        }, 3000);
                    }, i * 50);
                }
            }
            </script>
            
            <style>
            @keyframes confettiFall {
                0% {
                    transform: translateY(-10px) rotate(0deg);
                    opacity: 1;
                }
                100% {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
            </style>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tu carrito está vacío. 
                <a href="?sec=productos" class="alert-link">¡Explora nuestros productos!</a>
            </div>
        <?php endif; ?>
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