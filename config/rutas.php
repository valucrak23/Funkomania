<?php
/**
 * Configuración de rutas del sistema
 */
return [
    // Rutas públicas
    'inicio' => 'secciones/inicio.php',
    'productos' => 'secciones/productos.php',
    'detalle' => 'secciones/detalle.php',
    'contacto' => 'secciones/contacto.php',
    'contacto_procesar' => 'secciones/contacto_procesar.php',
    'carrito' => 'secciones/carrito.php',
    'checkout' => 'secciones/checkout.php',
    'historial' => 'secciones/historial.php',
    'detalle_orden' => 'secciones/detalle_orden.php',
    'alumno' => 'secciones/alumno.php',
    '404' => 'secciones/404.php',
    
    // Rutas de administración
    'admin/admin_productos' => 'secciones/admin/admin_productos.php',
    'admin/agregar_producto' => 'secciones/admin/agregar_producto.php',
    'admin/admin_categorias' => 'secciones/admin/admin_categorias.php',
    'admin/agregar_categoria' => 'secciones/admin/agregar_categoria.php',
    'admin/admin_usuarios' => 'secciones/admin/admin_usuarios.php',
    'admin/agregar_usuario' => 'secciones/admin/agregar_usuario.php',
    
    // Rutas de autenticación
    'auth/login' => 'secciones/auth/login.php',
    'auth/logout' => 'secciones/auth/logout.php',
    
    // Rutas de registro (públicas)
    'registro' => 'secciones/registro.php',
];
?> 