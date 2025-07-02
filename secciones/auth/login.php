<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php?msg=logout_first');
    exit;
}
// La lógica de procesamiento del login se mantiene igual,
// pero se ejecutará dentro del contexto de index.php

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM usuarios WHERE email = ?", [$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
                $_SESSION['usuario_nivel'] = $usuario['Nivel'];
                if ($_SESSION['usuario_nivel'] === 'Admin') {
                    header('Location: index.php?sec=admin/admin_productos');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'El email o la contraseña son incorrectos.';
            }
        } catch (Exception $e) {
            $error = 'Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.';
            error_log('Error de login: ' . $e->getMessage());
        }
    }
}
?>

<div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="login-container">
        <div class="text-center mb-4">
            <a href="index.php" class="logo-link">
                <h3><i class="bi bi-shield-shaded"></i> FunkoManía Admin</h3>
            </a>
            <p class="text-muted">Inicia sesión para gestionar la tienda.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?sec=auth/login">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg btn-tematico w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                </button>
            </div>
        </form>
         <div class="text-center mt-4">
            <a href="index.php" class="text-decoration-none">← Volver a la tienda</a>
        </div>
    </div>
</div> 