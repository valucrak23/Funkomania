<?php
require_once __DIR__ . '/../clases/Producto.php';
require_once "clases/ProductoDAO.php";
require_once "clases/CategoriaDAO.php";

$mensaje = isset($_GET['msg']) && $_GET['msg'] === 'agregado' ? '¡Producto agregado al carrito!' : '';

$dao = new ProductoDAO();
$cat_id = isset($_GET['cat']) ? intval($_GET['cat']) : null;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

if ($busqueda) {
    $productos = $dao->buscarPorNombre($busqueda);
    // Si no se encontraron productos por nombre, buscar por nombre de categoría
    if (empty($productos)) {
        $productos = $dao->buscarPorCategoriaNombre($busqueda);
        if (!empty($productos)) {
            $titulo = "Resultados para la categoría: \"" . htmlspecialchars($busqueda) . "\"";
        } else {
            $titulo = "Resultados para: \"" . htmlspecialchars($busqueda) . "\"";
        }
    } else {
        $titulo = "Resultados para: \"" . htmlspecialchars($busqueda) . "\"";
    }
} elseif ($cat_id) {
    $productos = $dao->obtenerPorCategoria($cat_id);
    if ($cat_id == 11) {
        // Categoría "Sin Categoría"
        $titulo = "Sin Categoría";
    } else {
        $categoriaDAO = new CategoriaDAO();
        $categoria = $categoriaDAO->obtenerPorId($cat_id);
        $titulo = $categoria ? htmlspecialchars($categoria['nombre_categoria']) : "Productos";
    }
} else {
    $productos = $dao->obtenerTodos();
    $titulo = "Todos los productos";
}

// Función para obtener la clase CSS según la categoría
function getCategoriaClass($categorias) {
    if (empty($categorias) || $categorias === 'Sin categoría' || $categorias === 'Sin Categoría') {
        return 'card-default';
    }
    
    // Convertir a minúsculas y limpiar
    $categorias_lower = strtolower(trim($categorias));
    
    // Mapeo de categorías a clases CSS
    $categoriaMap = [
        'star wars' => 'card-star-wars',
        'marvel' => 'card-marvel',
        'dc cómics' => 'card-dc-comics',
        'dc comics' => 'card-dc-comics',
        'harry potter' => 'card-harry-potter',
        'señor de los anillos' => 'card-senor-anillos',
        'senor de los anillos' => 'card-senor-anillos',
        'series tv' => 'card-series',
        'series' => 'card-series',
        'películas' => 'card-peliculas',
        'peliculas' => 'card-peliculas',
        'dibujos animados' => 'card-dibujos-animados',
        'videojuegos' => 'card-videojuegos',
        'música' => 'card-musica',
        'musica' => 'card-musica',
        'disney' => 'card-disney',
        'ediciones especiales' => 'card-ediciones-especiales',
    ];
    
    // Buscar coincidencias
    foreach ($categoriaMap as $keyword => $class) {
        if (strpos($categorias_lower, $keyword) !== false) {
            return $class;
        }
    }
    
    // Si no encuentra coincidencia, generar una clase basada en el hash del nombre
    $hash = crc32($categorias_lower);
    $colorIndex = abs($hash) % 8; // 8 colores diferentes
    return "card-dynamic-$colorIndex";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-center mb-0"><?=$titulo?></h2>
    <?php if (!$busqueda && !$cat_id): ?>
        <a href="?sec=productos&refresh=1" class="btn btn-outline-secondary btn-sm" title="Refrescar orden aleatorio">
            <i class="bi bi-arrow-clockwise"></i> Refrescar
        </a>
    <?php endif; ?>
</div>

<!-- Formulario de búsqueda -->
<div class="row justify-content-center mb-4">
    <div class="col-md-8">
        <form method="get" class="d-flex">
            <input type="hidden" name="sec" value="productos">
            <?php if ($cat_id): ?>
                <input type="hidden" name="cat" value="<?=$cat_id?>">
            <?php endif; ?>
            <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por nombre..." value="<?=htmlspecialchars($busqueda)?>">
            <button type="submit" class="btn btn-primary btn-tematico"><i class="bi bi-search"></i></button>
            <?php if ($busqueda || $cat_id): ?>
                <a href="?sec=productos" class="btn btn-secondary btn-tematico ms-2">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-success text-center"><i class="bi bi-check-circle-fill"></i> <?=$mensaje?></div>
<?php endif; ?>

<?php if (empty($productos)): ?>
    <div class="text-center py-5">
        <div class="alert alert-info" style="background: rgba(26, 32, 44, 0.7); border-color: rgba(255,255,255,0.1);">
            <h4><i class="bi bi-emoji-frown"></i> No se encontraron productos</h4>
            <p>Intenta con otros términos de búsqueda o explora nuestras categorías.</p>
            <a href="?sec=productos" class="btn btn-primary btn-tematico mt-2">Ver todos los productos</a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach($productos as $producto): ?>
        <div class="col">
            <div class="card h-100 text-center <?=getCategoriaClass($producto->getCategorias())?>">
                <a href="?sec=detalle&id=<?=$producto->getId()?>" class="stretched-link"></a>
                <div class="card-img-container">
                    <img src="<?=htmlspecialchars($producto->getImagen())?>" class="card-img-top" alt="<?=htmlspecialchars($producto->getNombre())?>">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title flex-grow-1"><?=htmlspecialchars($producto->getNombre())?></h5>
                    <p class="card-text fw-bold fs-5 text-success mb-3">$<?=number_format($producto->getPrecio(), 2)?></p>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <form method="post" class="z-2">
                            <input type="hidden" name="agregar_carrito" value="<?=$producto->getId()?>">
                            <button type="submit" class="btn btn-primary btn-tematico w-100"><i class="bi bi-cart-plus"></i> Agregar</button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary btn-tematico w-100" 
                                onclick="mostrarModalLogin(<?=$producto->getId()?>, '<?=htmlspecialchars($producto->getNombre())?>')">
                            <i class="bi bi-cart-plus"></i> Agregar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

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
