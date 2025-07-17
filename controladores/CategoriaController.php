<?php
require_once __DIR__ . '/../clases/CategoriaDAO.php';
require_once __DIR__ . '/../clases/Utils.php';

/**
 * Controlador para manejar la lógica de categorías
 */
class CategoriaController {
    
    /**
     * Procesar formulario de agregar/editar categoría
     */
    public static function procesarFormulario($datos) {
        $dao = new CategoriaDAO();
        $errores = [];
        
        $nombre = Utils::limpiarTexto($datos['nombre'] ?? '');
        $descripcion = Utils::limpiarTexto($datos['descripcion'] ?? '');
        $id = isset($datos['id']) ? intval($datos['id']) : null;
        
        // Validaciones
        if (!Utils::validarNombre($nombre)) {
            $errores[] = 'El nombre de la categoría es obligatorio y debe tener al menos 2 caracteres.';
        }
        
        if (empty($errores)) {
            if ($id) { // Actualizar
                if ($dao->actualizar($id, $nombre, $descripcion)) {
                    return ['success' => true, 'mensaje' => 'Categoría editada exitosamente'];
                } else {
                    $errores[] = 'Error al actualizar la categoría.';
                }
            } else { // Insertar
                if ($dao->insertar($nombre, $descripcion)) {
                    return ['success' => true, 'mensaje' => 'Categoría agregada exitosamente'];
                } else {
                    $errores[] = 'Error al agregar la categoría.';
                }
            }
        }
        
        return ['error' => true, 'errores' => $errores];
    }
    
    /**
     * Eliminar categoría
     */
    public static function eliminarCategoria($id) {
        $dao = new CategoriaDAO();
        
        // Verificar si la categoría es protegida
        if ($dao->esProtegida($id)) {
            return ['error' => true, 'mensaje' => 'No se puede eliminar una categoría protegida'];
        }
        
        // Verificar si la categoría tiene productos
        if ($dao->tieneProductos($id)) {
            return ['error' => true, 'mensaje' => 'No se puede eliminar una categoría que tiene productos asignados'];
        }
        
        if ($dao->eliminar($id)) {
            return ['success' => true, 'mensaje' => 'Categoría eliminada exitosamente'];
        }
        
        return ['error' => true, 'mensaje' => 'Error al eliminar la categoría'];
    }
    
    /**
     * Obtener categoría para edición
     */
    public static function obtenerCategoriaParaEdicion($id) {
        $dao = new CategoriaDAO();
        return $dao->obtenerPorId($id);
    }
    
    /**
     * Obtener todas las categorías
     */
    public static function obtenerTodasLasCategorias() {
        $dao = new CategoriaDAO();
        return $dao->obtenerTodas();
    }
    
    /**
     * Obtener categorías para formularios de administración
     */
    public static function obtenerCategoriasParaAdmin() {
        $dao = new CategoriaDAO();
        return $dao->obtenerTodasParaAdmin();
    }
    
    /**
     * Buscar categorías por nombre
     */
    public static function buscarCategorias($termino) {
        $dao = new CategoriaDAO();
        return $dao->buscarPorNombre($termino);
    }
}
?> 