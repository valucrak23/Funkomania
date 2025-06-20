# FunkoLandia - Tienda de Funkos con Base de Datos

## 📋 Descripción

Esta es una aplicación web de tienda de Funkos desarrollada en PHP que utiliza una base de datos MySQL para almacenar y gestionar productos. La aplicación incluye funcionalidades de catálogo, categorías, detalles de productos y un sistema de gestión de inventario.

## 🚀 Características

- **Catálogo de Funkos** con imágenes y descripciones
- **Categorización** de productos por tipo (Disney, Marvel, Star Wars, etc.)
- **Sistema de búsqueda** por nombre y descripción
- **Interfaz responsive** con Bootstrap
- **Arquitectura MVC** con clases bien estructuradas
- **Base de datos MySQL** con PDO para seguridad
- **Patrón DAO** para acceso a datos
- **Integración** con base de datos existente `funkos`

## 📦 Requisitos

- **XAMPP/Laragon** (Apache + MySQL + PHP)
- **PHP 7.4** o superior
- **MySQL 5.7** o superior
- **Base de datos 'funkos'** existente
- **Navegador web** moderno

## 🛠️ Instalación

### 1. Configurar XAMPP/Laragon

1. Descarga e instala XAMPP o Laragon
2. Inicia el servidor web y MySQL
3. Verifica que ambos servicios estén ejecutándose

### 2. Clonar/Descargar el proyecto

1. Coloca los archivos del proyecto en la carpeta `htdocs` de XAMPP o en la carpeta de Laragon
2. La ruta debería ser: `C:\xampp\htdocs\PII-CUBILLO-PARCIAL_1\` o similar

### 3. Configurar la base de datos

**Opción A: Crear desde phpMyAdmin**

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada `funkos`
3. Ejecuta el siguiente SQL:

```sql
-- Crear la tabla de categorías específicas de Funkos
CREATE TABLE IF NOT EXISTS categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar categorías de ejemplo para Funkos
INSERT INTO categoria (nombre_categoria) VALUES
('Disney'),
('Marvel'),
('Star Wars'),
('Harry Potter'),
('Anime'),
('Películas'),
('Series TV'),
('Videojuegos'),
('Deportistas'),
('Música');

-- Crear la tabla de productos con imagen por defecto
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL,
    Descripcion VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) NOT NULL DEFAULT 'not_found.png',
    categoria_id INT,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Verificar la instalación

1. Ve a: `http://localhost/PII-CUBILLO-PARCIAL_1/`
2. Deberías ver la página principal de la tienda
3. Navega por las diferentes secciones para verificar que todo funcione

## 📁 Estructura del proyecto

```
PII-CUBILLO-PARCIAL_1/
├── config/
│   └── database.php          # Configuración de base de datos
├── clases/
│   ├── Producto.php          # Clase principal de productos
│   └── ProductoDAO.php       # Acceso a datos (DAO)
├── secciones/
│   ├── productos.php        # Vista de catálogo
│   ├── detalle.php          # Vista de detalle
│   ├── categoria1.php       # Categoría Disney/Marvel
│   ├── categoria2.php       # Categoría Star Wars/Harry Potter
│   └── categoria3.php       # Categoría Anime/Videojuegos
├── img/                     # Imágenes de productos
├── css/                     # Estilos CSS
├── index.php               # Archivo principal
└── README.md               # Este archivo
```

## 🔧 Configuración

### Base de datos

La aplicación está configurada para usar la base de datos `funkos`. Si necesitas cambiar la configuración, edita el archivo `config/database.php`:

```php
define('DB_HOST', 'localhost');     // Host de MySQL
define('DB_NAME', 'funkos');        // Nombre de la base de datos
define('DB_USER', 'root');          // Usuario de MySQL
define('DB_PASS', '');              // Contraseña de MySQL
```

### Estructura de la base de datos

**Tabla `productos`:**
- `id` - Identificador único (AUTO_INCREMENT)
- `Nombre` - Nombre del producto
- `Descripcion` - Descripción del producto
- `precio` - Precio del producto
- `imagen` - Ruta de la imagen (por defecto: 'not_found.png')
- `categoria_id` - ID de la categoría (relación con tabla `categoria`)

**Tabla `categoria`:**
- `id` - Identificador único (AUTO_INCREMENT)
- `nombre_categoria` - Nombre de la categoría

## 🎯 Funcionalidades

### Para usuarios:
- **Ver catálogo completo** de Funkos
- **Filtrar por categorías**: Disney, Marvel, Star Wars, Harry Potter, etc.
- **Ver detalles** de cada Funko
- **Buscar productos** por nombre o descripción

### Para desarrolladores:
- **Arquitectura escalable** con patrones MVC y DAO
- **Código seguro** con propiedades privadas y métodos getter
- **Base de datos optimizada** con índices
- **Fácil mantenimiento** y extensión
- **Integración** con base de datos existente

## 🔍 Uso de la API

### Obtener todos los productos:
```php
$dao = new ProductoDAO();
$productos = $dao->obtenerTodos();
```

### Buscar por ID:
```php
$producto = $dao->obtenerPorId(1);
```

### Filtrar por categoría:
```php
$productos = $dao->obtenerPorCategoria('Marvel');
```

### Buscar por nombre:
```php
$productos = $dao->buscarPorNombre('iron man');
```

### Obtener todas las categorías:
```php
$categorias = $dao->obtenerCategorias();
```

## 🛡️ Seguridad

- **Prepared statements** para prevenir SQL injection
- **Propiedades privadas** en las clases
- **Validación de datos** en el DAO
- **Manejo de errores** con try-catch
- **Relaciones de base de datos** seguras

## 🐛 Solución de problemas

### Error de conexión a la base de datos:
1. Verifica que XAMPP/Laragon esté ejecutándose
2. Comprueba que MySQL esté activo
3. Revisa las credenciales en `config/database.php`
4. Asegúrate de que la base de datos `funkos` exista

### Error "categoria_id no existe":
1. Ejecuta el script SQL para crear la tabla `productos` con la columna `categoria_id`
2. O agrega manualmente la columna:
   ```sql
   ALTER TABLE productos ADD COLUMN categoria_id INT;
   ```

### Página no carga:
1. Verifica que Apache esté ejecutándose
2. Comprueba la ruta del proyecto en `htdocs`
3. Revisa los logs de error de Apache

### Imágenes no se muestran:
1. Verifica que la imagen `not_found.png` esté en la carpeta `img/`
2. Comprueba las rutas en la base de datos
3. Asegúrate de que los permisos de archivo sean correctos

## 📝 Notas adicionales

- La aplicación se adapta automáticamente a tu estructura de base de datos existente
- Si la tabla `categoria` está vacía, se insertarán categorías básicas automáticamente
- Se pueden agregar nuevas funcionalidades fácilmente usando el patrón DAO
- El código está documentado y sigue buenas prácticas de PHP
- La aplicación mantiene compatibilidad con el código existente

## 🤝 Contribución

Para contribuir al proyecto:
1. Fork el repositorio
2. Crea una rama para tu feature
3. Realiza tus cambios
4. Envía un pull request

## 📄 Licencia

Este proyecto es de uso educativo y está disponible bajo licencia MIT.

---

**Desarrollado con ❤️ para el aprendizaje de PHP y bases de datos** 