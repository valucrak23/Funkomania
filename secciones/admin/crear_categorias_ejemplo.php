<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance();
    $sql = "SELECT COUNT(*) as total FROM categoria";
    $stmt = $db->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $result['total'];
    echo "<div class='container my-4'>";
    if ($total == 0) {
        echo "<h2><i class='bi bi-plus-circle-fill'></i> Creando categorías de ejemplo...</h2>";
        $categorias = [
            ['nombre' => 'Marvel', 'descripcion' => 'Personajes del universo Marvel'],
            ['nombre' => 'Star Wars', 'descripcion' => 'Personajes de Star Wars'],
            ['nombre' => 'Disney', 'descripcion' => 'Personajes de Disney'],
            ['nombre' => 'Videojuegos', 'descripcion' => 'Personajes de videojuegos'],
            ['nombre' => 'Sin Categoría', 'descripcion' => 'Productos sin categoría asignada']
        ];
        foreach ($categorias as $cat) {
            $sql = "INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)";
            $db->query($sql, [$cat['nombre'], $cat['descripcion']]);
            echo "<p>✅ Categoría <strong>{$cat['nombre']}</strong> creada</p>";
        }
        echo "<div class='alert alert-success mt-3'><i class='bi bi-check-circle-fill'></i> <strong>¡Categorías creadas exitosamente!</strong></div>";
    } else {
        echo "<div class='alert alert-info'><i class='bi bi-info-circle-fill'></i> Ya existen <strong>$total</strong> categorías en la base de datos</div>";
    }
    echo "<a href='?sec=admin/admin_categorias' class='btn btn-primary mt-3'><i class='bi bi-arrow-left'></i> Volver a Gestionar Categorías</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='container my-4'><div class='alert alert-danger'><i class='bi bi-exclamation-triangle-fill'></i> Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<a href='?sec=admin/admin_categorias' class='btn btn-primary'><i class='bi bi-arrow-left'></i> Volver a Gestionar Categorías</a></div>";
}
?> 