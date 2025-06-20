<?php
// Iniciar la sesión en cada carga de página
session_start();

// Cargar la configuración y las clases base
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'clases/Producto.php';
require_once 'clases/ProductoDAO.php';

// --- Lógica de Carrito ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    $id_producto = intval($_POST['agregar_carrito']);
    if (!isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto] = 1;
    } else {
        $_SESSION['carrito'][$id_producto]++;
    }
    // Redirección para evitar reenvío de formulario
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '&msg=agregado');
    exit;
}

// --- Enrutador de Secciones ---
$seccion = $_GET['sec'] ?? 'inicio';
$seccionesValidas = [
    'inicio' => 'secciones/inicio.php',
    'productos' => 'secciones/productos.php',
    'detalle' => 'secciones/detalle.php',
    'contacto' => 'secciones/contacto.php',
    'contacto_procesar' => 'secciones/contacto_procesar.php',
    'carrito' => 'secciones/carrito.php',
    'alumno' => 'secciones/alumno.php',
    '404' => 'secciones/404.php',
    // Rutas de Admin
    'admin/admin_productos' => 'secciones/admin/admin_productos.php',
    'admin/agregar_producto' => 'secciones/admin/agregar_producto.php',
    'admin/admin_categorias' => 'secciones/admin/admin_categorias.php',
    'admin/agregar_categoria' => 'secciones/admin/agregar_categoria.php',
    // Rutas de Autenticación
    'auth/login' => 'secciones/auth/login.php',
    'auth/logout' => 'secciones/auth/logout.php',
];

// --- Lógica de Seguridad para Rutas de Admin ---
if (strpos($seccion, 'admin/') === 0) {
    // Si la sección es de admin, verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        // Si no está logueado, redirigir a la página de login
        header('Location: index.php?sec=auth/login');
        exit;
    }
}

// Redirigir si un usuario logueado intenta acceder a la página de login
if ($seccion === 'auth/login' && isset($_SESSION['usuario_id'])) {
    header('Location: index.php?sec=admin/admin_productos');
    exit;
}

// --- Lógica de Logout ---
if ($seccion === 'auth/logout') {
    include 'secciones/auth/logout.php';
}

// --- Lógica de Gestión de Categorías (Refactorizada) ---
if ($seccion === 'admin/admin_categorias') {
    $dao = new ProductoDAO();
    if (isset($_GET['eliminar'])) {
        $id_eliminar = intval($_GET['eliminar']);
        if ($dao->eliminarCategoria($id_eliminar)) {
            header('Location: ?sec=admin/admin_categorias&msg=eliminada');
        } else {
            header('Location: ?sec=admin/admin_categorias&msg=error');
        }
        exit;
    }
    $categorias = $dao->obtenerCategorias();

} elseif ($seccion === 'admin/agregar_categoria') {
    $dao = new ProductoDAO();
    $errores = [];
    $editando = false;
    $categoria = ['id' => null, 'nombre_categoria' => '', 'descripcion' => ''];

    // Cargar datos para editar (solo en GET)
    if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $editando = true;
        $categoria = $dao->obtenerCategoriaPorId(intval($_GET['id']));
        if (!$categoria) {
            header('Location: ?sec=admin/admin_categorias&msg=error');
            exit;
        }
    }

    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        
        if ($id) { // Si hay ID, estamos editando
            $editando = true;
            $categoria['id'] = $id; // Mantener ID para el action del form si hay error
        }
        
        if (empty($nombre)) {
            $errores[] = 'El nombre de la categoría es obligatorio.';
        }

        if (empty($errores)) {
            $success = $id 
                ? $dao->actualizarCategoria($id, $nombre, $descripcion)
                : $dao->insertarCategoria($nombre, $descripcion);
            
            if ($success) {
                $msg = $id ? 'editada' : 'agregada';
                header("Location: ?sec=admin/admin_categorias&msg=$msg");
                exit;
            } else {
                $errores[] = 'Ocurrió un error al guardar en la base de datos.';
            }
        }
    }
}

