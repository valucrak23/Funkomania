<?php
require_once __DIR__ . '/ProductoDAO.php';

/*
Representa un producto en la tienda con sus propiedades y métodos
 */
class Producto {
    private $id;            
    private $nombre;         
    private $descripcion;     
    private $precio;          
    private $categorias;       
    private $stock;           
    private $fecha_lanzamiento; 
    private $imagen;          

    public function __construct($id, $nombre, $descripcion, $precio, $categorias, $stock, $fecha_lanzamiento, $imagen) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->categorias = $categorias;
        $this->stock = $stock;
        $this->fecha_lanzamiento = $fecha_lanzamiento;
        $this->imagen = $imagen;
    }

    // Métodos getter para acceder a las propiedades privadas
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getCategorias() {
        return $this->categorias;
    }

    public function getStock() {
        return $this->stock;
    }

    public function getFechaLanzamiento() {
        return $this->fecha_lanzamiento;
    }

    /**
     * Devuelve la RUTA COMPLETA de la imagen para usar en etiquetas <img>.
     * Si no hay imagen, devuelve la imagen por defecto.
     */
    public function getImagen() {
        if (!empty($this->imagen) && file_exists(__DIR__ . '/../img/' . $this->imagen)) {
            return BASE_URL . '/img/' . $this->imagen;
        }
        return BASE_URL . '/img/not_found.png';
    }

    /**
     * Devuelve solo el NOMBRE DEL ARCHIVO de la imagen, para lógica interna.
     */
    public function getNombreImagen() {
        return $this->imagen;
    }

    /**
     * Método para guardar el producto en la base de datos
     */
    public function guardar() {
        $dao = new ProductoDAO();
        if ($this->id) {
            return $dao->actualizar($this);
        } else {
            return $dao->insertar($this);
        }
    }

    /**
     * Método para eliminar el producto de la base de datos
     */
    public function eliminar() {
        if ($this->id) {
            $dao = new ProductoDAO();
            return $dao->eliminar($this->id);
        }
        return false;
    }
}