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