<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/Utils.php');

/**
 * Clase para manejar las operaciones de base de datos de usuarios
 */
class UsuarioDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        if ($this->db->getConnection() === null)
        {
            die("Error crítico: No se pudo establecer la conexión con la base de datos.");
        }
    }

    /**
     * Insertar un nuevo usuario
     */
    public function insertar($nombre_completo, $email, $password, $nivel = 'User') {
        try {
            // Verificar si el email ya existe
            if ($this->emailExiste($email))
            {
                return false;
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre_completo, email, password, Nivel) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [$nombre_completo, $email, $password_hash, $nivel]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en insertar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un email ya existe
     */
    public function emailExiste($email) {
        try 
        {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
            $stmt = $this->db->query($sql, [$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (Exception $e) {
            error_log("Error en emailExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email) {
        try 
        {
            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmt = $this->db->query($sql, [$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerPorEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        try 
        {
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos() {
        try 
        {
            $sql = "SELECT id, nombre_completo, email, Nivel FROM usuarios ORDER BY nombre_completo";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar usuario
     */
    public function actualizar($id, $nombre_completo, $email, $nivel, $password = null) {
        try {
            if ($password)
            {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, Nivel = ?, password = ? WHERE id = ?;";
                $stmt = $this->db->query($sql, [$nombre_completo, $email, $nivel, $password_hash, $id]);
            } else
            {
                $sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, Nivel = ? WHERE id = ?;";
                $stmt = $this->db->query($sql, [$nombre_completo, $email, $nivel, $id]);
            }
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id) {
        try {
            // Verificar que no sea el último admin
            if ($this->esUltimoAdmin($id))
            {
                return false;
            }
            
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error en eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si es el último admin
     */
    private function esUltimoAdmin($id_usuario) {
        try 
        {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE Nivel = 'Admin' AND id != ?";
            $stmt = $this->db->query($sql, [$id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] == 0;
        } catch (Exception $e) {
            error_log("Error en esUltimoAdmin: " . $e->getMessage());
            return true; // Por seguridad, asumir que es el último admin
        }
    }

    // Métodos de validación usando Utils
    public function validarEmail($email) {
        return Utils::validarEmail($email);
    }

    public function validarPassword($password) {
        return Utils::validarPassword($password);
    }

    public function validarNombre($nombre) {
        return Utils::validarNombre($nombre);
    }
}
?> 