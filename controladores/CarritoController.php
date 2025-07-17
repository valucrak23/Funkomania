<?php
require_once __DIR__ . '/../clases/ProductoDAO.php';
require_once __DIR__ . '/../clases/Utils.php';

/**
 * Controlador para manejar la lógica del carrito de compras
 */
class CarritoController {
    
    /**
     * Agregar producto al carrito
     */
    public static function agregarProducto($id_producto) {
        if (!Utils::usuarioLogueado()) {
            return ['error' => 'Debes estar logueado para agregar productos al carrito'];
        }
        
        if (!isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] = 1;
        } else {
            // Verificar que no exceda el límite de 3
            if ($_SESSION['carrito'][$id_producto] < 3) {
                $_SESSION['carrito'][$id_producto]++;
            }
        }
        
        return ['success' => true, 'mensaje' => 'Producto agregado al carrito'];
    }
    
    /**
     * Quitar producto del carrito
     */
    public static function quitarProducto($id_producto) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            unset($_SESSION['carrito'][$id_producto]);
        }
        return ['success' => true];
    }
    
    /**
     * Modificar cantidad de producto
     */
    public static function modificarCantidad($id_producto, $accion) {
        if (!isset($_SESSION['carrito'][$id_producto])) {
            return ['error' => 'Producto no encontrado en el carrito'];
        }
        
        if ($accion === 'aumentar' && $_SESSION['carrito'][$id_producto] < 3) {
            $_SESSION['carrito'][$id_producto]++;
        } elseif ($accion === 'disminuir' && $_SESSION['carrito'][$id_producto] > 1) {
            $_SESSION['carrito'][$id_producto]--;
        }
        
        return ['success' => true];
    }
    
    /**
     * Obtener productos del carrito con información completa
     */
    public static function obtenerProductosCarrito() {
        if (empty($_SESSION['carrito'])) {
            return [];
        }
        
        $dao = new ProductoDAO();
        $productos_carrito = [];
        $total = 0;
        
        foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
            $producto = $dao->obtenerPorId($id_producto);
            if ($producto) {
                $subtotal = $producto->getPrecio() * $cantidad;
                $productos_carrito[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }
        
        return [
            'productos' => $productos_carrito,
            'total' => $total,
            'cantidad_items' => count($productos_carrito)
        ];
    }
    
    /**
     * Limpiar carrito
     */
    public static function limpiarCarrito() {
        unset($_SESSION['carrito']);
        return ['success' => true];
    }
    
    /**
     * Verificar si el carrito está vacío
     */
    public static function carritoVacio() {
        return empty($_SESSION['carrito']);
    }
    
    /**
     * Obtener cantidad de items en el carrito
     */
    public static function obtenerCantidadItems() {
        return !empty($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
    }
}
?> 