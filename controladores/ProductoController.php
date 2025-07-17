<?php
require_once __DIR__ . '/../clases/ProductoDAO.php';
require_once __DIR__ . '/../clases/CategoriaDAO.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/Utils.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Controlador para manejar la lógica de productos
 */
class ProductoController {
    
    /**
     * Procesar formulario de agregar/editar producto
     */
    public static function procesarFormulario($datos, $archivos = null) {
        $dao = new ProductoDAO();
        $categoriaDAO = new CategoriaDAO();
        $errores = [];
        
        // Validar datos básicos
        $nombre = Utils::limpiarTexto($datos['nombre'] ?? '');
        $descripcion = Utils::limpiarTexto($datos['descripcion'] ?? '');
        $precio = floatval($datos['precio'] ?? 0);
        $id = isset($datos['id']) ? intval($datos['id']) : null;
        
        // Validaciones
        if (!Utils::validarNombre($nombre)) {
            $errores[] = 'El nombre es obligatorio y debe tener al menos 2 caracteres.';
        }
        if (empty($descripcion)) {
            $errores[] = 'La descripción es obligatoria.';
        }
        if (!Utils::validarPrecio($precio)) {
            $errores[] = 'El precio debe ser mayor a 0.';
        }
        
        // Procesar categorías
        $id_principal = $datos['categoria_principal'] ?? null;
        $id_secundaria = $datos['categoria_secundaria'] ?? null;
        $cats_ids = [];
        if (!empty($id_principal)) { 
            $cats_ids[] = $id_principal; 
        }
        if (!empty($id_secundaria) && $id_secundaria != $id_principal) { 
            $cats_ids[] = $id_secundaria; 
        }
        $cats_ids = array_unique($cats_ids);
        
        if (empty($id_principal)) {
            $errores[] = 'La categoría principal es obligatoria.';
        }
        
        // Procesar imagen
        $imagen_final = null;
        if (isset($datos['eliminar_imagen']) && $datos['eliminar_imagen'] === '1') {
            $imagen_final = null;
        } elseif ($archivos && isset($archivos['imagen']) && $archivos['imagen']['error'] === UPLOAD_ERR_OK) {
            if (!Utils::validarImagen($archivos['imagen'], ['png'])) {
                $errores[] = 'Solo se permiten archivos PNG.';
            } else {
                $nombreArchivo = Utils::generarNombreArchivo($nombre);
                $destino = __DIR__ . '/../img/' . $nombreArchivo;
                
                if (move_uploaded_file($archivos['imagen']['tmp_name'], $destino)) {
                    $imagen_final = $nombreArchivo;
                } else {
                    $errores[] = 'Error al subir la imagen.';
                }
            }
        }
        
        if (empty($errores)) {
            $db = Database::getInstance();
            
            if ($id) { // Actualizar
                $producto_actualizado = new Producto($id, $nombre, $descripcion, $precio, '', null, null, $imagen_final);
                $dao->actualizar($producto_actualizado);
                $db->query("DELETE FROM producto_categoria WHERE producto_id = ?", [$id]);
                foreach ($cats_ids as $catid) {
                    $db->query("INSERT INTO producto_categoria (producto_id, categoria_id) VALUES (?, ?)", [$id, $catid]);
                }
                return ['success' => true, 'mensaje' => 'Producto editado exitosamente'];
            } else { // Insertar
                $producto_nuevo = new Producto(null, $nombre, $descripcion, $precio, '', null, null, $imagen_final);
                $dao->insertar($producto_nuevo);
                $id = $db->getConnection()->lastInsertId();
                foreach ($cats_ids as $catid) {
                    $db->query("INSERT INTO producto_categoria (producto_id, categoria_id) VALUES (?, ?)", [$id, $catid]);
                }
                return ['success' => true, 'mensaje' => 'Producto agregado exitosamente'];
            }
        }
        
        return ['error' => true, 'errores' => $errores];
    }
    
    /**
     * Eliminar producto
     */
    public static function eliminarProducto($id) {
        $dao = new ProductoDAO();
        $db = Database::getInstance();
        
        // Eliminar relaciones con categorías
        $db->query("DELETE FROM producto_categoria WHERE producto_id = ?", [$id]);
        
        // Eliminar producto
        if ($dao->eliminar($id)) {
            return ['success' => true, 'mensaje' => 'Producto eliminado exitosamente'];
        }
        
        return ['error' => true, 'mensaje' => 'Error al eliminar el producto'];
    }
    
    /**
     * Obtener datos para formulario de edición
     */
    public static function obtenerDatosParaEdicion($id) {
        $dao = new ProductoDAO();
        $categoriaDAO = new CategoriaDAO();
        $db = Database::getInstance();
        
        $producto = $dao->obtenerPorId($id);
        if (!$producto) {
            return null;
        }
        
        // Obtener categorías del producto
        $stmt = $db->query("SELECT categoria_id FROM producto_categoria WHERE producto_id = ?", [$id]);
        $categorias_producto = array_column($stmt->fetchAll(), 'categoria_id');
        
        $categoria_principal_id = null;
        $categoria_secundaria_id = null;
        
        if (!empty($categorias_producto)) {
            $categoria_principal_id = array_shift($categorias_producto);
            if (!empty($categorias_producto)) {
                $categoria_secundaria_id = array_shift($categorias_producto);
            }
        }
        
        return [
            'producto' => $producto,
            'categoria_principal_id' => $categoria_principal_id,
            'categoria_secundaria_id' => $categoria_secundaria_id,
            'categorias' => $categoriaDAO->obtenerTodasParaAdmin()
        ];
    }
    
    /**
     * Obtener productos para administración
     */
    public static function obtenerProductosParaAdmin($busqueda = '') {
        $dao = new ProductoDAO();
        
        if (!empty($busqueda)) {
            return $dao->buscarPorNombre($busqueda);
        } else {
            return $dao->obtenerTodosParaAdmin();
        }
    }
}
?> 