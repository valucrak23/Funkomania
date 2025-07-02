<?php
session_start();
// Seguridad: solo admins
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php?sec=auth/login');
    exit;
}

// Enrutador simple
$seccion = $_GET['sec'] ?? 'inicio';
$seccionesValidas = [
    'inicio' => 'secciones/inicio.php',
    'productos' => '../secciones/admin/admin_productos.php',
    'marcas' => 'secciones/marcas.php', // Puedes crear este archivo luego
    'usuarios' => 'secciones/usuarios.php', // Puedes crear este archivo luego
    'niveles' => 'secciones/niveles.php', // Puedes crear este archivo luego
    'provincias' => 'secciones/provincias.php', // Puedes crear este archivo luego
    'login' => 'secciones/login.php', // Puedes crear este archivo luego
    'categorias' => '../secciones/admin/admin_categorias.php',
];
$pagina = $seccionesValidas[$seccion] ?? $seccionesValidas['inicio'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="?sec=inicio">Parcial 2</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="?sec=inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=productos">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=marcas">Marcas</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=usuarios">Usuarios</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=niveles">Niveles</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=provincias">Provincias</a></li>
                <li class="nav-item"><a class="nav-link" href="?sec=login">Login</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
<?php
if (file_exists($pagina)) {
    include $pagina;
} else {
    echo '<div class="alert alert-warning">Sección no encontrada.</div>';
}
?>
</div>
</body>
</html> 