// --- Lógica de Formularios de Administración (PRODUCTOS) ---
if ($seccion === 'admin/agregar_producto') {
    $dao = new ProductoDAO();
    $db = Database::getInstance();
    $categorias = $dao->obtenerCategorias();

    $editando = false;
    $producto = null;
    $categoria_principal_id = null;
    $categoria_secundaria_id = null;
    $errores = [];

    // Lógica para cargar datos si estamos editando
    if (isset($_GET['id'])) {
        $editando = true;
        $id = intval($_GET['id']);
        $producto = $dao->obtenerPorId($id);
        
        $stmt = $db->query("SELECT categoria_id FROM producto_categoria WHERE producto_id = ?", [$id]);
        $categorias_producto = array_column($stmt->fetchAll(), 'categoria_id');

        if (!empty($categorias_producto)) {
            $categoria_principal_id = array_shift($categorias_producto);
            if (!empty($categorias_producto)) {
                $categoria_secundaria_id = array_shift($categorias_producto);
            }
        }
    }

    // Lógica para procesar el formulario si se envía
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        
        $id_principal = $_POST['categoria_principal'] ?? null;
        $id_secundaria = $_POST['categoria_secundaria'] ?? null;
        $cats_ids = [];
        if (!empty($id_principal)) { $cats_ids[] = $id_principal; }
        if (!empty($id_secundaria) && $id_secundaria != $id_principal) { $cats_ids[] = $id_secundaria; }
        $cats_ids = array_unique($cats_ids);

        if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
        if ($descripcion === '') $errores[] = 'La descripción es obligatoria.';
        if ($precio <= 0) $errores[] = 'El precio debe ser mayor a 0.';
        if (empty($id_principal)) $errores[] = 'La categoría principal es obligatoria.';
        if ($id_principal == $id_secundaria && !empty($id_principal)) {
            $errores[] = 'La categoría secundaria no puede ser igual a la principal.';
        }

        $imagen_final = ($editando && $producto) ? $producto->getNombreImagen() : null;

        if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] === '1') {
            $imagen_a_borrar = __DIR__ . '/img/' . $producto->getNombreImagen();
            if (file_exists($imagen_a_borrar) && $producto->getNombreImagen() !== 'not_found.png') {
                unlink($imagen_a_borrar);
            }
            $imagen_final = null;
        } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['imagen']['tmp_name'];
            $nombreOriginal = $_FILES['imagen']['name'];
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            
            if ($extension !== 'png') {
                $errores[] = 'Solo se permiten archivos PNG.';
            } else {
                $nombreProducto = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombre);
                $nombreProducto = preg_replace('/\s+/', '_', trim($nombreProducto));
                $nombreProducto = strtolower($nombreProducto);
                $nombreArchivo = $nombreProducto . '.png';
                $destino = __DIR__ . '/img/' . $nombreArchivo;
                
                if (move_uploaded_file($tmp, $destino)) {
                    $imagen_final = $nombreArchivo;
                } else {
                    $errores[] = 'Error al subir la imagen.';
                }
            }
        }

        if (empty($errores)) {
            if ($id) { // Actualizar
                $producto_actualizado = new Producto($id, $nombre, $descripcion, $precio, '', 0, null, $imagen_final);
                $dao->actualizar($producto_actualizado);
                $db->query("DELETE FROM producto_categoria WHERE producto_id = ?", [$id]);
                foreach ($cats_ids as $catid) {
                    $db->query("INSERT INTO producto_categoria (producto_id, categoria_id) VALUES (?, ?)", [$id, $catid]);
                }
                header('Location: ?sec=admin/admin_productos&msg=editado');
                exit;
            } else { // Insertar
                $producto_nuevo = new Producto(null, $nombre, $descripcion, $precio, '', 0, null, $imagen_final);
                $dao->insertar($producto_nuevo);
                $id = $db->getConnection()->lastInsertId();
                foreach ($cats_ids as $catid) {
                    $db->query("INSERT INTO producto_categoria (producto_id, categoria_id) VALUES (?, ?)", [$id, $catid]);
                }
                header('Location: ?sec=admin/admin_productos&msg=agregado');
                exit;
            }
        }
    }
}

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
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <canvas id="starfield"></canvas>
    
    <header class="navbar navbar-expand-lg navbar-dark bg-dark-transparent sticky-top">
        <div class="container">
            <a class="navbar-brand logo" href="?sec=inicio">
                <img src="img/funko_pop_loki.png" alt="Logo Funko" style="height: 40px; margin-right: 10px;">
                FunkoManía
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
                                $dao = new ProductoDAO();
                                $categorias_menu = $dao->obtenerCategorias();
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
                            <i class="bi bi-cart-fill"></i> Carrito 
                            <span class="badge bg-primary rounded-pill">
                                <?= !empty($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0 ?>
                            </span>
                        </a>
                    </li>
                    <!-- Lógica de Navegación de Admin -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownAdmin">
                                <li><a class="dropdown-item" href="?sec=admin/admin_productos">Gestionar Productos</a></li>
                                <li><a class="dropdown-item" href="?sec=admin/admin_categorias">Gestionar Categorías</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?sec=auth/logout">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn-tematico" href="?sec=auth/login"><i class="bi bi-shield-lock-fill"></i> Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <!-- Contenedor normal para secciones públicas -->
    <main class="container my-4 flex-grow-1">
        <?php
        // Incluir la página de la sección
        if (file_exists($pagina)) {
            include $pagina;
        } else {
            include $seccionesValidas['404'];
        }
        ?>
    </main>

    <footer class="container-fluid text-center py-4 mt-auto">
        <p class="mb-0">FunkoManía © <?= date('Y') ?> - Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/fondo-estrellas.js"></script>
</body>
</html>
