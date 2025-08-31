<?php
// admin/dashboard.php - Dashboard del administrador

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Obtener estadísticas
$stats = getUserStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Luxury Lashes</title>
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
                    <a href="dashboard.php" class="nav-link active">
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
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total de Usuarios</div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-number"><?php echo $stats['admin']; ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
                <div class="stat-card colaborador">
                    <div class="stat-number"><?php echo $stats['colaborador']; ?></div>
                    <div class="stat-label">Colaboradores</div>
                </div>
                <div class="stat-card" style="border-left-color: var(--danger-color);">
                    <div class="stat-number" style="color: var(--danger-color);"><?php echo $stats['inactivos']; ?></div>
                    <div class="stat-label">Usuarios Inactivos</div>
                </div>
            </div>

            <!-- Panel de Bienvenida -->
            <div class="card fade-in">
                <h2 class="card-title">Bienvenido al Panel de Administración</h2>
                <p style="margin-bottom: 32px; color: var(--text-secondary); line-height: 1.6;">
                    Desde aquí puedes gestionar todos los aspectos del sistema Luxury Lashes de forma centralizada y eficiente.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <div class="info-card">
                        <h4 style="color: var(--primary-color);">
                            <span style="background: rgb(37 99 235 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">💄</span>
                            Registrar Servicios
                        </h4>
                        <p style="margin-bottom: 20px;">
                            Registra servicios realizados por ti o por cualquier colaborador del equipo.
                        </p>
                        <a href="register_service.php" class="btn btn-primary">
                            Registrar Servicio
                        </a>
                    </div>
                    
                    <div class="info-card">
                        <h4 style="color: var(--success-color);">
                            <span style="background: rgb(5 150 105 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">📊</span>
                            Ver Historial
                        </h4>
                        <p style="margin-bottom: 20px;">
                            Consulta el historial completo de servicios realizados y estadísticas.
                        </p>
                        <a href="services_history.php" class="btn btn-success">
                            Ver Historial
                        </a>
                    </div>
                    
                    <div class="info-card">
                        <h4 style="color: var(--text-primary);">
                            <span style="background: rgb(100 116 139 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">👥</span>
                            Gestionar Usuarios
                        </h4>
                        <p style="margin-bottom: 20px;">
                            Administra usuarios, roles y permisos del sistema.
                        </p>
                        <a href="manage_users.php" class="btn btn-secondary">
                            Gestionar Usuarios
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="card fade-in">
                <h3 class="card-title">Resumen del Sistema</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                    
                    <!-- Estado General -->
                    <div class="info-card" style="background: rgb(5 150 105 / 0.05); border-color: rgb(5 150 105 / 0.1);">
                        <h4 style="color: var(--success-color);">Estado General</h4>
                        <div style="font-size: 14px; color: var(--text-secondary);">
                            <p style="margin-bottom: 8px;">
                                <strong style="color: var(--text-primary);">Sistema:</strong> 
                                <span style="color: var(--success-color); font-weight: 600;">Operativo</span>
                            </p>
                            <p style="margin-bottom: 8px;">
                                <strong style="color: var(--text-primary);">Usuarios Activos:</strong> 
                                <?php echo $stats['total_activos']; ?>
                            </p>
                            <p style="margin-bottom: 0;">
                                <strong style="color: var(--text-primary);">Última Actualización:</strong> 
                                <?php echo date('d/m/Y H:i'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Distribución de Roles -->
                    <div class="info-card" style="background: rgb(37 99 235 / 0.05); border-color: rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color);">Distribución de Roles</h4>
                        <div style="font-size: 14px; color: var(--text-secondary);">
                            <?php if ($stats['total'] > 0): ?>
                                <p style="margin-bottom: 8px;">
                                    <strong style="color: var(--text-primary);">Administradores:</strong> 
                                    <?php echo round(($stats['admin'] / $stats['total']) * 100, 1); ?>%
                                </p>
                                <p style="margin-bottom: 0;">
                                    <strong style="color: var(--text-primary);">Colaboradores:</strong> 
                                    <?php echo round(($stats['colaborador'] / $stats['total']) * 100, 1); ?>%
                                </p>
                            <?php else: ?>
                                <p style="margin-bottom: 0; font-style: italic;">No hay datos disponibles</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="info-card">
                        <h4 style="color: var(--text-primary);">Acciones Rápidas</h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="create_user.php" class="btn btn-primary" style="font-size: 13px; padding: 8px 12px;">
                                Crear Usuario
                            </a>
                            <a href="manage_users.php" class="btn btn-secondary" style="font-size: 13px; padding: 8px 12px;">
                                Ver Todos los Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            <div class="card fade-in">
                <h3 class="card-title">Información del Sistema</h3>
                <div class="info-card" style="margin: 0;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; font-size: 14px;">
                        <div>
                            <strong style="color: var(--text-primary);">Versión:</strong>
                            <span style="color: var(--text-secondary);">1.0.0</span>
                        </div>
                        <div>
                            <strong style="color: var(--text-primary);">Estado:</strong>
                            <span style="color: var(--success-color); font-weight: 600;">Activo</span>
                        </div>
                        <div>
                            <strong style="color: var(--text-primary);">Último acceso:</strong>
                            <span style="color: var(--text-secondary);"><?php echo date('d/m/Y H:i:s'); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--text-primary);">Usuario actual:</strong>
                            <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($current_user['nombres_completos']) . ' (' . $current_user['cedula'] . ')'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consejos y Ayuda -->
            <div class="card fade-in">
                <h3 class="card-title">Consejos de Uso</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    
                    <div class="info-card" style="background: rgb(217 119 6 / 0.05); border-color: rgb(217 119 6 / 0.1);">
                        <h4 style="color: var(--warning-color);">Seguridad</h4>
                        <ul style="margin-left: 0; list-style: none; padding-left: 0; font-size: 13px; color: var(--text-secondary);">
                            <li style="margin-bottom: 6px;">• Revisa regularmente los usuarios activos</li>
                            <li style="margin-bottom: 6px;">• Deshabilita cuentas no utilizadas</li>
                            <li style="margin-bottom: 0;">• Mantén actualizada la información de contacto</li>
                        </ul>
                    </div>

                    <div class="info-card" style="background: rgb(37 99 235 / 0.05); border-color: rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color);">Gestión</h4>
                        <ul style="margin-left: 0; list-style: none; padding-left: 0; font-size: 13px; color: var(--text-secondary);">
                            <li style="margin-bottom: 6px;">• Usa filtros para encontrar usuarios rápidamente</li>
                            <li style="margin-bottom: 6px;">• Asigna roles apropiados según las responsabilidades</li>
                            <li style="margin-bottom: 0;">• Monitorea las estadísticas regularmente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Actualizar la hora cada segundo en la información del sistema
        function updateTime() {
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
            const timeString = now.toLocaleDateString('es-CO', options);
            
            // Buscar el elemento de último acceso y actualizarlo
            const timeElements = document.querySelectorAll('span');
            timeElements.forEach(element => {
                if (element.textContent.includes(':') && element.textContent.includes('/')) {
                    element.textContent = timeString;
                }
            });
        }
        
        // Actualizar cada segundo
        setInterval(updateTime, 1000);
        
        // Animación de entrada para las estadísticas
        window.addEventListener('load', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });

        // Efecto hover para las cards de información
        document.querySelectorAll('.info-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Console log para desarrollo
        console.log('🎉 Dashboard de Luxury Lashes cargado correctamente');
        console.log('👤 Usuario:', '<?php echo addslashes($current_user['nombres_completos']); ?>');
        console.log('📊 Estadísticas:', {
            total: <?php echo $stats['total']; ?>,
            admin: <?php echo $stats['admin']; ?>,
            colaborador: <?php echo $stats['colaborador']; ?>,
            inactivos: <?php echo $stats['inactivos']; ?>
        });
    </script>
</body>
</html>