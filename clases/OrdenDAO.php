<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/Producto.php');

/**
 * Clase para manejar las operaciones de base de datos de órdenes
 */
class OrdenDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        if ($this->db->getConnection() === null) {
            die("Error crítico: No se pudo establecer la conexión con la base de datos.");
        }
    }

    /**
     * Crear una nueva orden
     */
    public function crearOrden($usuario_id, $datos_cliente, $productos_carrito, $total) {
        try {
            $this->db->getConnection()->beginTransaction();

            // Insertar la orden
            $sql = "INSERT INTO ordenes (usuario_id, total, nombre_cliente, email_cliente, telefono_cliente, 
                                       direccion_envio, ciudad, codigo_postal, metodo_pago, ultimos_digitos_tarjeta) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->query($sql, [
                $usuario_id,
                $total,
                $datos_cliente['nombre'],
                $datos_cliente['email'],
                $datos_cliente['telefono'],
                $datos_cliente['direccion'],
                $datos_cliente['ciudad'],
                $datos_cliente['codigo_postal'],
                'tarjeta',
                substr(preg_replace('/\s+/', '', $datos_cliente['numero_tarjeta']), -4)
            ]);

            $orden_id = $this->db->getConnection()->lastInsertId();

            // Insertar los items de la orden
            foreach ($productos_carrito as $item) {
                $sql = "INSERT INTO orden_items (orden_id, producto_id, cantidad, precio_unitario, subtotal) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $this->db->query($sql, [
                    $orden_id,
                    $item['producto']->getId(),
                    $item['cantidad'],
                    $item['producto']->getPrecio(),
                    $item['subtotal']
                ]);
            }

            $this->db->getConnection()->commit();
            return $orden_id;

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error en crearOrden: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de compras de un usuario
     */
    public function obtenerHistorialUsuario($usuario_id) {
        try {
            $sql = "SELECT o.*, 
                           COUNT(oi.id) as total_items,
                           GROUP_CONCAT(CONCAT(p.Nombre, ' (', oi.cantidad, ')') SEPARATOR ', ') as productos
                    FROM ordenes o
                    LEFT JOIN orden_items oi ON o.id = oi.orden_id
                    LEFT JOIN productos p ON oi.producto_id = p.id
                    WHERE o.usuario_id = ?
                    GROUP BY o.id
                    ORDER BY o.fecha_orden DESC";
            
            $stmt = $this->db->query($sql, [$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerHistorialUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalles de una orden específica
     */
    public function obtenerDetalleOrden($orden_id, $usuario_id = null) {
        try {
            $sql = "SELECT o.*, 
                           oi.cantidad, oi.precio_unitario, oi.subtotal,
                           p.Nombre as nombre_producto, 
                           CASE 
                               WHEN p.imagen IS NULL OR p.imagen = '' THEN 'img/not_found.png'
                               ELSE CONCAT('img/', p.imagen)
                           END as imagen
                    FROM ordenes o
                    LEFT JOIN orden_items oi ON o.id = oi.orden_id
                    LEFT JOIN productos p ON oi.producto_id = p.id
                    WHERE o.id = ?";
            
            $params = [$orden_id];
            if ($usuario_id) {
                $sql .= " AND o.usuario_id = ?";
                $params[] = $usuario_id;
            }
            
            $sql .= " ORDER BY oi.id";
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerDetalleOrden: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una orden por ID
     */
    public function obtenerOrdenPorId($orden_id, $usuario_id = null) {
        try {
            $sql = "SELECT * FROM ordenes WHERE id = ?";
            $params = [$orden_id];
            
            if ($usuario_id) {
                $sql .= " AND usuario_id = ?";
                $params[] = $usuario_id;
            }
            
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerOrdenPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar estado de una orden
     */
    public function actualizarEstadoOrden($orden_id, $estado) {
        try {
            $sql = "UPDATE ordenes SET estado = ? WHERE id = ?";
            return $this->db->query($sql, [$estado, $orden_id]);

        } catch (Exception $e) {
            error_log("Error en actualizarEstadoOrden: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de compras del usuario
     */
    public function obtenerEstadisticasUsuario($usuario_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_ordenes,
                        SUM(total) as total_gastado,
                        AVG(total) as promedio_orden,
                        MAX(fecha_orden) as ultima_compra
                    FROM ordenes 
                    WHERE usuario_id = ? AND estado != 'cancelada'";
            
            $stmt = $this->db->query($sql, [$usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticasUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener los datos de la última compra del usuario
     */
    public function obtenerUltimosDatosUsuario($usuario_id) {
        try {
            $sql = "SELECT 
                        nombre_cliente, email_cliente, telefono_cliente,
                        direccion_envio, ciudad, codigo_postal
                    FROM ordenes 
                    WHERE usuario_id = ? 
                    ORDER BY fecha_orden DESC 
                    LIMIT 1";
            
            $stmt = $this->db->query($sql, [$usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en obtenerUltimosDatosUsuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el usuario tiene compras anteriores
     */
    public function tieneComprasAnteriores($usuario_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM ordenes WHERE usuario_id = ?";
            $stmt = $this->db->query($sql, [$usuario_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;

        } catch (Exception $e) {
            error_log("Error en tieneComprasAnteriores: " . $e->getMessage());
            return false;
        }
    }
}
?> 