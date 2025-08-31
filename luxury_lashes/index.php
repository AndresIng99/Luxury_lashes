<?php
// index.php - Página principal de login
session_start();

// Si ya está logueado, redirigir según su rol
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/welcome.php');
    }
    exit;
}

// Verificar si hay mensajes de error o éxito
$error_message = '';
$success_message = '';

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Luxury Lashes</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form fade-in" action="login.php" method="POST">
            <div class="logo">
                <h1>Luxury Lashes</h1>
                <p>Sistema de Gestión</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="cedula" class="form-label">Cédula</label>
                <input 
                    type="text" 
                    id="cedula" 
                    name="cedula" 
                    class="form-input" 
                    placeholder="Ingresa tu número de cédula"
                    required
                    maxlength="12"
                    pattern="[0-9]+"
                    title="Solo se permiten números"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Ingresa tu contraseña"
                    required
                    minlength="6"
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Iniciar Sesión
            </button>
            
            <div class="text-center mt-3">
                <small style="color: var(--text-muted);">
                    ¿Problemas para acceder? Contacta al administrador
                </small>
            </div>
            
        </form>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('.login-form').addEventListener('submit', function(e) {
            const cedula = document.getElementById('cedula').value;
            const password = document.getElementById('password').value;
            
            // Validar que la cédula solo contenga números
            if (!/^[0-9]+$/.test(cedula)) {
                e.preventDefault();
                alert('La cédula solo debe contener números');
                return;
            }
            
            // Validar longitud mínima de cédula
            if (cedula.length < 6) {
                e.preventDefault();
                alert('La cédula debe tener al menos 6 dígitos');
                return;
            }
            
            // Validar longitud mínima de contraseña
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
        });

        // Solo permitir números en el campo cédula
        document.getElementById('cedula').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>