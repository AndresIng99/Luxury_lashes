<?php
// user/welcome.php - Pantalla de bienvenida para colaboradores

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario esté logueado
requireLogin();

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Si es admin, redirigir al dashboard
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Luxury Lashes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>💄 Luxury Lashes</h1>
                    <p>Portal del Colaborador</p>
                </div>
                <div class="user-info">
                    <div class="welcome-text">
                        Hola, <strong><?php echo htmlspecialchars($current_user['nombres_completos']); ?></strong>
                        <br><small>Colaborador</small>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        🚪 Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            <div class="welcome-container">
                <div class="welcome-card fade-in">
                    <div class="welcome-icon">👋</div>
                    <h1 class="welcome-title">¡Bienvenido!</h1>
                    <h2 class="welcome-subtitle"><?php echo htmlspecialchars($current_user['nombres_completos']); ?></h2>
                    <p class="welcome-text">
                        Te damos la bienvenida al sistema de gestión de <strong>Luxury Lashes</strong>. 
                        Tu cuenta de colaborador está activa y lista para usar.
                    </p>
                    
                    <div class="info-card" style="background: rgb(37 99 235 / 0.05); border-color: rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color); text-align: left;">Tu Información</h4>
                        <div style="text-align: left; color: var(--text-secondary); font-size: 14px;">
                            <p style="margin-bottom: 8px;"><strong style="color: var(--text-primary);">Cédula:</strong> <?php echo htmlspecialchars($current_user['cedula']); ?></p>
                            <p style="margin-bottom: 8px;"><strong style="color: var(--text-primary);">Rol:</strong> <span class="role-badge role-colaborador">Colaborador</span></p>
                            <p style="margin-bottom: 0;"><strong style="color: var(--text-primary);">Estado:</strong> <span style="color: var(--success-color); font-weight: 600;">Activo</span></p>
                        </div>
                    </div>
                    
                    <div class="info-card" style="background: rgb(5 150 105 / 0.05); border-color: rgb(5 150 105 / 0.1);">
                        <h4 style="color: var(--success-color); text-align: left;">¿Qué puedes hacer ahora?</h4>
                        <div style="text-align: left; color: var(--text-secondary); font-size: 14px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
                                <a href="register_service.php" class="btn btn-primary" style="text-decoration: none;">
                                    💄 Registrar Servicio
                                </a>
                                <a href="my_services.php" class="btn btn-secondary" style="text-decoration: none;">
                                    📋 Ver Mis Servicios
                                </a>
                            </div>
                        </div>
                    </div>                            </ul>
                        </div>
                    </div>
                    
                    <!-- Información de contacto -->
                    <div class="info-card" style="background: rgb(217 119 6 / 0.05); border-color: rgb(217 119 6 / 0.1);">
                        <h4 style="color: var(--warning-color); text-align: left;">¿Necesitas Ayuda?</h4>
                        <div style="text-align: left; color: var(--text-secondary); font-size: 14px;">
                            <p style="margin-bottom: 8px;">Si tienes preguntas sobre el registro de servicios:</p>
                            <ul style="margin-left: 0; list-style: none; padding-left: 0;">
                                <li style="margin-bottom: 4px;">• Contacta al administrador del sistema</li>
                                <li style="margin-bottom: 0;">• O reporta cualquier problema técnico</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="margin-top: 32px; text-align: center;">
                        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">
                            Fecha y hora actual: <span id="currentDateTime" style="font-weight: 600; color: var(--text-secondary);"></span>
                        </p>
                        
                        <a href="../logout.php" class="btn btn-secondary">
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Actualizar fecha y hora en tiempo real
        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'America/Bogota'
            };
            
            const dateTimeString = now.toLocaleDateString('es-CO', options);
            document.getElementById('currentDateTime').textContent = dateTimeString;
        }
        
        // Actualizar cada segundo
        setInterval(updateDateTime, 1000);
        
        // Actualizar inmediatamente
        updateDateTime();
        
        // Animación de bienvenida
        window.addEventListener('load', function() {
            const card = document.querySelector('.welcome-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            
            setTimeout(function() {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 200);
        });
        
        // Mostrar mensaje de bienvenida personalizado
        setTimeout(function() {
            const userName = '<?php echo addslashes($current_user['nombres_completos']); ?>';
            const welcomeMessages = [
                `¡Hola ${userName}! 🎉 Bienvenido al sistema Luxury Lashes`,
                `¡Qué gusto verte, ${userName}! ✨ Tu sesión está activa`,
                `¡Excelente día, ${userName}! 🌟 El sistema está listo para ti`
            ];
            
            const randomMessage = welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
            
            // Solo mostrar si no se ha mostrado antes en esta sesión
            if (!sessionStorage.getItem('welcome_message_shown')) {
                alert(randomMessage);
                sessionStorage.setItem('welcome_message_shown', 'true');
            }
        }, 1500);
        
        // Efecto de hover en las cards informativas
        document.querySelectorAll('[style*="background: #f8f9ff"], [style*="background: #e8f5e8"], [style*="background: #fff3cd"]').forEach(function(card) {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.transition = 'transform 0.3s ease';
                this.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>