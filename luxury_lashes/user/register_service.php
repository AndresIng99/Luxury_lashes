<?php
// user/register_service.php - Registrar servicio (Colaborador)

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario esté logueado
requireLogin();

// Si es admin, redirigir a la página de admin
if (isAdmin()) {
    header('Location: ../admin/register_service.php');
    exit;
}

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Obtener datos necesarios
$areas = getAreasServicio();

$message = '';
$message_type = '';

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos
    $errors = [];
    
    $tipo = cleanInput($_POST['tipo'] ?? '');
    $area_id = !empty($_POST['area_id']) ? intval($_POST['area_id']) : null;
    $servicio_id = !empty($_POST['servicio_id']) ? intval($_POST['servicio_id']) : null;
    $producto_personalizado = cleanInput($_POST['producto_personalizado'] ?? '');
    $descripcion = cleanInput($_POST['descripcion'] ?? '');
    $metodo_pago = cleanInput($_POST['metodo_pago'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);
    
    // Validaciones
    if (!in_array($tipo, ['servicio', 'producto'])) {
        $errors[] = 'Tipo de registro inválido';
    }
    
    if ($tipo === 'servicio' && ($area_id === null || $servicio_id === null)) {
        $errors[] = 'Selecciona el área y el servicio';
    }
    
    if ($tipo === 'producto' && empty($producto_personalizado)) {
        $errors[] = 'Describe el producto o servicio personalizado';
    }
    
    if (!in_array($metodo_pago, ['efectivo', 'nequi', 'daviplata'])) {
        $errors[] = 'Selecciona un método de pago válido';
    }
    
    if ($monto <= 0) {
        $errors[] = 'El monto debe ser mayor a cero';
    }
    
    // Si no hay errores, registrar
    if (empty($errors)) {
        $data = [
            'colaborador_id' => $current_user['id'], // Colaborador registra para sí mismo
            'registrado_por' => $current_user['id'],
            'tipo' => $tipo,
            'area_id' => $area_id,
            'servicio_id' => $servicio_id,
            'producto_personalizado' => $producto_personalizado,
            'descripcion' => $descripcion,
            'metodo_pago' => $metodo_pago,
            'monto' => $monto,
            'fecha_servicio' => date('Y-m-d H:i:s')
        ];
        
        $result = registrarServicio($data);
        
        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Limpiar formulario después del éxito
            $_POST = [];
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
    <title>Registrar Servicio - Luxury Lashes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>Luxury Lashes</h1>
                    <p>Portal del Colaborador</p>
                </div>
                <div class="user-info">
                    <div class="welcome-text">
                        Hola, <strong><?php echo htmlspecialchars($current_user['nombres_completos']); ?></strong>
                        <br><small>Colaborador</small>
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
                    <a href="welcome.php" class="nav-link">
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="register_service.php" class="nav-link active">
                        Registrar Servicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my_services.php" class="nav-link">
                        Mis Servicios
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            <!-- Saludo de bienvenida -->
            <div class="card fade-in" style="text-align: center; margin-bottom: 24px;">
                <div style="font-size: 48px; margin-bottom: 16px;">💄</div>
                <h2 style="color: var(--primary-color); margin-bottom: 8px;">
                    ¡Hola, <?php echo htmlspecialchars($current_user['nombres_completos']); ?>!
                </h2>
                <p style="color: var(--text-secondary); font-size: 16px;">
                    Registra los servicios y productos que has realizado hoy
                </p>
            </div>

            <div class="card fade-in">
                <h2 class="card-title">Registrar Servicio o Producto</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerServiceForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                        <!-- Columna Izquierda -->
                        <div>
                            <!-- Tipo de registro -->
                            <div class="form-group">
                                <label class="form-label">¿Qué vas a registrar? *</label>
                                <div style="display: flex; gap: 16px; margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="tipo" value="servicio" id="tipo_servicio" <?php echo (!isset($_POST['tipo']) || $_POST['tipo'] === 'servicio') ? 'checked' : ''; ?> required>
                                        <span style="font-weight: 500;">Servicio realizado</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="tipo" value="producto" id="tipo_producto" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'producto') ? 'checked' : ''; ?> required>
                                        <span style="font-weight: 500;">Producto vendido</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Área de servicio -->
                            <div class="form-group" id="area_group">
                                <label for="area_id" class="form-label">¿En qué área trabajaste? *</label>
                                <select id="area_id" name="area_id" class="form-select">
                                    <option value="">Seleccionar área...</option>
                                    <?php foreach ($areas as $area): ?>
                                        <option value="<?php echo $area['id']; ?>" <?php echo (isset($_POST['area_id']) && $_POST['area_id'] == $area['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($area['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Servicio específico -->
                            <div class="form-group" id="servicio_group">
                                <label for="servicio_id" class="form-label">¿Qué servicio específico? *</label>
                                <select id="servicio_id" name="servicio_id" class="form-select">
                                    <option value="">Primero selecciona un área</option>
                                </select>
                            </div>

                            <!-- Producto personalizado -->
                            <div class="form-group" id="producto_group" style="display: none;">
                                <label for="producto_personalizado" class="form-label">¿Qué producto vendiste? *</label>
                                <input 
                                    type="text" 
                                    id="producto_personalizado" 
                                    name="producto_personalizado" 
                                    class="form-input" 
                                    placeholder="Ej: Crema hidratante facial, Shampoo anticaspa..."
                                    value="<?php echo htmlspecialchars($_POST['producto_personalizado'] ?? ''); ?>"
                                    maxlength="200"
                                >
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div>
                            <!-- Método de pago -->
                            <div class="form-group">
                                <label for="metodo_pago" class="form-label">¿Cómo pagaron? *</label>
                                <select id="metodo_pago" name="metodo_pago" class="form-select" required>
                                    <option value="">Seleccionar método...</option>
                                    <option value="efectivo" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] === 'efectivo') ? 'selected' : ''; ?>>
                                        💵 Efectivo
                                    </option>
                                    <option value="nequi" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] === 'nequi') ? 'selected' : ''; ?>>
                                        📱 Nequí
                                    </option>
                                    <option value="daviplata" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] === 'daviplata') ? 'selected' : ''; ?>>
                                        💳 Daviplata
                                    </option>
                                </select>
                            </div>

                            <!-- Monto -->
                            <div class="form-group">
                                <label for="monto" class="form-label">¿Cuánto cobraste? *</label>
                                <div style="position: relative;">
                                    <input 
                                        type="number" 
                                        id="monto" 
                                        name="monto" 
                                        class="form-input" 
                                        placeholder="50000"
                                        value="<?php echo htmlspecialchars($_POST['monto'] ?? ''); ?>"
                                        min="0"
                                        step="0.01"
                                        required
                                        style="padding-left: 40px;"
                                    >
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                                </div>
                                <small style="color: var(--text-muted); font-size: 12px;">Pesos colombianos (COP)</small>
                            </div>

                            <!-- Descripción adicional -->
                            <div class="form-group">
                                <label for="descripcion" class="form-label">Detalles adicionales (opcional)</label>
                                <textarea 
                                    id="descripcion" 
                                    name="descripcion" 
                                    class="form-input" 
                                    rows="3"
                                    placeholder="¿Algo especial que quieras recordar de este servicio?"
                                    maxlength="500"
                                ><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                                <small style="color: var(--text-muted); font-size: 12px;">Máximo 500 caracteres</small>
                            </div>
                        </div>
                    </div>

                    <!-- Información del registro -->
                    <div class="info-card" style="margin: 32px 0; background: rgb(5 150 105 / 0.05); border-color: rgb(5 150 105 / 0.1);">
                        <h4 style="color: var(--success-color); margin-bottom: 16px;">📝 Información del Registro</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; font-size: 14px;">
                            <div>
                                <strong style="color: var(--text-primary);">Fecha y Hora:</strong>
                                <span style="color: var(--text-secondary);" id="current_datetime"><?php echo date('d/m/Y H:i:s'); ?></span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Colaborador:</strong>
                                <span style="color: var(--success-color); font-weight: 600;"><?php echo htmlspecialchars($current_user['nombres_completos']); ?> (Tú)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div style="display: flex; gap: 16px; justify-content: flex-end; margin-top: 32px; flex-wrap: wrap;">
                        <a href="welcome.php" class="btn btn-secondary">
                            Volver al Inicio
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Registrar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Reutilizar el mismo JavaScript del archivo de administrador
        // (mismo código que en register_service_admin.php)
        
        // Actualizar fecha y hora cada segundo
        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            document.getElementById('current_datetime').textContent = now.toLocaleDateString('es-CO', options);
        }
        
        setInterval(updateDateTime, 1000);

        // Manejar cambios entre servicio y producto
        document.querySelectorAll('input[name="tipo"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const esServicio = this.value === 'servicio';
                
                document.getElementById('area_group').style.display = esServicio ? 'block' : 'none';
                document.getElementById('servicio_group').style.display = esServicio ? 'block' : 'none';
                document.getElementById('producto_group').style.display = esServicio ? 'none' : 'block';
                
                if (esServicio) {
                    document.getElementById('producto_personalizado').value = '';
                } else {
                    document.getElementById('area_id').value = '';
                    document.getElementById('servicio_id').value = '';
                    document.getElementById('servicio_id').innerHTML = '<option value="">No aplica para productos</option>';
                }
            });
        });

        // Cargar servicios cuando cambia el área
        document.getElementById('area_id').addEventListener('change', function() {
            const areaId = this.value;
            const servicioSelect = document.getElementById('servicio_id');
            
            if (!areaId) {
                servicioSelect.innerHTML = '<option value="">Primero selecciona un área</option>';
                return;
            }
            
            servicioSelect.innerHTML = '<option value="">Cargando servicios...</option>';
            
            fetch('../api/get_servicios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'area_id=' + areaId
            })
            .then(response => response.json())
            .then(data => {
                servicioSelect.innerHTML = '<option value="">Seleccionar servicio...</option>';
                data.forEach(servicio => {
                    servicioSelect.innerHTML += `<option value="${servicio.id}">${servicio.nombre}</option>`;
                });
            })
            .catch(error => {
                servicioSelect.innerHTML = '<option value="">Error al cargar servicios</option>';
                console.error('Error:', error);
            });
        });

        // Formatear monto
        document.getElementById('monto').addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value.split('.').length > 2) {
                value = value.substring(0, value.lastIndexOf('.'));
            }
            this.value = value;
        });

        // Validaciones del formulario
        document.getElementById('registerServiceForm').addEventListener('submit', function(e) {
            const tipo = document.querySelector('input[name="tipo"]:checked').value;
            const metodo = document.getElementById('metodo_pago').value;
            const monto = parseFloat(document.getElementById('monto').value);
            
            if (tipo === 'servicio') {
                const area = document.getElementById('area_id').value;
                const servicio = document.getElementById('servicio_id').value;
                
                if (!area) {
                    e.preventDefault();
                    alert('Por favor selecciona el área donde trabajaste');
                    return;
                }
                
                if (!servicio) {
                    e.preventDefault();
                    alert('Por favor selecciona el servicio específico que realizaste');
                    return;
                }
            } else {
                const producto = document.getElementById('producto_personalizado').value.trim();
                
                if (!producto) {
                    e.preventDefault();
                    alert('Por favor describe qué producto vendiste');
                    return;
                }
            }
            
            if (!metodo) {
                e.preventDefault();
                alert('Por favor selecciona cómo te pagaron');
                return;
            }
            
            if (isNaN(monto) || monto <= 0) {
                e.preventDefault();
                alert('Por favor ingresa un monto válido mayor a cero');
                return;
            }
            
            if (!confirm('¿Confirmas que quieres registrar este servicio?')) {
                e.preventDefault();
                return;
            }
        });

        // Inicializar estado del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
            if (tipoSeleccionado) {
                tipoSeleccionado.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>