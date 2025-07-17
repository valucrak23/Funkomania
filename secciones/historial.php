<?php
require_once __DIR__ . '/../clases/OrdenDAO.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?sec=auth/login&msg=login_required');
    exit;
}

$ordenDAO = new OrdenDAO();
$historial = $ordenDAO->obtenerHistorialUsuario($_SESSION['usuario_id']);
$estadisticas = $ordenDAO->obtenerEstadisticasUsuario($_SESSION['usuario_id']);

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
                <h2><i class="bi bi-clock-history"></i> Mi Historial de Compras</h2>
                <a href="?sec=productos" class="btn btn-primary btn-tematico">
                    <i class="bi bi-shop"></i> Seguir comprando
                </a>
            </div>

            <!-- Estadísticas del usuario -->
            <?php if (!empty($estadisticas) && $estadisticas['total_ordenes'] > 0): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center estadisticas-card">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?= $estadisticas['total_ordenes'] ?></h5>
                                <p class="card-text">Total de compras</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center estadisticas-card">
                            <div class="card-body">
                                <h5 class="card-title text-success">$<?= number_format($estadisticas['total_gastado'], 2) ?></h5>
                                <p class="card-text">Total gastado</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center estadisticas-card">
                            <div class="card-body">
                                <h5 class="card-title text-info">$<?= number_format($estadisticas['promedio_orden'], 2) ?></h5>
                                <p class="card-text">Promedio por orden</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center estadisticas-card">
                            <div class="card-body">
                                <h5 class="card-title text-warning">
                                    <?= $estadisticas['ultima_compra'] ? date('d/m/Y', strtotime($estadisticas['ultima_compra'])) : 'N/A' ?>
                                </h5>
                                <p class="card-text">Última compra</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($historial)): ?>
                <div class="text-center py-5">
                    <div class="alert alert-info">
                        <i class="bi bi-emoji-frown"></i>
                        <h4>No tienes compras aún</h4>
                        <p>¡Comienza a comprar para ver tu historial aquí!</p>
                        <a href="?sec=productos" class="btn btn-primary btn-tematico mt-2">
                            <i class="bi bi-shop"></i> Ver productos
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($historial as $orden): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 historial-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-receipt"></i> Orden #<?= $orden['id'] ?>
                                    </h6>
                                    <span class="badge <?= getEstadoBadgeClass($orden['estado']) ?>">
                                        <?= getEstadoTexto($orden['estado']) ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Fecha:</small><br>
                                            <strong><?= date('d/m/Y H:i', strtotime($orden['fecha_orden'])) ?></strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted">Total:</small><br>
                                            <strong class="text-success">$<?= number_format($orden['total'], 2) ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Productos:</small><br>
                                        <small><?= htmlspecialchars($orden['productos']) ?></small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Dirección de envío:</small><br>
                                        <small>
                                            <?= htmlspecialchars($orden['direccion_envio']) ?>, 
                                            <?= htmlspecialchars($orden['ciudad']) ?> 
                                            (<?= htmlspecialchars($orden['codigo_postal']) ?>)
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?= $orden['total_items'] ?> item(s)
                                        </small>
                                        <a href="?sec=detalle_orden&id=<?= $orden['id'] ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 