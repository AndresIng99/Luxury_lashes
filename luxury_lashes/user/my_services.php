<?php
// user/my_services.php - Ver mis servicios con filtros avanzados (Colaborador)

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

// Obtener filtros de la URL
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo_filtro = $_GET['tipo'] ?? '';
$fecha_especifica = $_GET['fecha_especifica'] ?? '';

// Si hay fecha específica, usarla como rango
if ($fecha_especifica) {
    $fecha_inicio = $fecha_especifica;
    $fecha_fin = $fecha_especifica;
}

// Obtener servicios del colaborador con filtros
$servicios = getHistorialServiciosAvanzado($current_user['id'], $fecha_inicio ?: null, $fecha_fin ?: null, $tipo_filtro ?: null, null);

// Obtener estadísticas del colaborador (sin métodos de pago ni totales de dinero)
$estadisticas = getEstadisticasAvanzadas($current_user['id'], $fecha_inicio ?: null, $fecha_fin ?: null);

// Obtener fechas con servicios del colaborador
$fechas_recientes = getFechasConServicios($current_user['id'], 15);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Servicios - Luxury Lashes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            <!-- Estadísticas personales (sin montos) -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $estadisticas['totales']['total_registros']; ?></div>
                    <div class="stat-label">Mis Servicios Registrados</div>
                </div>
                <div class="stat-card colaborador">
                    <div class="stat-number">
                        <?php 
                        $servicios_count = 0;
                        foreach ($estadisticas['por_tipo'] as $tipo) {
                            if ($tipo['tipo'] === 'servicio') $servicios_count = $tipo['cantidad'];
                        }
                        echo $servicios_count;
                        ?>
                    </div>
                    <div class="stat-label">Servicios Realizados</div>
                </div>
                <div class="stat-card" style="border-left-color: var(--warning-color);">
                    <div class="stat-number" style="color: var(--warning-color);">
                        <?php 
                        $productos_count = 0;
                        foreach ($estadisticas['por_tipo'] as $tipo) {
                            if ($tipo['tipo'] === 'producto') $productos_count = $tipo['cantidad'];
                        }
                        echo $productos_count;
                        ?>
                    </div>
                    <div class="stat-label">Productos Vendidos</div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-number" style="color: var(--primary-color);">
                        <?php 
                        // Calcular días trabajados
                        $fechas_trabajadas = [];
                        foreach ($servicios as $servicio) {
                            $fecha = date('Y-m-d', strtotime($servicio['fecha_servicio']));
                            $fechas_trabajadas[$fecha] = true;
                        }
                        echo count($fechas_trabajadas);
                        ?>
                    </div>
                    <div class="stat-label">Días Trabajados</div>
                </div>
            </div>

            <!-- Filtros para colaborador -->
            <div class="card fade-in">
                <h3 class="card-title">Filtros de Búsqueda</h3>
                <form method="GET" id="filtrosForm">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
                        
                        <!-- Rango de fechas -->
                        <div class="form-group">
                            <label class="form-label">Fecha inicial</label>
                            <input type="date" name="fecha_inicio" class="form-input" value="<?php echo $fecha_inicio; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Fecha final</label>
                            <input type="date" name="fecha_fin" class="form-input" value="<?php echo $fecha_fin; ?>">
                        </div>
                        
                        <!-- Tipo -->
                        <div class="form-group">
                            <label class="form-label">Tipo de registro</label>
                            <select name="tipo" class="form-select">
                                <option value="">Servicios y Productos</option>
                                <option value="servicio" <?php echo ($tipo_filtro === 'servicio') ? 'selected' : ''; ?>>Solo Servicios</option>
                                <option value="producto" <?php echo ($tipo_filtro === 'producto') ? 'selected' : ''; ?>>Solo Productos</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                        <a href="my_services.php" class="btn btn-secondary">Limpiar</a>
                        <button type="button" onclick="setToday()" class="btn btn-secondary">Hoy</button>
                        <button type="button" onclick="setYesterday()" class="btn btn-secondary">Ayer</button>
                        <button type="button" onclick="setThisWeek()" class="btn btn-secondary">Esta Semana</button>
                        <button type="button" onclick="setThisMonth()" class="btn btn-secondary">Este Mes</button>
                    </div>
                </form>
                
                <!-- Fechas rápidas -->
                <?php if (!empty($fechas_recientes)): ?>
                <div style="margin-top: 20px;">
                    <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 14px;">Mis días de trabajo:</h4>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <?php foreach ($fechas_recientes as $fecha_info): ?>
                            <a href="?fecha_especifica=<?php echo $fecha_info['fecha']; ?>" 
                               class="btn btn-secondary" 
                               style="font-size: 12px; padding: 6px 12px; <?php echo ($fecha_especifica === $fecha_info['fecha']) ? 'background: var(--primary-color); color: white;' : ''; ?>">
                                <?php echo date('d/m', strtotime($fecha_info['fecha'])); ?> 
                                (<?php echo $fecha_info['total_servicios']; ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Gráficas para colaborador -->
            <?php if (!empty($estadisticas['servicios_populares']) || !empty($estadisticas['productos_populares']) || !empty($estadisticas['por_dia_semana'])): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; margin-bottom: 24px;">
                
                <!-- Mis servicios más realizados -->
                <?php if (!empty($estadisticas['servicios_populares'])): ?>
                <div class="card">
                    <h3 class="card-title">Mis Servicios Favoritos</h3>
                    <div style="height: 300px;">
                        <canvas id="misServiciosChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actividad por día de la semana -->
                <div class="card">
                    <h3 class="card-title">Mi Actividad Semanal</h3>
                    <div style="height: 300px;">
                        <canvas id="miActividadChart"></canvas>
                    </div>
                </div>
                
                <!-- Mis productos más vendidos -->
                <?php if (!empty($estadisticas['productos_populares'])): ?>
                <div class="card">
                    <h3 class="card-title">Productos que Más Vendo</h3>
                    <div style="height: 300px;">
                        <canvas id="misProductosChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 16px;">📊</div>
                <h3>Registra algunos servicios para ver tus gráficas</h3>
                <p>Una vez que tengas servicios registrados, aquí verás estadísticas de tu trabajo</p>
                <a href="register_service.php" class="btn btn-primary">Registrar Servicio</a>
            </div>
            <?php endif; ?>

            <!-- Historial de servicios -->
            <div class="card fade-in">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2 class="card-title mb-0">Mi Historial de Servicios</h2>
                        <?php if ($fecha_inicio || $fecha_fin): ?>
                            <p style="color: var(--text-muted); font-size: 14px; margin: 4px 0 0 0;">
                                <?php if ($fecha_inicio): ?>Desde: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?><?php endif; ?>
                                <?php if ($fecha_fin): ?> - Hasta: <?php echo date('d/m/Y', strtotime($fecha_fin)); ?><?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <a href="register_service.php" class="btn btn-primary">
                        Registrar Nuevo Servicio
                    </a>
                </div>

                <?php if (empty($servicios)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                        <div style="font-size: 4rem; margin-bottom: 24px;">💄</div>
                        <h3 style="color: var(--text-secondary); margin-bottom: 16px;">
                            <?php if ($fecha_inicio || $fecha_fin): ?>
                                No tienes servicios en estas fechas
                            <?php else: ?>
                                Aún no has registrado servicios
                            <?php endif; ?>
                        </h3>
                        <p style="margin-bottom: 24px; color: var(--text-muted);">
                            <?php if ($fecha_inicio || $fecha_fin): ?>
                                Intenta ajustar los filtros de fecha
                            <?php else: ?>
                                Comienza registrando tu primer servicio del día
                            <?php endif; ?>
                        </p>
                        <a href="register_service.php" class="btn btn-primary">
                            Registrar Mi Primer Servicio
                        </a>
                    </div>
                <?php else: ?>
                    
                    <!-- Búsqueda -->
                    <div style="margin-bottom: 24px;">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="form-input" 
                            placeholder="Buscar en mis servicios..."
                            style="max-width: 400px;"
                        >
                    </div>

                    <div class="table-container">
                        <table class="table" id="servicesTable">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Tipo</th>
                                    <th>Servicio/Producto</th>
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
                                                <?php echo date('H:i:s', strtotime($servicio['fecha_servicio'])); ?>
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
                                            <?php if (!empty($servicio['descripcion'])): ?>
                                                <div style="max-width: 200px; font-size: 13px; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 50)); ?>
                                                    <?php if (strlen($servicio['descripcion']) > 50): ?>...<?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic; font-size: 12px;">
                                                    Sin detalles adicionales
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen personal -->
                    <div style="margin-top: 24px; padding: 20px; background: var(--bg-secondary); border-radius: var(--radius-lg); font-size: 14px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div>
                                <strong style="color: var(--text-primary);">Registros mostrados:</strong>
                                <span style="color: var(--text-secondary);" id="registrosCount"><?php echo count($servicios); ?></span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Servicios realizados:</strong>
                                <span style="color: var(--success-color);">
                                    <?php 
                                    $servicios_realizados = 0;
                                    foreach ($servicios as $servicio) {
                                        if ($servicio['tipo'] === 'servicio') $servicios_realizados++;
                                    }
                                    echo $servicios_realizados;
                                    ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Productos vendidos:</strong>
                                <span style="color: var(--warning-color);">
                                    <?php 
                                    $productos_vendidos = 0;
                                    foreach ($servicios as $servicio) {
                                        if ($servicio['tipo'] === 'producto') $productos_vendidos++;
                                    }
                                    echo $productos_vendidos;
                                    ?>
                                </span>
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

            <!-- Motivación personal -->
            <div class="card fade-in">
                <h3 class="card-title">Tu Progreso</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    
                    <div class="info-card" style="background: rgb(5 150 105 / 0.05); border-color: rgb(5 150 105 / 0.1);">
                        <h4 style="color: var(--success-color);">
                            <span style="background: rgb(5 150 105 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">🎯</span>
                            Tu Desempeño
                        </h4>
                        <div style="font-size: 14px; color: var(--text-secondary);">
                            <p style="margin-bottom: 8px;">
                                <strong style="color: var(--text-primary);">Total de servicios:</strong> 
                                <?php echo $estadisticas['totales']['total_registros']; ?>
                            </p>
                            <p style="margin-bottom: 8px;">
                                <strong style="color: var(--text-primary);">Días activos:</strong> 
                                <?php echo count(array_unique(array_map(function($s) { return date('Y-m-d', strtotime($s['fecha_servicio'])); }, $servicios))); ?>
                            </p>
                            <p style="margin-bottom: 0;">
                                <strong style="color: var(--text-primary);">Promedio por día:</strong> 
                                <?php 
                                $dias_trabajados = count(array_unique(array_map(function($s) { return date('Y-m-d', strtotime($s['fecha_servicio'])); }, $servicios)));
                                echo $dias_trabajados > 0 ? round($estadisticas['totales']['total_registros'] / $dias_trabajados, 1) : 0;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="info-card" style="background: rgb(37 99 235 / 0.05); border-color: rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color);">
                            <span style="background: rgb(37 99 235 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">📈</span>
                            Consejos
                        </h4>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            <ul style="margin-left: 0; list-style: none; padding-left: 0;">
                                <li style="margin-bottom: 6px;">• Registra tus servicios inmediatamente</li>
                                <li style="margin-bottom: 6px;">• Añade detalles que te ayuden a recordar</li>
                                <li style="margin-bottom: 6px;">• Revisa tu progreso semanalmente</li>
                                <li style="margin-bottom: 0;">• Mantén un ritmo constante</li>
                            </ul>
                        </div>
                    </div>
                </div>
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
            
            // Actualizar contador
            document.getElementById('registrosCount').textContent = visibleRows;
            
            // Mensaje si no hay resultados
            const tbody = table.getElementsByTagName('tbody')[0];
            const existingMessage = document.getElementById('noResultsMessage');
            
            if (existingMessage) {
                existingMessage.remove();
            }
            
            if (visibleRows === 0 && searchTerm !== '') {
                const messageRow = document.createElement('tr');
                messageRow.id = 'noResultsMessage';
                messageRow.innerHTML = '<td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);"><div style="font-size: 2rem; margin-bottom: 16px;">🔍</div><strong>No se encontraron resultados</strong><br>Intenta con otros términos</td>';
                tbody.appendChild(messageRow);
            }
        });

        // Funciones para filtros de fechas
        function setToday() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="fecha_inicio"]').value = today;
            document.querySelector('input[name="fecha_fin"]').value = today;
        }
        
        function setYesterday() {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const dateStr = yesterday.toISOString().split('T')[0];
            document.querySelector('input[name="fecha_inicio"]').value = dateStr;
            document.querySelector('input[name="fecha_fin"]').value = dateStr;
        }
        
        function setThisWeek() {
            const today = new Date();
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
            const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6));
            document.querySelector('input[name="fecha_inicio"]').value = firstDay.toISOString().split('T')[0];
            document.querySelector('input[name="fecha_fin"]').value = lastDay.toISOString().split('T')[0];
        }
        
        function setThisMonth() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            document.querySelector('input[name="fecha_inicio"]').value = firstDay.toISOString().split('T')[0];
            document.querySelector('input[name="fecha_fin"]').value = lastDay.toISOString().split('T')[0];
        }

        // Gráficas con Chart.js (solo para el colaborador, sin datos financieros)
        document.addEventListener('DOMContentLoaded', function() {
            
            // Mis servicios más realizados
            <?php if (!empty($estadisticas['servicios_populares'])): ?>
            var misServiciosData = <?php echo json_encode($estadisticas['servicios_populares']); ?>;
            if (misServiciosData.length > 0) {
                var ctx1 = document.getElementById('misServiciosChart');
                if (ctx1) {
                    new Chart(ctx1, {
                        type: 'doughnut',
                        data: {
                            labels: misServiciosData.map(function(item) { return item.servicio; }),
                            datasets: [{
                                data: misServiciosData.map(function(item) { return item.cantidad; }),
                                backgroundColor: [
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(16, 185, 129, 0.8)',
                                    'rgba(249, 115, 22, 0.8)',
                                    'rgba(168, 85, 247, 0.8)',
                                    'rgba(236, 72, 153, 0.8)',
                                    'rgba(34, 197, 94, 0.8)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            }
            <?php endif; ?>

            // Mi actividad por día de la semana
            var miActividadData = <?php echo json_encode($estadisticas['por_dia_semana']); ?>;
            var ctx2 = document.getElementById('miActividadChart');
            if (ctx2) {
                var diasSemana = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                var diasEspanol = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                
                var miActividadPorDia = [0, 0, 0, 0, 0, 0, 0];
                
                if (miActividadData && miActividadData.length > 0) {
                    miActividadData.forEach(function(item) {
                        var index = diasSemana.indexOf(item.dia_semana);
                        if (index !== -1) {
                            miActividadPorDia[index] = parseInt(item.cantidad);
                        }
                    });
                }
                
                new Chart(ctx2, {
                    type: 'bar',
                    data: {
                        labels: diasEspanol,
                        datasets: [{
                            label: 'Mis Servicios',
                            data: miActividadPorDia,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Mis productos más vendidos
            <?php if (!empty($estadisticas['productos_populares'])): ?>
            var misProductosData = <?php echo json_encode($estadisticas['productos_populares']); ?>;
            if (misProductosData.length > 0) {
                var ctx3 = document.getElementById('misProductosChart');
                if (ctx3) {
                    var productosLabels = misProductosData.map(function(item) {
                        return item.producto_personalizado.length > 20 ? 
                            item.producto_personalizado.substring(0, 20) + '...' : 
                            item.producto_personalizado;
                    });
                    
                    new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: productosLabels,
                            datasets: [{
                                label: 'Cantidad Vendida',
                                data: misProductosData.map(function(item) { return item.cantidad; }),
                                backgroundColor: 'rgba(249, 115, 22, 0.8)',
                                borderColor: 'rgba(249, 115, 22, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            }
            <?php endif; ?>
            
            // Debug para colaboradores
            console.log('Gráficas de colaborador inicializadas');
            console.log('Mis servicios:', <?php echo json_encode($estadisticas['servicios_populares']); ?>);
            console.log('Mi actividad:', <?php echo json_encode($estadisticas['por_dia_semana']); ?>);
            console.log('Mis productos:', <?php echo json_encode($estadisticas['productos_populares']); ?>);
        });

        // Animaciones
        window.addEventListener('load', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('fade-in');
                }, index * 30);
            });
        });

        // Efectos interactivos
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>