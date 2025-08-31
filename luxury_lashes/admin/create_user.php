<?php
// admin/create_user.php - Crear nuevo usuario

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener datos del usuario actual
$current_user = getCurrentUser();

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
    
    if (empty($data['password'])) {
        $errors[] = 'La contraseña es obligatoria';
    } elseif (strlen($data['password']) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($data['role']) || !in_array($data['role'], ['admin', 'colaborador'])) {
        $errors[] = 'Selecciona un rol válido';
    }
    
    // Si no hay errores, crear usuario
    if (empty($errors)) {
        $result = createUser($data);
        
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Limpiar formulario
            $data = ['cedula' => '', 'nombres_completos' => '', 'celular' => '', 'email' => '', 'password' => '', 'role' => ''];
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
    <title>Crear Usuario - Luxury Lashes</title>
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
                    <a href="register_service.php" class="nav-link">
                        Registrar Servicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="services_history.php" class="nav-link">
                        Historial de Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="create_user.php" class="nav-link active">
                        Crear Usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link">
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
                <h2 class="card-title">Crear Nuevo Usuario</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="createUserForm">
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
                                    value="<?php echo htmlspecialchars($data['cedula'] ?? ''); ?>"
                                    required
                                    maxlength="12"
                                    pattern="[0-9]+"
                                    title="Solo se permiten números"
                                >
                                <small style="color: var(--text-muted); font-size: 12px;">La cédula será el usuario para iniciar sesión</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nombres_completos" class="form-label">Nombres Completos *</label>
                                <input 
                                    type="text" 
                                    id="nombres_completos" 
                                    name="nombres_completos" 
                                    class="form-input" 
                                    placeholder="Ej: María José García López"
                                    value="<?php echo htmlspecialchars($data['nombres_completos'] ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($data['celular'] ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                    required
                                    maxlength="100"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">Contraseña *</label>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Mínimo 6 caracteres"
                                    required
                                    minlength="6"
                                >
                                <small style="color: var(--text-muted); font-size: 12px;">Mínimo 6 caracteres</small>
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
                                    <option value="colaborador" <?php echo (isset($data['role']) && $data['role'] === 'colaborador') ? 'selected' : ''; ?>>
                                        Colaborador
                                    </option>
                                    <option value="admin" <?php echo (isset($data['role']) && $data['role'] === 'admin') ? 'selected' : ''; ?>>
                                        Administrador
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información sobre roles -->
                    <div class="info-card" style="margin: 24px 0;">
                        <h4 style="margin-bottom: 16px; color: var(--text-primary);">Información sobre los Roles</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div style="background: rgb(37 99 235 / 0.05); padding: 16px; border-radius: 8px; border: 1px solid rgb(37 99 235 / 0.1);">
                                <strong style="color: var(--primary-color);">Administrador</strong>
                                <ul style="margin: 8px 0 0 16px; color: var(--text-secondary); font-size: 13px;">
                                    <li>Acceso completo al sistema</li>
                                    <li>Crear y gestionar usuarios</li>
                                    <li>Ver estadísticas y reportes</li>
                                    <li>Registrar servicios para cualquier colaborador</li>
                                </ul>
                            </div>
                            <div style="background: rgb(5 150 105 / 0.05); padding: 16px; border-radius: 8px; border: 1px solid rgb(5 150 105 / 0.1);">
                                <strong style="color: var(--success-color);">Colaborador</strong>
                                <ul style="margin: 8px 0 0 16px; color: var(--text-secondary); font-size: 13px;">
                                    <li>Registrar sus propios servicios</li>
                                    <li>Ver historial personal de servicios</li>
                                    <li>Acceso a estadísticas personales</li>
                                    <li>Interfaz simplificada y amigable</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div style="display: flex; gap: 16px; justify-content: flex-end; margin-top: 32px; flex-wrap: wrap;">
                        <a href="dashboard.php" class="btn btn-secondary">
                            Volver al Dashboard
                        </a>
                        <button type="submit" class="btn btn-success">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Validaciones del formulario
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
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
            
            // Validar contraseña
            if (password.length < 6) {
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
            
            // Confirmación antes de crear
            if (!confirm('¿Estás seguro de crear este usuario?')) {
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

        // Mostrar/ocultar información del rol seleccionado
        document.getElementById('role').addEventListener('change', function() {
            const selectedRole = this.value;
            const infoCards = document.querySelectorAll('.info-card > div > div');
            
            infoCards.forEach(card => {
                card.style.opacity = '0.5';
            });
            
            if (selectedRole === 'admin') {
                infoCards[0].style.opacity = '1';
                infoCards[0].style.transform = 'scale(1.02)';
                infoCards[0].style.transition = 'all 0.3s ease';
            } else if (selectedRole === 'colaborador') {
                infoCards[1].style.opacity = '1';
                infoCards[1].style.transform = 'scale(1.02)';
                infoCards[1].style.transition = 'all 0.3s ease';
            }
            
            setTimeout(() => {
                infoCards.forEach(card => {
                    card.style.transform = 'scale(1)';
                });
            }, 300);
        });
    </script>
</body>
</html>