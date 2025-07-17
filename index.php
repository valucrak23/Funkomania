<?php
ob_start();
session_start();

// Cargar configuración y clases
require_once 'config/database.php';
require_once 'config/rutas.php';
require_once 'clases/Producto.php';
require_once 'clases/ProductoDAO.php';
require_once 'clases/CategoriaDAO.php';
require_once 'clases/UsuarioDAO.php';
require_once 'clases/OrdenDAO.php';
require_once 'clases/Utils.php';
require_once 'controladores/CarritoController.php';
require_once 'controladores/ProductoController.php';
require_once 'controladores/CategoriaController.php';

// Limpiar orden aleatorio si se solicita
if (isset($_GET['refresh'])) {
    unset($_SESSION['productos_orden_aleatorio']);
    $redirect_url = '?sec=' . ($_GET['sec'] ?? 'inicio');
    header('Location: ' . $redirect_url);
    exit;
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    $resultado = CarritoController::agregarProducto(intval($_POST['agregar_carrito']));
    if (isset($resultado['error'])) {
        header('Location: index.php?sec=auth/login&msg=login_required');
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '&msg=agregado');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quitar'])) {
    CarritoController::quitarProducto(intval($_POST['quitar']));
    header('Location: ?sec=carrito');
    exit;
}

if (isset($_GET['quitar'])) {
    CarritoController::quitarProducto(intval($_GET['quitar']));
    header('Location: ?sec=carrito');
    exit;
}

if (isset($_GET['modificar_cantidad'])) {
    $id = intval($_GET['modificar_cantidad']);
    $accion = $_GET['accion'] ?? '';
    CarritoController::modificarCantidad($id, $accion);
    header('Location: ?sec=carrito');
    exit;
}

// Enrutador
$seccion = $_GET['sec'] ?? 'inicio';
$seccionesValidas = require 'config/rutas.php';

// Verificar seguridad para rutas de admin
if (strpos($seccion, 'admin/') === 0 && !Utils::usuarioLogueado()) {
    header('Location: index.php?sec=auth/login');
    exit;
}

// Redirigir si usuario logueado intenta acceder al login
if ($seccion === 'auth/login' && Utils::usuarioLogueado()) {
    header('Location: index.php?sec=admin/admin_productos');
    exit;
}

// Procesar logout
if ($seccion === 'auth/logout') {
    include 'secciones/auth/logout.php';
}

// Procesar acciones de administración
if ($seccion === 'admin/admin_categorias' && isset($_GET['eliminar'])) {
    $resultado = CategoriaController::eliminarCategoria(intval($_GET['eliminar']));
    $mensaje = isset($resultado['error']) ? 'error' : 'eliminada';
    header('Location: ?sec=admin/admin_categorias&msg=' . $mensaje);
    exit;
}

if ($seccion === 'admin/agregar_categoria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = CategoriaController::procesarFormulario($_POST);
    if (isset($resultado['success'])) {
        $mensaje = isset($_POST['id']) ? 'editada' : 'agregada';
        header('Location: ?sec=admin/admin_categorias&msg=' . $mensaje);
    } else {
        // Redirigir con errores
        header('Location: ?sec=admin/agregar_categoria&errores=' . urlencode(serialize($resultado['errores'])));
    }
    exit;
}

if ($seccion === 'admin/agregar_producto' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = ProductoController::procesarFormulario($_POST, $_FILES);
    if (isset($resultado['success'])) {
        $mensaje = isset($_POST['id']) ? 'editado' : 'agregado';
        header('Location: ?sec=admin/admin_productos&msg=' . $mensaje);
    } else {
        // Redirigir con errores
        header('Location: ?sec=admin/agregar_producto&errores=' . urlencode(serialize($resultado['errores'])));
    }
    exit;
}

if ($seccion === 'admin/admin_productos' && isset($_GET['eliminar'])) {
    $resultado = ProductoController::eliminarProducto(intval($_GET['eliminar']));
    $mensaje = isset($resultado['success']) ? 'eliminado' : 'error';
    header('Location: ?sec=admin/admin_productos&msg=' . $mensaje);
    exit;
}

// Determinar página a cargar
$pagina = $seccionesValidas[$seccion] ?? $seccionesValidas['404'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FunkoManía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="img/logofunko.png">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <canvas id="starfield"></canvas>
    
    <header class="navbar navbar-expand-lg navbar-dark bg-dark-transparent sticky-top">
        <div class="container">
            <a class="navbar-brand logo" href="?sec=inicio">
                <img src="img/logofunko.png" alt="Logo Funko" style="height: 110px; margin-right: 10px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="?sec=inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="?sec=productos">Funkos</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCategorias" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categorías
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCategorias">
                            <?php
                                $categoriaDAO = new CategoriaDAO();
                                $categorias_menu = $categoriaDAO->obtenerTodas();
                                foreach ($categorias_menu as $cat_item) {
                                    echo '<li><a class="dropdown-item" href="?sec=productos&cat=' . $cat_item['id'] . '">' . htmlspecialchars($cat_item['nombre_categoria']) . '</a></li>';
                                }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="?sec=contacto">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link" href="?sec=alumno">Alumnos</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="?sec=carrito">
                            <i class="bi bi-cart-fill"></i> Carrito <span class="badge bg-primary rounded-pill"><?= CarritoController::obtenerCantidadItems() ?></span>
                        </a>
                    </li>
                    
                    <?php if (Utils::usuarioLogueado()): ?>
                        <?php if (Utils::esAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownAdmin">
                                    <li><a class="dropdown-item" href="?sec=admin/admin_productos">Gestionar Productos</a></li>
                                    <li><a class="dropdown-item" href="?sec=admin/admin_categorias">Gestionar Categorías</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?sec=historial">Mi Historial</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?sec=auth/logout">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                    <li><a class="dropdown-item" href="?sec=historial">Mi Historial</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="index.php?sec=auth/logout">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?sec=auth/login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <main class="container my-4 flex-grow-1">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logout_first'): ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle-fill"></i> Debes cerrar sesión para acceder al login de administrador.
            </div>
        <?php endif; ?>
        
        <?php
        if (file_exists($pagina)) {
            include $pagina;
        } else {
            include $seccionesValidas['404'];
        }
        ?>
    </main>

    <?php require_once 'secciones/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/fondo-estrellas.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
