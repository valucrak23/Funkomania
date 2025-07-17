<?php
/**
 * Clase de utilidades para funciones comunes del sistema
 */
class Utils {
    
    /**
     * Validar formato de email
     */
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar contraseña (mínimo 6 caracteres)
     */
    public static function validarPassword($password) {
        return strlen($password) >= 6;
    }

    /**
     * Validar nombre completo (mínimo 2 caracteres)
     */
    public static function validarNombre($nombre) {
        return strlen(trim($nombre)) >= 2;
    }

    /**
     * Validar precio (debe ser mayor a 0)
     */
    public static function validarPrecio($precio) {
        return is_numeric($precio) && $precio > 0;
    }

    /**
     * Limpiar y validar texto de entrada
     */
    public static function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Generar nombre de archivo seguro para imágenes
     */
    public static function generarNombreArchivo($nombreProducto, $extension = 'png') {
        $nombreProducto = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombreProducto);
        $nombreProducto = preg_replace('/\s+/', '_', trim($nombreProducto));
        $nombreProducto = strtolower($nombreProducto);
        return $nombreProducto . '.' . $extension;
    }

    /**
     * Validar archivo de imagen
     */
    public static function validarImagen($archivo, $extensionesPermitidas = ['png', 'jpg', 'jpeg']) {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        return in_array($extension, $extensionesPermitidas);
    }

    /**
     * Obtener los últimos 4 dígitos de una tarjeta
     */
    public static function obtenerUltimosDigitosTarjeta($numeroTarjeta) {
        return substr(preg_replace('/\s+/', '', $numeroTarjeta), -4);
    }

    /**
     * Validar fecha de vencimiento de tarjeta
     */
    public static function validarFechaVencimiento($mes, $anio) {
        $mesActual = (int)date('m');
        $anioActual = (int)date('y');
        
        $mes = (int)$mes;
        $anio = (int)$anio;
        
        // Validar rango de mes y año
        if ($mes < 1 || $mes > 12 || $anio < 0 || $anio > 40) {
            return false;
        }
        
        // Validar que no sea anterior al mes actual
        if ($anio < $anioActual || ($anio == $anioActual && $mes < $mesActual)) {
            return false;
        }
        
        return true;
    }

    /**
     * Formatear precio para mostrar
     */
    public static function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, ',', '.');
    }

    /**
     * Redirigir con mensaje
     */
    public static function redirigir($url, $mensaje = null) {
        if ($mensaje) {
            $separador = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separador . 'msg=' . urlencode($mensaje);
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Verificar si el usuario está logueado
     */
    public static function usuarioLogueado() {
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Verificar si el usuario es admin
     */
    public static function esAdmin() {
        return isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] === 'Admin';
    }

    /**
     * Obtener datos del usuario logueado
     */
    public static function obtenerUsuarioActual() {
        if (!self::usuarioLogueado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'nivel' => $_SESSION['usuario_nivel']
        ];
    }
}
?> 