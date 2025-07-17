<?php
require_once(__DIR__ . '/../config/database.php');

/**
 * Clase para manejar las operaciones de base de datos de productos
 */
class ProductoDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        if ($this->db->getConnection() === null) {
            die("Error crítico: No se pudo establecer la conexión con la base de datos.");
        }
    }

    /**
     * Obtener todos los productos con información de categorías (muchos a muchos)
     */
    public function obtenerTodos() {
        try {
            // Verificar si ya existe un orden aleatorio en la sesión
            if (!isset($_SESSION['productos_orden_aleatorio'])) {
                // Generar un nuevo orden aleatorio
                $sql = "SELECT p.id FROM productos p ORDER BY RAND()";
                $stmt = $this->db->query($sql);
                $orden_ids = [];
                while ($row = $stmt->fetch()) {
                    $orden_ids[] = $row['id'];
                }
                $_SESSION['productos_orden_aleatorio'] = $orden_ids;
            }
            
            // Usar el orden guardado en la sesión
            $orden_ids = $_SESSION['productos_orden_aleatorio'];
            if (empty($orden_ids)) {
                // Si no hay orden, usar orden por nombre
                $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                        FROM productos p
                        LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                        LEFT JOIN categoria c ON pc.categoria_id = c.id
                        GROUP BY p.id
                        ORDER BY p.Nombre";
            } else {
                // Construir la consulta con el orden específico
                $placeholders = str_repeat('?,', count($orden_ids) - 1) . '?';
                $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                        FROM productos p
                        LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                        LEFT JOIN categoria c ON pc.categoria_id = c.id
                        GROUP BY p.id
                        ORDER BY FIELD(p.id, $placeholders)";
            }
            
            $stmt = $this->db->query($sql, $orden_ids);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $categorias = $row['categorias'] ?? null;
                // Si no hay categorías, usar "Sin Categoría"
                if (empty($categorias)) {
                    $categorias = 'Sin Categoría';
                }
                $productos[] = new Producto(
                    $row['id'], $row['Nombre'], $row['Descripcion'], $row['precio'],
                    $categorias, 0, null, $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un producto por ID con sus categorías
     */
    public function obtenerPorId($id) {
        try {
            // Se une con las categorías para obtenerlas todas en una sola consulta
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, 
                           GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    WHERE p.id = ?
                    GROUP BY p.id";

            $stmt = $this->db->query($sql, [$id]);
            $row = $stmt->fetch();

            if ($row) {
                // Se pasa el nombre del archivo de imagen y la lista de categorías
                return new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['categorias'] ?? 'Sin categoría', // Usamos el campo concatenado
                    0,
                    null,
                    $row['imagen'] // Pasamos solo el nombre del archivo
                );
            }
            return null;
        } catch (Exception $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos por categoría (usando tabla pivote)
     */
    public function obtenerPorCategoria($categoriaId) {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    INNER JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    WHERE pc.categoria_id = ?
                    GROUP BY p.id
                    ORDER BY p.Nombre";
            $stmt = $this->db->query($sql, [$categoriaId]);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['categorias'] ?? 'Sin Categoría',
                    0,
                    null,
                    $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerPorCategoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar productos por nombre (todas sus categorías)
     */
    public function buscarPorNombre($termino) {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    WHERE p.Nombre LIKE ? OR p.Descripcion LIKE ?
                    GROUP BY p.id
                    ORDER BY p.Nombre";
            $termino = "%$termino%";
            $stmt = $this->db->query($sql, [$termino, $termino]);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['categorias'] ?? 'Sin categoría',
                    0,
                    null,
                    $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en buscarPorNombre: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos por rango de precio (todas sus categorías)
     */
    public function obtenerPorRangoPrecio($precioMin, $precioMax) {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    WHERE p.precio BETWEEN ? AND ?
                    GROUP BY p.id
                    ORDER BY p.precio ASC";
            $stmt = $this->db->query($sql, [$precioMin, $precioMax]);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['categorias'] ?? 'Sin categoría',
                    0,
                    null,
                    $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerPorRangoPrecio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insertar un nuevo producto
     */
    public function insertar(Producto $producto) {
        $db = Database::getInstance();
        $sql = "INSERT INTO productos (Nombre, Descripcion, precio, imagen) VALUES (?, ?, ?, ?)";
        
        // Obtener el nombre de la imagen; si es nulo, usar not_found.png
        $imagen = $producto->getNombreImagen() ?? 'not_found.png';

        $stmt = $db->query($sql, [
            $producto->getNombre(),
            $producto->getDescripcion(),
            $producto->getPrecio(),
            $imagen
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Actualizar un producto existente
     */
    public function actualizar(Producto $producto) {
        $db = Database::getInstance();
        $sql = "UPDATE productos SET Nombre = ?, Descripcion = ?, precio = ?, imagen = ? WHERE id = ?";

        // Obtener el nombre de la imagen; si es nulo, usar not_found.png
        $imagen = $producto->getNombreImagen() ?? 'not_found.png';

        $stmt = $db->query($sql, [
            $producto->getNombre(),
            $producto->getDescripcion(),
            $producto->getPrecio(),
            $imagen,
            $producto->getId()
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Eliminar un producto
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM productos WHERE id = ?";
            return $this->db->query($sql, [$id]);
        } catch (Exception $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los productos para la tabla de administración.
     * Devuelve un array asociativo.
     */
    public function obtenerTodosParaAdmin() {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    GROUP BY p.id
                    ORDER BY p.Nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerTodosParaAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una selección de productos destacados (ej. aleatorios)
     */
    public function obtenerDestacados($limite = 4) {
        try {
            // Verificar si ya existe un orden aleatorio en la sesión
            if (!isset($_SESSION['productos_orden_aleatorio'])) {
                // Generar un nuevo orden aleatorio
                $sql = "SELECT p.id FROM productos p ORDER BY RAND()";
                $stmt = $this->db->query($sql);
                $orden_ids = [];
                while ($row = $stmt->fetch()) {
                    $orden_ids[] = $row['id'];
                }
                $_SESSION['productos_orden_aleatorio'] = $orden_ids;
            }
            
            // Usar el orden guardado en la sesión
            $orden_ids = $_SESSION['productos_orden_aleatorio'];
            if (empty($orden_ids)) {
                // Si no hay orden, usar orden por nombre
                $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                        FROM productos p
                        LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                        LEFT JOIN categoria c ON pc.categoria_id = c.id
                        GROUP BY p.id
                        ORDER BY p.Nombre
                        LIMIT ?";
                $stmt = $this->db->query($sql, [$limite]);
            } else {
                // Construir la consulta con el orden específico y límite
                $placeholders = str_repeat('?,', count($orden_ids) - 1) . '?';
                $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                        FROM productos p
                        LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                        LEFT JOIN categoria c ON pc.categoria_id = c.id
                        GROUP BY p.id
                        ORDER BY FIELD(p.id, $placeholders)
                        LIMIT ?";
                $stmt = $this->db->query($sql, array_merge($orden_ids, [$limite]));
            }
            
            $productos = [];
            while ($row = $stmt->fetch()) {
                $categorias = $row['categorias'] ?? null;
                // Si no hay categorías, usar "Sin Categoría"
                if (empty($categorias)) {
                    $categorias = 'Sin Categoría';
                }
                $productos[] = new Producto(
                    $row['id'], $row['Nombre'], $row['Descripcion'], $row['precio'],
                    $categorias, 0, null, $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerDestacados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar productos por nombre de categoría
     */
    public function buscarPorCategoriaNombre($nombreCategoria) {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    WHERE c.nombre_categoria LIKE ?
                    GROUP BY p.id
                    ORDER BY p.Nombre";
            $nombreCategoria = "%$nombreCategoria%";
            $stmt = $this->db->query($sql, [$nombreCategoria]);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['categorias'] ?? 'Sin categoría',
                    0,
                    null,
                    $row['imagen']
                );
            }
            return $productos;
        } catch (Exception $e) {
            error_log("Error en buscarPorCategoriaNombre: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si una categoría tiene productos asignados
     */
    public function categoriaTieneProductos($categoria_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM producto_categoria WHERE categoria_id = ?";
            $stmt = $this->db->query($sql, [$categoria_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;

        } catch (Exception $e) {
            error_log("Error en categoriaTieneProductos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener productos por categoría (alias para mantener compatibilidad)
     */
    public function obtenerProductosPorCategoria($categoria_id) {
        return $this->obtenerPorCategoria($categoria_id);
    }
}
?> 