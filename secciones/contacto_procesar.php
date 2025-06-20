<section>
    <h2>Resumen de contacto</h2>
    <p><strong>Nombre y Apellido:</strong> <?=$_POST['nombre'] ?? ''?></p>
    <p><strong>Correo electrónico:</strong> <?=$_POST['email'] ?? ''?></p>
    <p><strong>Mensaje:</strong> <?=$_POST['mensaje'] ?? ''?></p>
    <a href="../index.php?sec=contacto" class="btn btn-secondary">Volver</a>
</section>