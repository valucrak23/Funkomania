<!-- Hero Banner -->
<div class="hero-banner text-center text-white py-5 mb-5">
    <div class="container">
        <h1 class="display-3 fw-bold">Tu Universo de Funkos</h1>
        <p class="lead col-lg-8 mx-auto">
            Desde los héroes más valientes hasta los villanos más icónicos. Encuentra la pieza que falta en tu colección y llévate un pedazo de tus historias favoritas a casa.
        </p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            <a href="?sec=productos" class="btn btn-primary btn-lg px-4 gap-3">Explorar la Colección</a>
            <a href="#destacados" class="btn btn-outline-secondary btn-lg px-4">Ver Destacados</a>
        </div>
    </div>
</div>

<!-- Sección de Productos Destacados -->
<div id="destacados" class="container px-4 py-5">
    <h2 class="pb-2 border-bottom text-center mb-4">Funkos Destacados</h2>
    
    <div class="row row-cols-1 row-cols-lg-4 align-items-stretch g-4 py-5">
        <?php
            $dao = new ProductoDAO();
            $productos_destacados = $dao->obtenerDestacados(4);
            if (!empty($productos_destacados)) {
                foreach($productos_destacados as $producto) {
                    // Reutilizamos la misma estructura de tarjeta de la sección de productos
                    echo '<div class="col">';
                    echo '    <div class="card h-100 text-center">';
                    echo '        <a href="?sec=detalle&id='.$producto->getId().'" class="stretched-link"></a>';
                    echo '        <div class="card-img-container">';
                    echo '            <img src="'.htmlspecialchars($producto->getImagen()).'" class="card-img-top" alt="'.htmlspecialchars($producto->getNombre()).'">';
                    echo '        </div>';
                    echo '        <div class="card-body d-flex flex-column">';
                    echo '            <h5 class="card-title flex-grow-1">'.htmlspecialchars($producto->getNombre()).'</h5>';
                    echo '            <p class="card-text fw-bold fs-5 text-success mb-3">$'.number_format($producto->getPrecio(), 2).'</p>';
                    echo '            <form method="post" class="z-2">';
                    echo '                <input type="hidden" name="agregar_carrito" value="'.$producto->getId().'">';
                    echo '                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cart-plus"></i> Agregar</button>';
                    echo '            </form>';
                    echo '        </div>';
                    echo '    </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info text-center">No hay productos destacados disponibles en este momento.</div></div>';
            }
        ?>
    </div>
</div>

<!-- Call to Action -->
<div class="container">
    <div class="p-5 mb-4 rounded-3 jumbotron-style">
        <div class="container-fluid py-5 text-center">
            <h3 class="display-6 fw-bold">¿No encuentras lo que buscas?</h3>
            <p class="fs-5">Explora nuestras categorías y sumérgete en un mundo de posibilidades. ¡Tu próximo Funko te está esperando!</p>
            <div class="text-center">
                <a href="?sec=productos" class="btn btn-primary btn-lg btn-tematico me-3">
                    <i class="bi bi-shop"></i> Ver catálogo
                </a>
                <a href="?sec=contacto" class="btn btn-outline-light btn-lg btn-tematico">
                    <i class="bi bi-envelope"></i> Contacto
                </a>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-4">
    <a href="?sec=productos" class="btn btn-primary btn-tematico">
        <i class="bi bi-arrow-right"></i> Ver todos los productos
    </a>
</div>
