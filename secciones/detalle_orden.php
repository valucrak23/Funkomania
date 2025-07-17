<?php
require_once __DIR__ . '/../clases/OrdenDAO.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?sec=auth/login&msg=login_required');
    exit;
}

$orden_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orden_id) {
    header('Location: index.php?sec=historial&msg=orden_no_encontrada');
    exit;
}

$ordenDAO = new OrdenDAO();
$orden = $ordenDAO->obtenerOrdenPorId($orden_id, $_SESSION['usuario_id']);

if (!$orden) {
    header('Location: index.php?sec=historial&msg=orden_no_encontrada');
    exit;
}

$detalles = $ordenDAO->obtenerDetalleOrden($orden_id, $_SESSION['usuario_id']);

// Función para obtener el color del badge según el estado
function getEstadoBadgeClass($estado) {
    switch ($estado) {
        case 'pendiente': return 'bg-warning';
        case 'confirmada': return 'bg-info';
        case 'enviada': return 'bg-primary';
        case 'entregada': return 'bg-success';
        case 'cancelada': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Función para obtener el texto del estado
function getEstadoTexto($estado) {
    switch ($estado) {
        case 'pendiente': return 'Pendiente';
        case 'confirmada': return 'Confirmada';
        case 'enviada': return 'Enviada';
        case 'entregada': return 'Entregada';
        case 'cancelada': return 'Cancelada';
        default: return 'Desconocido';
    }
}
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-receipt"></i> Detalle de Orden #<?= $orden['id'] ?></h2>
                <div>
                    <a href="?sec=historial" class="btn btn-outline-secondary btn-tematico me-2">
                        <i class="bi bi-arrow-left"></i> Volver al historial
                    </a>
                    <a href="?sec=productos" class="btn btn-primary btn-tematico">
                        <i class="bi bi-shop"></i> Seguir comprando
                    </a>
                </div>
            </div>

            <!-- Información de la orden -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Información de la Orden</h5>
                            <span class="badge <?= getEstadoBadgeClass($orden['estado']) ?> fs-6">
                                <?= getEstadoTexto($orden['estado']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Fecha de orden:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($orden['fecha_orden'])) ?></p>
                                    
                                    <p><strong>Método de pago:</strong><br>
                                    Tarjeta terminada en <?= $orden['ultimos_digitos_tarjeta'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong><br>
                                    <span class="text-success fs-5">$<?= number_format($orden['total'], 2) ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de envío -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Información de Envío</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($orden['nombre_cliente']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($orden['email_cliente']) ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($orden['telefono_cliente']) ?></p>
                            <p><strong>Dirección:</strong><br>
                            <?= htmlspecialchars($orden['direccion_envio']) ?><br>
                            <?= htmlspecialchars($orden['ciudad']) ?>, <?= htmlspecialchars($orden['codigo_postal']) ?></p>
                        </div>
                    </div>

                    <!-- Productos de la orden -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($detalles as $item): ?>
                                <div class="d-flex align-items-center mb-3 p-3 detalle-orden-item rounded">
                                    <img src="<?= htmlspecialchars($item['imagen']) ?>" 
                                         alt="<?= htmlspecialchars($item['nombre_producto']) ?>" 
                                         class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['nombre_producto']) ?></h6>
                                        <small class="text-muted">
                                            Cantidad: <?= $item['cantidad'] ?> | 
                                            Precio unitario: $<?= number_format($item['precio_unitario'], 2) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-success">$<?= number_format($item['subtotal'], 2) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Resumen lateral -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 100px;">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen</h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $total_items = 0;
                            foreach ($detalles as $item) {
                                $total_items += $item['cantidad'];
                            }
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total de productos:</span>
                                <strong><?= $total_items ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>$<?= number_format($orden['total'], 2) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío:</span>
                                <strong>Gratis</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fs-5">Total:</span>
                                <strong class="fs-5 text-success">$<?= number_format($orden['total'], 2) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 