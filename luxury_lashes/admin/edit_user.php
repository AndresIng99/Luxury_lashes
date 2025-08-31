<?php
// admin/edit_user.php - Editar usuario existente

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener datos del usuario actual (quien está editando)
$current_user = getCurrentUser();

// Verificar que se haya proporcionado un ID de usuario
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = 'ID de usuario inválido';
    header('Location: manage_users.php');
    exit;
}

$userId = intval($_GET['id']);

// Obtener datos del usuario a editar
$user_to_edit = getUserForEdit($userId);

if (!$user_to_edit) {
    $_SESSION['error_message'] = 'Usuario no encontrado';
    header('Location: manage_users.php');
    exit;
}

$message = '';
$message_type = '';

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar datos
    $data = [
        'cedula' => cleanInput($_POST['cedula'] ?? ''),
        'nombres_completos' => cleanInput($_POST['nombres_completos'] ?? ''),
        'celular' => cleanInput($_POST['celular'] ?? ''),
        'email' => cleanInput($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'role' => cleanInput($_POST['role'] ?? '')
    ];
    
    // Validaciones
    $errors = [];
    
    if (empty($data['cedula'])) {
        $errors[] = 'La cédula es obligatoria';
    } elseif (!validateCedula($data['cedula'])) {
        $errors[] = 'Formato de cédula inválido (6-12 dígitos)';
    }
    
    if (empty($data['nombres_completos'])) {
        $errors[] = 'El nombre completo es obligatorio';
    }
    
    if (empty($data['celular'])) {
        $errors[] = 'El número de celular es obligatorio';
    } elseif (!validateCelular($data['celular'])) {
        $errors[] = 'Formato de celular inválido (10-15 dígitos)';
    }
    
    if (empty($data['email'])) {
        $errors[] = 'El email es obligatorio';
    } elseif (!validateEmail($data['email'])) {
        $errors[] = 'Formato de email inválido';
    }
    
    // Validar contraseña solo si se proporcionó
    if (!empty($data['password']) && strlen($data['password']) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($data['role']) || !in_array($data['role'], ['admin', 'colaborador'])) {
        $errors[] = 'Selecciona un rol válido';
    }
    
    // Si no hay errores, actualizar usuario
    if (empty($errors)) {
        $result = updateUser($userId, $data);
        
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Actualizar datos mostrados en el formulario
            $user_to_edit = getUserForEdit($userId);
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Luxury Lashes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Luxury Lashes</h1>
                    <p>Panel de Administración</p>
                </div>
                <div class="user-info">
                    <div class="welcome-text">
                        Hola, <strong><?php echo htmlspecialchars($current_user['nombres_completos']); ?></strong>
                        <br><small>Administrador</small>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegación -->
    <nav class="nav">
        <div class="container">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="create_user.php" class="nav-link">
                        Crear Usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link active">
                        Gestionar Usuarios
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            <div class="card fade-in">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2 class="card-title mb-0">Editar Usuario</h2>
                        <p style="color: var(--text-muted); font-size: 14px; margin: 4px 0 0 0;">
                            Modificando: <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($user_to_edit['nombres_completos']); ?></strong>
                        </p>
                    </div>
                    <a href="manage_users.php" class="btn btn-secondary">
                        Volver a Gestión
                    </a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="editUserForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                        <!-- Columna Izquierda -->
                        <div>
                            <div class="form-group">
                                <label for="cedula" class="form-label">Cédula *</label>
                                <input 
                                    type="text" 
                                    id="cedula" 
                                    name="cedula" 
                                    class="form-input" 
                                    placeholder="Ej: 12345678"
                                    value="<?php echo htmlspecialchars($user_to_edit['cedula']); ?>"
                                    required
                                    maxlength="12"
                                    pattern="[0-9]+"
                                    title="Solo se permiten números"
                                >
                                <small style="color: var(--text-muted); font-size: 12px;">La cédula es el usuario para iniciar sesión</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nombres_completos" class="form-label">Nombres Completos *</label>
                                <input 
                                    type="text" 
                                    id="nombres_completos" 
                                    name="nombres_completos" 
                                    class="form-input" 
                                    placeholder="Ej: María José García López"
                                    value="<?php echo htmlspecialchars($user_to_edit['nombres_completos']); ?>"
                                    required
                                    maxlength="100"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="celular" class="form-label">Número de Celular *</label>
                                <input 
                                    type="text" 
                                    id="celular" 
                                    name="celular" 
                                    class="form-input" 
                                    placeholder="Ej: 3001234567"
                                    value="<?php echo htmlspecialchars($user_to_edit['celular']); ?>"
                                    required
                                    maxlength="15"
                                    pattern="[0-9]+"
                                    title="Solo se permiten números"
                                >
                            </div>
                        </div>
                        
                        <!-- Columna Derecha -->
                        <div>
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico *</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="Ej: maria@ejemplo.com"
                                    value="<?php echo htmlspecialchars($user_to_edit['email']); ?>"
                                    required
                                    maxlength="100"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Dejar vacío para mantener la actual"
                                    minlength="6"
                                >
                                <small style="color: var(--text-muted); font-size: 12px;">Dejar vacío para no cambiar la contraseña</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="role" class="form-label">Rol de Usuario *</label>
                                <select 
                                    id="role" 
                                    name="role" 
                                    class="form-select" 
                                    required
                                >
                                    <option value="">Seleccionar rol...</option>
                                    <option value="colaborador" <?php echo ($user_to_edit['role'] === 'colaborador') ? 'selected' : ''; ?>>
                                        Colaborador
                                    </option>
                                    <option value="admin" <?php echo ($user_to_edit['role'] === 'admin') ? 'selected' : ''; ?>>
                                        Administrador
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información actual del usuario -->
                    <div class="info-card" style="margin: 32px 0;">
                        <h4 style="color: var(--text-primary); margin-bottom: 16px;">Información Actual del Usuario</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 14px;">
                            <div>
                                <strong style="color: var(--text-primary);">Estado:</strong>
                                <span class="role-badge <?php echo $user_to_edit['status'] === 'activo' ? 'role-colaborador' : ''; ?>" 
                                      style="<?php echo $user_to_edit['status'] === 'inactivo' ? 'background: rgb(220 38 38 / 0.1); color: var(--danger-color); margin-left: 8px;' : 'margin-left: 8px;'; ?>">
                                    <?php echo $user_to_edit['status'] === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Rol Actual:</strong>
                                <span class="role-badge role-<?php echo $user_to_edit['role']; ?>" style="margin-left: 8px;">
                                    <?php echo $user_to_edit['role'] === 'admin' ? 'Admin' : 'Colaborador'; ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">ID:</strong>
                                <span style="color: var(--text-secondary);">#<?php echo $user_to_edit['id']; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advertencias importantes -->
                    <?php if ($user_to_edit['id'] == $current_user['id']): ?>
                        <div class="alert alert-info">
                            <strong>⚠️ Advertencia:</strong> Estás editando tu propia cuenta. Ten cuidado al cambiar tu cédula o rol.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Botones -->
                    <div style="display: flex; gap: 16px; justify-content: flex-end; margin-top: 32px; flex-wrap: wrap;">
                        <a href="manage_users.php" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Validaciones del formulario
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            const cedula = document.getElementById('cedula').value;
            const nombres = document.getElementById('nombres_completos').value;
            const celular = document.getElementById('celular').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            
            // Validar cédula
            if (!/^[0-9]{6,12}$/.test(cedula)) {
                e.preventDefault();
                alert('La cédula debe contener entre 6 y 12 dígitos');
                return;
            }
            
            // Validar nombre
            if (nombres.trim().length < 3) {
                e.preventDefault();
                alert('El nombre debe tener al menos 3 caracteres');
                return;
            }
            
            // Validar celular
            if (!/^[0-9]{10,15}$/.test(celular)) {
                e.preventDefault();
                alert('El celular debe contener entre 10 y 15 dígitos');
                return;
            }
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, ingresa un email válido');
                return;
            }
            
            // Validar contraseña solo si se proporciona
            if (password && password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            // Validar rol
            if (!role) {
                e.preventDefault();
                alert('Por favor, selecciona un rol');
                return;
            }
            
            // Confirmación antes de actualizar
            const confirmMessage = password ? 
                '¿Estás seguro de actualizar este usuario? Se cambiará la contraseña también.' :
                '¿Estás seguro de actualizar este usuario?';
                
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return;
            }
        });

        // Solo permitir números en cédula y celular
        document.getElementById('cedula').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('celular').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Capitalizar nombres
        document.getElementById('nombres_completos').addEventListener('input', function(e) {
            let value = this.value;
            // Capitalizar cada palabra
            value = value.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            this.value = value;
        });

        // Convertir email a minúsculas
        document.getElementById('email').addEventListener('input', function(e) {
            this.value = this.value.toLowerCase();
        });

        // Resaltar el campo de contraseña cuando se enfoca
        document.getElementById('password').addEventListener('focus', function() {
            this.parentNode.style.background = 'rgb(217 119 6 / 0.05)';
            this.parentNode.style.borderRadius = '8px';
            this.parentNode.style.padding = '8px';
            this.parentNode.style.transition = 'all 0.2s ease';
        });

        document.getElementById('password').addEventListener('blur', function() {
            this.parentNode.style.background = '';
            this.parentNode.style.padding = '';
        });
    </script>
</body>
</html>