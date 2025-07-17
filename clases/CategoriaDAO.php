<?php
require_once(__DIR__ . '/../config/database.php');

/**
 * Clase para manejar las operaciones de base de datos de categorías
 */
class CategoriaDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if ($this->db->getConnection() === null)
            die("Error crítico: No se pudo establecer la conexión con la base de datos.");
    }

    /**
     * Obtener todas las categorías
     */
    public function obtenerTodas() {
        try {
            $sql = "SELECT id, nombre_categoria, descripcion FROM categoria ORDER BY nombre_categoria";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las categorías incluyendo "Sin Categoría para formularios de administración
     */
    public function obtenerTodasParaAdmin() {
        try {
            $sql = "SELECT id, nombre_categoria, descripcion FROM categoria ORDER BY nombre_categoria";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías para admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si una categoría es protegida (no se puede eliminar)
     */
    public function esProtegida($categoria_id) {
        try {
            $sql = "SELECT nombre_categoria FROM categoria WHERE id = ?";
            $stmt = $this->db->query($sql, [$categoria_id]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($categoria) {
                return $categoria['nombre_categoria'] === 'Sin Categoría';
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en esProtegida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insertar una nueva categoría
     */
    public function insertar($nombre, $descripcion) {
        try {
            $sql = "INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?, ?)";
            $stmt = $this->db->query($sql, [$nombre, $descripcion]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en insertarCategoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una categoría existente
     */
    public function actualizar($id, $nombre, $descripcion) {
        try {
            $sql = "UPDATE categoria SET nombre_categoria = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->db->query($sql, [$nombre, $descripcion, $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en actualizarCategoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una categoría
     */
    public function eliminar($id) {
        try {
            // Verificar si la categoría es protegida
            if ($this->esProtegida($id)) {
                return false;
            }
            
            // Verificar si la categoría tiene productos asignados
            if ($this->tieneProductos($id)) {
                return false;
            }
            
            $sql = "DELETE FROM categoria WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en eliminarCategoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si una categoría tiene productos asignados
     */
    public function tieneProductos($categoria_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM producto_categoria WHERE categoria_id = ?";
            $stmt = $this->db->query($sql, [$categoria_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log("Error en tieneProductos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener categoría por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT id, nombre_categoria, descripcion FROM categoria WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerCategoriaPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener ID de categoría por nombre
     */
    public function obtenerIdPorNombre($nombreCategoria) {
        try {
            $sql = "SELECT id FROM categoria WHERE nombre_categoria = ?";
            $stmt = $this->db->query($sql, [$nombreCategoria]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (Exception $e) {
            error_log("Error en obtenerIdPorNombre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar categorías por nombre
     */
    public function buscarPorNombre($termino) {
        try {
            $sql = "SELECT id, nombre_categoria, descripcion FROM categoria WHERE nombre_categoria LIKE ? ORDER BY nombre_categoria";
            $termino = "%$termino%";
            $stmt = $this->db->query($sql, [$termino]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en buscarPorNombre: " . $e->getMessage());
            return false;
        }
    }
}
?> 