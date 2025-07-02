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
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    GROUP BY p.id
                    ORDER BY RAND()";
            $stmt = $this->db->query($sql);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'], $row['Nombre'], $row['Descripcion'], $row['precio'],
                    $row['categorias'] ?? 'Sin categoría', 0, null, $row['imagen']
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
                // Se pasa solo el nombre de la imagen, la clase Producto se encarga del resto
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
     * Obtener todas las categorías
     */
    public function obtenerCategorias() {
        try {
            $sql = "SELECT id, nombre_categoria, descripcion FROM categoria ORDER BY nombre_categoria";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            // En un entorno de producción, podría ser mejor devolver un array vacío
            // y manejar el error de forma más elegante en la vista.
            die("DEPURACIÓN: ¡ERROR FATAL al intentar obtener las categorías!: " . $e->getMessage());
        }
    }

    /**
     * Insertar una nueva categoría
     */
    public function insertarCategoria($nombre, $descripcion) {
        try {
            $sql = "INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)";
            return $this->db->query($sql, [$nombre, $descripcion]);
        } catch (Exception $e) {
            error_log("Error al insertar categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una categoría existente
     */
    public function actualizarCategoria($id, $nombre, $descripcion) {
        try {
            $sql = "UPDATE categoria SET nombre_categoria = ?, descripcion = ? WHERE id = ?";
            return $this->db->query($sql, [$nombre, $descripcion, $id]);
        } catch (Exception $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una categoría
     */
    public function eliminarCategoria($id) {
        try {
            // Opcional: Antes de eliminar, podrías verificar si algún producto usa esta categoría.
            // Por simplicidad, aquí la eliminamos directamente.
            $sql = "DELETE FROM categoria WHERE id = ?";
            return $this->db->query($sql, [$id]);
        } catch (Exception $e) {
            error_log("Error al eliminar categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el ID de una categoría por nombre
     */
    private function obtenerCategoriaId($nombreCategoria) {
        try {
            $sql = "SELECT id FROM categoria WHERE nombre_categoria = ?";
            $stmt = $this->db->query($sql, [$nombreCategoria]);
            $row = $stmt->fetch();
            
            if ($row) {
                return $row['id'];
            }
            
            // Si la categoría no existe, la creamos
            $sql = "INSERT INTO categoria (nombre_categoria) VALUES (?)";
            $this->db->query($sql, [$nombreCategoria]);
            return $this->db->getConnection()->lastInsertId();
        } catch (Exception $e) {
            error_log("Error en obtenerCategoriaId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener productos con información completa de categoría
     */
    public function obtenerProductosConCategoria() {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, 
                           c.id as categoria_id, c.nombre_categoria 
                    FROM productos p 
                    LEFT JOIN categoria c ON p.categoria_id = c.id 
                    ORDER BY p.Nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error en obtenerProductosConCategoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una categoría por su ID
     */
    public function obtenerCategoriaPorId($id) {
        try {
            $sql = "SELECT * FROM categoria WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            $row = $stmt->fetch();
            return $row ? $row : null;
        } catch (Exception $e) {
            error_log("Error en obtenerCategoriaPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los productos con paginación
     */
    public function obtenerTodosPaginados($limite = 12, $offset = 0) {
        try {
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, c.nombre_categoria 
                    FROM productos p 
                    LEFT JOIN categoria c ON p.categoria_id = c.id 
                    ORDER BY p.Nombre
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->query($sql, [$limite, $offset]);
            $productos = [];
            
            while ($row = $stmt->fetch()) {
                // Solo usar imagen por defecto si está vacía o es NULL
                $imagen = empty($row['imagen']) ? 'img/not_found.png' : 'img/' . $row['imagen'];
                $productos[] = new Producto(
                    $row['id'],
                    $row['Nombre'],
                    $row['Descripcion'],
                    $row['precio'],
                    $row['nombre_categoria'] ?? 'Sin categoría',
                    0, // stock - no existe en tu tabla
                    null, // fecha_lanzamiento - no existe en tu tabla
                    $imagen
                );
            }
            
            return $productos;
        } catch (Exception $e) {
            error_log("Error en obtenerTodosPaginados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de productos
     */
    public function contarProductos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM productos";
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch();
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error en contarProductos: " . $e->getMessage());
            return 0;
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
            $sql = "SELECT p.id, p.Nombre, p.Descripcion, p.precio, p.imagen, GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias
                    FROM productos p
                    LEFT JOIN producto_categoria pc ON p.id = pc.producto_id
                    LEFT JOIN categoria c ON pc.categoria_id = c.id
                    GROUP BY p.id
                    ORDER BY RAND()
                    LIMIT ?";
            $stmt = $this->db->query($sql, [$limite]);
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto(
                    $row['id'], $row['Nombre'], $row['Descripcion'], $row['precio'],
                    $row['categorias'] ?? 'Sin categoría', 0, null, $row['imagen']
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
}
?> 