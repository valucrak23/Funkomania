<?php
require_once __DIR__ . '/../clases/ProductoDAO.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?sec=auth/login&msg=login_required');
    exit;
}

// Verificar si hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    header('Location: index.php?sec=carrito&msg=carrito_vacio');
    exit;
}

$dao = new ProductoDAO();
$productos_carrito = [];
$total = 0;

// Obtener productos del carrito
foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    $producto = $dao->obtenerPorId($id_producto);
    if ($producto) {
        $productos_carrito[] = [
            'producto' => $producto,
            'cantidad' => $cantidad,
            'subtotal' => $producto->getPrecio() * $cantidad
        ];
        $total += $producto->getPrecio() * $cantidad;
    }
}

// Procesar el formulario de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = [];
    
    // Validar datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');
    
    // Datos de la tarjeta
    $numero_tarjeta = preg_replace('/\s+/', '', $_POST['numero_tarjeta'] ?? '');
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $titular = trim($_POST['titular'] ?? '');
    
    // Validaciones
    if (empty($nombre)) $errores[] = 'El nombre es obligatorio.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email es obligatorio y debe ser válido.';
    if (empty($telefono)) $errores[] = 'El teléfono es obligatorio.';
    if (empty($direccion)) $errores[] = 'La dirección es obligatoria.';
    if (empty($ciudad)) $errores[] = 'La ciudad es obligatoria.';
    if (empty($codigo_postal)) $errores[] = 'El código postal es obligatorio.';
    
    // Validaciones de tarjeta
    if (empty($numero_tarjeta) || strlen($numero_tarjeta) < 13) $errores[] = 'El número de tarjeta es obligatorio y debe tener al menos 13 dígitos.';
    if (empty($fecha_vencimiento)) $errores[] = 'La fecha de vencimiento es obligatoria.';
    if (empty($cvv) || strlen($cvv) < 3) $errores[] = 'El CVV es obligatorio y debe tener al menos 3 dígitos.';
    if (empty($titular)) $errores[] = 'El nombre del titular es obligatorio.';
    
    if (empty($errores)) {
        // Aquí iría la lógica de procesamiento del pago
        // Por ahora simulamos una compra exitosa
        
        // Limpiar carrito después de la compra
        unset($_SESSION['carrito']);
        
        // Redirigir a página de confirmación
        header('Location: index.php?sec=checkout&msg=compra_exitosa');
        exit;
    }
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-8">
            <h2 class="mb-4"><i class="bi bi-credit-card-fill"></i> Confirmar Compra</h2>
            
            <?php if ($msg === 'compra_exitosa'): ?>
                <div class="alert alert-success text-center">
                    <i class="bi bi-check-circle-fill"></i> 
                    <strong>¡Compra realizada con éxito!</strong><br>
                    Tu pedido ha sido procesado correctamente. Recibirás un email de confirmación.
                    <div class="mt-3">
                        <a href="?sec=productos" class="btn btn-primary btn-tematico">Seguir comprando</a>
                    </div>
                </div>
            <?php else: ?>
                
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="?sec=checkout" class="checkout-form">
                    <!-- Información de contacto -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-person-fill"></i> Información de Contacto</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono *</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección de envío -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-geo-alt-fill"></i> Dirección de Envío</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección *</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad *</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                                           value="<?= htmlspecialchars($_POST['ciudad'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="codigo_postal" class="form-label">Código Postal *</label>
                                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" 
                                           value="<?= htmlspecialchars($_POST['codigo_postal'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de pago -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="bi bi-credit-card-fill"></i> Información de Pago</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="numero_tarjeta" class="form-label">Número de Tarjeta *</label>
                                <input type="text" class="form-control" id="numero_tarjeta" name="numero_tarjeta" 
                                       placeholder="1234 5678 9012 3456" maxlength="19" 
                                       value="<?= htmlspecialchars($_POST['numero_tarjeta'] ?? '') ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titular" class="form-label">Nombre del Titular *</label>
                                    <input type="text" class="form-control" id="titular" name="titular" 
                                           value="<?= htmlspecialchars($_POST['titular'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="fecha_vencimiento" class="form-label">Vencimiento *</label>
                                    <input type="text" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                           placeholder="MM/AA" maxlength="5" 
                                           value="<?= htmlspecialchars($_POST['fecha_vencimiento'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="cvv" class="form-label">CVV *</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" 
                                           placeholder="123" maxlength="4" 
                                           value="<?= htmlspecialchars($_POST['cvv'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg btn-tematico">
                            <i class="bi bi-check-circle-fill"></i> Confirmar Compra
                        </button>
                        <a href="?sec=carrito" class="btn btn-secondary btn-lg btn-tematico ms-2">
                            <i class="bi bi-arrow-left"></i> Volver al Carrito
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Resumen del pedido -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header">
                    <h5><i class="bi bi-cart-check-fill"></i> Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($productos_carrito as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($item['producto']->getImagen()) ?>" 
                                     alt="<?= htmlspecialchars($item['producto']->getNombre()) ?>" 
                                     class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($item['producto']->getNombre()) ?></h6>
                                    <small class="text-muted">Cantidad: <?= $item['cantidad'] ?></small>
                                </div>
                            </div>
                            <span class="fw-bold">$<?= number_format($item['subtotal'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Total</h5>
                        <h5 class="mb-0 text-success">$<?= number_format($total, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Formatear número de tarjeta
document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Formatear fecha de vencimiento
document.getElementById('fecha_vencimiento').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Solo números para CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script> 