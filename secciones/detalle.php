<?php
require_once __DIR__ . '/../clases/ProductoDAO.php';

$dao = new ProductoDAO();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$producto = $dao->obtenerPorId($id);

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        // Si no está logueado, redirigir al login
        header('Location: index.php?sec=auth/login&msg=login_required');
        exit;
    }
    
    $id_producto = intval($_POST['agregar_carrito']);
    if (!isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto] = 1;
    } else {
        $_SESSION['carrito'][$id_producto]++;
    }
    header('Location: ?sec=detalle&id=' . $id . '&msg=agregado');
    exit;
}

$mensaje = isset($_GET['msg']) && $_GET['msg'] === 'agregado' ? '¡Producto agregado al carrito!' : '';
?>

<?php if($producto): ?>
<div class="content-wrapper">
    <?php if ($mensaje): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?=$mensaje?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5 text-center mb-4 mb-lg-0">
            <img src="<?=htmlspecialchars($producto->getImagen())?>" 
                 alt="<?=htmlspecialchars($producto->getNombre())?>" 
                 class="img-fluid rounded-3 shadow-lg" 
                 style="max-height: 500px; border: 3px solid rgba(255,255,255,0.1);">
        </div>
        <div class="col-lg-7 d-flex flex-column justify-content-center">
            <h2 class="mb-3"><?=htmlspecialchars($producto->getNombre())?></h2>
            <p class="lead text-muted" style="font-size: 1.1rem;"><?=htmlspecialchars($producto->getDescripcion())?></p>
            
            <div class="d-flex align-items-center my-3">
                <span class="badge bg-primary fs-6 me-3">
                    <?=htmlspecialchars($producto->getCategorias())?>
                </span>
            </div>
            
            <p class="display-5 fw-bold my-3" style="color: #48bb78;">$<?=number_format($producto->getPrecio(), 2)?></p>

            <div class="d-flex gap-2 mt-4">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <form method="post" action="?sec=detalle&id=<?=$id?>">
                        <input type="hidden" name="agregar_carrito" value="<?=$producto->getId()?>">
                        <button type="submit" class="btn btn-success btn-lg btn-tematico">
                            <i class="bi bi-cart-plus-fill"></i> Agregar al carrito
                        </button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-success btn-lg btn-tematico" 
                            onclick="mostrarModalLogin(<?=$producto->getId()?>, '<?=htmlspecialchars($producto->getNombre())?>')">
                        <i class="bi bi-cart-plus-fill"></i> Agregar al carrito
                    </button>
                <?php endif; ?>
                <a href="?sec=productos" class="btn btn-secondary btn-lg btn-tematico">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de login requerido -->
<div class="modal fade" id="modalLoginRequerido" tabindex="-1" aria-labelledby="modalLoginRequeridoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLoginRequeridoLabel">
                    <i class="bi bi-lock-fill text-warning"></i> Login requerido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Para agregar <strong id="nombreProductoLogin"></strong> al carrito, necesitas iniciar sesión.</p>
                <p class="text-muted small">Una vez que inicies sesión, podrás agregar productos a tu carrito.</p>
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
function mostrarModalLogin(idProducto, nombreProducto) {
    document.getElementById('nombreProductoLogin').textContent = nombreProducto;
    
    const modal = new bootstrap.Modal(document.getElementById('modalLoginRequerido'));
    modal.show();
}
</script>

<?php else: ?>
    <div class="alert alert-danger text-center">
        <h3><i class="bi bi-exclamation-triangle-fill"></i> Producto no encontrado</h3>
        <p>El producto que buscas no existe o fue removido. <a href="?sec=productos" class="alert-link">Volver al catálogo</a>.</p>
    </div>
<?php endif; ?>