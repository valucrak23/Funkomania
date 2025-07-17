<?php
require_once __DIR__ . '/../clases/UsuarioDAO.php';

$dao = new UsuarioDAO();
$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if (!$dao->validarNombre($nombre)) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    }
    if (!$dao->validarEmail($email)) {
        $errores[] = 'El email no es válido.';
    }
    if ($dao->emailExiste($email)) {
        $errores[] = 'El email ya está registrado.';
    }
    if (!$dao->validarPassword($password)) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $password2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $ok = $dao->insertar($nombre, $email, $password, 'User');
        if ($ok) {
            $exito = true;
        } else {
            $errores[] = 'No se pudo crear el usuario. Intenta con otro email.';
        }
    }
}
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h3 class="mb-3 text-center"><i class="bi bi-person-plus"></i> Crear cuenta nueva</h3>
        <?php if ($exito): ?>
            <div class="alert alert-success text-center">
                <i class="bi bi-check-circle"></i> ¡Usuario creado exitosamente!<br>
                <a href="index.php?sec=auth/login" class="btn btn-success mt-2 w-100">Ir a Iniciar sesión</a>
            </div>
        <?php else: ?>
            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="password2" class="form-label">Repetir contraseña</label>
                    <input type="password" class="form-control" id="password2" name="password2" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
                <a href="index.php?sec=auth/login" class="btn btn-link w-100 mt-2">¿Ya tienes cuenta? Inicia sesión</a>
            </form>
        <?php endif; ?>
    </div>
</div> 