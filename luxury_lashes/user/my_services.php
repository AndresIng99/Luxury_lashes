<?php
// user/my_services.php - Ver mis servicios (Colaborador)

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario esté logueado
requireLogin();

// Si es admin, redirigir a la página de admin
if (isAdmin()) {
    header('Location: ../admin/services_history.php');
    exit;
}

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Obtener servicios del colaborador
$servicios = getHistorialServicios($current_user['id'], 100);

// Obtener estadísticas del colaborador
$estadisticas = getEstadisticasServicios($current_user['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Servicios - Luxury Lashes</title>
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
                    <a href="register_service.php" class="nav-link">
                        Registrar Servicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my_services.php" class="nav-link active">
                        Mis Servicios
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            <!-- Estadísticas personales -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $estadisticas['total_servicios']; ?></div>
                    <div class="stat-label">Total de Servicios</div>
                </div>
                <div class="stat-card admin" style="border-left-color: var(--success-color);">
                    <div class="stat-number" style="color: var(--success-color);">
                        $<?php echo number_format($estadisticas['total_ingresos'], 0, ',', '.'); ?>
                    </div>
                    <div class="stat-label">Ingresos Generados</div>
                </div>
                <div class="stat-card colaborador">
                    <div class="stat-number">
                        <?php 
                        $servicios_count = 0;
                        $productos_count = 0;
                        foreach ($estadisticas['por_tipo'] as $tipo) {
                            if ($tipo['tipo'] === 'servicio') $servicios_count = $tipo['cantidad'];
                            if ($tipo['tipo'] === 'producto') $productos_count = $tipo['cantidad'];
                        }
                        echo $servicios_count;
                        ?>
                    </div>
                    <div class="stat-label">Servicios Realizados</div>
                </div>
                <div class="stat-card" style="border-left-color: var(--warning-color);">
                    <div class="stat-number" style="color: var(--warning-color);">
                        <?php echo $productos_count; ?>
                    </div>
                    <div class="stat-label">Productos Vendidos</div>
                </div>
            </div>

            <!-- Historial de servicios -->
            <div class="card fade-in">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
                    <h2 class="card-title mb-0">Tu Historial de Servicios</h2>
                    <a href="register_service.php" class="btn btn-primary">
                        Registrar Nuevo Servicio
                    </a>
                </div>

                <?php if (empty($servicios)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                        <div style="font-size: 4rem; margin-bottom: 24px;">💄</div>
                        <h3 style="color: var(--text-secondary); margin-bottom: 16px;">Aún no has registrado servicios</h3>
                        <p style="margin-bottom: 24px; color: var(--text-muted);">
                            Comienza registrando tu primer servicio del día
                        </p>
                        <a href="register_service.php" class="btn btn-primary">
                            Registrar Mi Primer Servicio
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Filtro de búsqueda -->
                    <div style="margin-bottom: 24px;">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="form-input" 
                            placeholder="Buscar en mis servicios..."
                            style="max-width: 400px;"
                        >
                    </div>

                    <!-- Lista de servicios -->
                    <div class="table-container">
                        <table class="table" id="servicesTable">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Servicio/Producto</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                    <tr data-tipo="<?php echo $servicio['tipo']; ?>">
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?php echo date('d/m/Y', strtotime($servicio['fecha_servicio'])); ?>
                                            </div>
                                            <div style="font-size: 12px; color: var(--text-muted);">
                                                <?php echo date('H:i', strtotime($servicio['fecha_servicio'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge <?php echo $servicio['tipo'] === 'servicio' ? 'role-colaborador' : 'role-admin'; ?>">
                                                <?php echo $servicio['tipo'] === 'servicio' ? 'Servicio' : 'Producto'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($servicio['tipo'] === 'servicio'): ?>
                                                <div style="font-weight: 500; color: var(--text-primary);">
                                                    <?php echo htmlspecialchars($servicio['servicio_nombre'] ?? 'Servicio no especificado'); ?>
                                                </div>
                                                <div style="font-size: 12px; color: var(--text-muted);">
                                                    <?php echo htmlspecialchars($servicio['area_nombre'] ?? ''); ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="font-weight: 500; color: var(--text-primary);">
                                                    <?php echo htmlspecialchars($servicio['producto_personalizado']); ?>
                                                </div>
                                                <div style="font-size: 12px; color: var(--text-muted);">
                                                    Producto personalizado
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 13px;">
                                                <?php
                                                $iconos = [
                                                    'efectivo' => '💵',
                                                    'nequi' => '📱',
                                                    'daviplata' => '💳'
                                                ];
                                                echo $iconos[$servicio['metodo_pago']] ?? '💰';
                                                ?>
                                                <?php echo ucfirst($servicio['metodo_pago']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: var(--success-color);">
                                                $<?php echo number_format($servicio['monto'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($servicio['descripcion'])): ?>
                                                <div style="max-width: 200px; font-size: 13px; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 50)); ?>
                                                    <?php if (strlen($servicio['descripcion']) > 50): ?>...<?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic; font-size: 12px;">
                                                    Sin detalles
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen -->
                    <div style="margin-top: 24px; padding: 20px; background: var(--bg-secondary); border-radius: var(--radius-lg); font-size: 14px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div>
                                <strong style="color: var(--text-primary);">Total de registros:</strong>
                                <span style="color: var(--text-secondary);"><?php echo count($servicios); ?></span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Último registro:</strong>
                                <span style="color: var(--text-secondary);">
                                    <?php 
                                    if (!empty($servicios)) {
                                        echo date('d/m/Y H:i', strtotime($servicios[0]['fecha_servicio']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Función de búsqueda
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('servicesTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            let visibleRows = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
                if (found) visibleRows++;
            }
            
            // Mensaje si no hay resultados
            const tbody = table.getElementsByTagName('tbody')[0];
            const existingMessage = document.getElementById('noResultsMessage');
            
            if (existingMessage) {
                existingMessage.remove();
            }
            
            if (visibleRows === 0 && searchTerm !== '') {
                const messageRow = document.createElement('tr');
                messageRow.id = 'noResultsMessage';
                messageRow.innerHTML = '<td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);"><div style="font-size: 2rem; margin-bottom: 16px;">🔍</div><strong>No se encontraron resultados</strong><br>Intenta con otros términos de búsqueda</td>';
                tbody.appendChild(messageRow);
            }
        });

        // Animaciones de entrada
        window.addEventListener('load', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('fade-in');
                }, index * 50);
            });
        });
    </script>
</body>
</html>