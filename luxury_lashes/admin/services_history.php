<?php
// admin/services_history.php - Historial completo de servicios para administradores (CORREGIDO)

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Obtener filtros de la URL
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$colaborador_filtro = isset($_GET['colaborador']) ? $_GET['colaborador'] : '';
$tipo_filtro = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$metodo_pago_filtro = isset($_GET['metodo_pago']) ? $_GET['metodo_pago'] : '';
$fecha_especifica = isset($_GET['fecha_especifica']) ? $_GET['fecha_especifica'] : '';

// Si hay fecha específica, usarla como rango
if ($fecha_especifica) {
    $fecha_inicio = $fecha_especifica;
    $fecha_fin = $fecha_especifica;
}

// Obtener datos
$servicios = getHistorialServiciosAvanzado(
    $colaborador_filtro ? $colaborador_filtro : null, 
    $fecha_inicio ? $fecha_inicio : null, 
    $fecha_fin ? $fecha_fin : null, 
    $tipo_filtro ? $tipo_filtro : null, 
    $metodo_pago_filtro ? $metodo_pago_filtro : null
);

$estadisticas = getEstadisticasAvanzadas(
    $colaborador_filtro ? $colaborador_filtro : null, 
    $fecha_inicio ? $fecha_inicio : null, 
    $fecha_fin ? $fecha_fin : null
);

$colaboradores = getColaboradoresActivos();
$fechas_recientes = getFechasConServicios(null, 15);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Servicios - Luxury Lashes</title>
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
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="register_service.php" class="nav-link">Registrar Servicio</a>
                </li>
                <li class="nav-item">
                    <a href="services_history.php" class="nav-link active">Historial de Servicios</a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link">Gestionar Usuarios</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            
            <!-- Estadísticas Generales -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $estadisticas['totales']['total_registros']; ?></div>
                    <div class="stat-label">Total Registros</div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-number" style="color: var(--success-color);">
                        $<?php echo number_format($estadisticas['totales']['total_ingresos'], 0, ',', '.'); ?>
                    </div>
                    <div class="stat-label">Ingresos Totales</div>
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
                    <div class="stat-label">Servicios</div>
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
                    <div class="stat-label">Productos</div>
                </div>
            </div>

            <!-- Filtros Avanzados -->
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
                        
                        <!-- Colaborador -->
                        <div class="form-group">
                            <label class="form-label">Colaborador</label>
                            <select name="colaborador" class="form-select">
                                <option value="">Todos los colaboradores</option>
                                <?php foreach ($colaboradores as $colab): ?>
                                    <option value="<?php echo $colab['id']; ?>" <?php echo ($colaborador_filtro == $colab['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($colab['nombres_completos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Tipo -->
                        <div class="form-group">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="">Servicios y Productos</option>
                                <option value="servicio" <?php echo ($tipo_filtro === 'servicio') ? 'selected' : ''; ?>>Solo Servicios</option>
                                <option value="producto" <?php echo ($tipo_filtro === 'producto') ? 'selected' : ''; ?>>Solo Productos</option>
                            </select>
                        </div>
                        
                        <!-- Método de pago -->
                        <div class="form-group">
                            <label class="form-label">Método de Pago</label>
                            <select name="metodo_pago" class="form-select">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo" <?php echo ($metodo_pago_filtro === 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                                <option value="nequi" <?php echo ($metodo_pago_filtro === 'nequi') ? 'selected' : ''; ?>>Nequí</option>
                                <option value="daviplata" <?php echo ($metodo_pago_filtro === 'daviplata') ? 'selected' : ''; ?>>Daviplata</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                        <a href="services_history.php" class="btn btn-secondary">Limpiar Filtros</a>
                        <button type="button" onclick="setToday()" class="btn btn-secondary">Hoy</button>
                        <button type="button" onclick="setYesterday()" class="btn btn-secondary">Ayer</button>
                        <button type="button" onclick="setThisWeek()" class="btn btn-secondary">Esta Semana</button>
                        <button type="button" onclick="setThisMonth()" class="btn btn-secondary">Este Mes</button>
                    </div>
                </form>
                
                <!-- Fechas rápidas -->
                <?php if (!empty($fechas_recientes)): ?>
                <div style="margin-top: 20px;">
                    <h4 style="color: var(--text-primary); margin-bottom: 12px; font-size: 14px;">Fechas con servicios:</h4>
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

            <!-- Gráficas -->
            <?php if (!empty($estadisticas['servicios_populares']) || !empty($estadisticas['por_metodo_pago']) || !empty($estadisticas['por_colaborador']) || !empty($estadisticas['por_dia_semana'])): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 24px;">
                
                <!-- Servicios más populares -->
                <?php if (!empty($estadisticas['servicios_populares'])): ?>
                <div class="card">
                    <h3 class="card-title">Servicios Más Solicitados</h3>
                    <div style="height: 300px;">
                        <canvas id="serviciosPopularesChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Métodos de pago -->
                <?php if (isset($estadisticas['por_metodo_pago']) && !empty($estadisticas['por_metodo_pago'])): ?>
                <div class="card">
                    <h3 class="card-title">Métodos de Pago</h3>
                    <div style="height: 300px;">
                        <canvas id="metodosPagoChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Por colaborador -->
                <?php if (isset($estadisticas['por_colaborador']) && !empty($estadisticas['por_colaborador'])): ?>
                <div class="card">
                    <h3 class="card-title">Por Colaborador</h3>
                    <div style="height: 300px;">
                        <canvas id="colaboradorChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actividad por día de la semana -->
                <div class="card">
                    <h3 class="card-title">Actividad por Día de la Semana</h3>
                    <div style="height: 300px;">
                        <canvas id="diaSemanaChart"></canvas>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 16px;">📊</div>
                <h3>No hay datos suficientes para mostrar gráficas</h3>
                <p>Registra algunos servicios para ver las estadísticas visuales</p>
            </div>
            <?php endif; ?>

            <!-- Resumen por Colaborador -->
            <div class="card fade-in">
                <h3 class="card-title">Resumen por Colaborador</h3>
                <?php
                // Obtener estadísticas de TODOS los colaboradores activos, incluso los que no tienen servicios en el filtro
                $todos_colaboradores = getColaboradoresActivos();
                $estadisticas_colaboradores = array();
                
                // Crear array con todos los colaboradores inicializados en 0
                foreach ($todos_colaboradores as $colaborador) {
                    $estadisticas_colaboradores[$colaborador['id']] = array(
                        'nombre' => $colaborador['nombres_completos'],
                        'cedula' => $colaborador['cedula'],
                        'total_servicios' => 0,
                        'servicios_realizados' => 0,
                        'productos_vendidos' => 0,
                        'total_ingresos' => 0,
                        'por_metodo_pago' => array('efectivo' => 0, 'nequi' => 0, 'daviplata' => 0)
                    );
                }
                
                // Llenar con datos reales de los servicios filtrados
                foreach ($servicios as $servicio) {
                    $colaborador_id = $servicio['colaborador_id'];
                    if (isset($estadisticas_colaboradores[$colaborador_id])) {
                        $estadisticas_colaboradores[$colaborador_id]['total_servicios']++;
                        $estadisticas_colaboradores[$colaborador_id]['total_ingresos'] += $servicio['monto'];
                        $estadisticas_colaboradores[$colaborador_id]['por_metodo_pago'][$servicio['metodo_pago']]++;
                        
                        if ($servicio['tipo'] === 'servicio') {
                            $estadisticas_colaboradores[$colaborador_id]['servicios_realizados']++;
                        } else {
                            $estadisticas_colaboradores[$colaborador_id]['productos_vendidos']++;
                        }
                    }
                }
                
                // Ordenar por total de ingresos (mayor a menor)
                uasort($estadisticas_colaboradores, function($a, $b) {
                    return $b['total_ingresos'] - $a['total_ingresos'];
                });
                ?>
                
                <?php if ($fecha_inicio || $fecha_fin || $colaborador_filtro || $tipo_filtro || $metodo_pago_filtro): ?>
                    <div style="background: rgb(37 99 235 / 0.05); padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color); margin-bottom: 12px; font-size: 16px;">📊 Filtros Aplicados:</h4>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 14px;">
                            <?php if ($fecha_inicio): ?>
                                <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <strong>Desde:</strong> <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($fecha_fin): ?>
                                <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <strong>Hasta:</strong> <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($colaborador_filtro): ?>
                                <?php 
                                $nombre_colaborador_filtro = '';
                                foreach ($colaboradores as $colab) {
                                    if ($colab['id'] == $colaborador_filtro) {
                                        $nombre_colaborador_filtro = $colab['nombres_completos'];
                                        break;
                                    }
                                }
                                ?>
                                <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <strong>Colaborador:</strong> <?php echo htmlspecialchars($nombre_colaborador_filtro); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($tipo_filtro): ?>
                                <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <strong>Tipo:</strong> <?php echo ucfirst($tipo_filtro); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($metodo_pago_filtro): ?>
                                <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    <strong>Método:</strong> <?php echo ucfirst($metodo_pago_filtro); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Total Servicios</th>
                                <th>Servicios</th>
                                <th>Productos</th>
                                <th>Total Ingresos</th>
                                <th>Efectivo</th>
                                <th>Nequí</th>
                                <th>Daviplata</th>
                                <th>% del Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $gran_total_ingresos = $estadisticas['totales']['total_ingresos'];
                            foreach ($estadisticas_colaboradores as $colaborador_id => $stats): 
                            ?>
                                <tr style="<?php echo $stats['total_servicios'] == 0 ? 'opacity: 0.6;' : ''; ?>">
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-primary);">
                                            <?php echo htmlspecialchars($stats['nombre']); ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            C.C. <?php echo htmlspecialchars($stats['cedula']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge <?php echo $stats['total_servicios'] > 0 ? 'role-colaborador' : ''; ?>" 
                                              style="<?php echo $stats['total_servicios'] == 0 ? 'background: rgb(100 116 139 / 0.1); color: var(--text-muted);' : ''; ?>">
                                            <?php echo $stats['total_servicios']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500; color: var(--primary-color);">
                                            <?php echo $stats['servicios_realizados']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500; color: var(--warning-color);">
                                            <?php echo $stats['productos_vendidos']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: <?php echo $stats['total_ingresos'] > 0 ? 'var(--success-color)' : 'var(--text-muted)'; ?>; font-size: 16px;">
                                            $<?php echo number_format($stats['total_ingresos'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: var(--text-secondary);">
                                            <?php echo $stats['por_metodo_pago']['efectivo']; ?>
                                            <?php if ($stats['por_metodo_pago']['efectivo'] > 0): ?>
                                                <span style="color: var(--text-muted);">💵</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: var(--text-secondary);">
                                            <?php echo $stats['por_metodo_pago']['nequi']; ?>
                                            <?php if ($stats['por_metodo_pago']['nequi'] > 0): ?>
                                                <span style="color: var(--text-muted);">📱</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 13px; color: var(--text-secondary);">
                                            <?php echo $stats['por_metodo_pago']['daviplata']; ?>
                                            <?php if ($stats['por_metodo_pago']['daviplata'] > 0): ?>
                                                <span style="color: var(--text-muted);">💳</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $porcentaje = $gran_total_ingresos > 0 ? ($stats['total_ingresos'] / $gran_total_ingresos) * 100 : 0;
                                        ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-weight: 500; color: var(--text-primary); min-width: 40px;">
                                                <?php echo number_format($porcentaje, 1); ?>%
                                            </span>
                                            <?php if ($porcentaje > 0): ?>
                                            <div style="background: #e2e8f0; height: 4px; border-radius: 2px; flex: 1; max-width: 60px; overflow: hidden;">
                                                <div style="background: var(--primary-color); height: 100%; width: <?php echo min($porcentaje, 100); ?>%; border-radius: 2px;"></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background: var(--bg-tertiary); font-weight: 600;">
                            <tr>
                                <td><strong>TOTALES</strong></td>
                                <td><strong><?php echo $estadisticas['totales']['total_registros']; ?></strong></td>
                                <td><strong style="color: var(--primary-color);">
                                    <?php 
                                    $total_servicios_general = 0;
                                    foreach ($estadisticas['por_tipo'] as $tipo) {
                                        if ($tipo['tipo'] === 'servicio') $total_servicios_general = $tipo['cantidad'];
                                    }
                                    echo $total_servicios_general;
                                    ?>
                                </strong></td>
                                <td><strong style="color: var(--warning-color);">
                                    <?php 
                                    $total_productos_general = 0;
                                    foreach ($estadisticas['por_tipo'] as $tipo) {
                                        if ($tipo['tipo'] === 'producto') $total_productos_general = $tipo['cantidad'];
                                    }
                                    echo $total_productos_general;
                                    ?>
                                </strong></td>
                                <td><strong style="color: var(--success-color); font-size: 16px;">
                                    $<?php echo number_format($estadisticas['totales']['total_ingresos'], 0, ',', '.'); ?>
                                </strong></td>
                                <td><strong>
                                    <?php 
                                    $total_efectivo = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'efectivo') {
                                                $total_efectivo = $metodo['cantidad'];
                                            }
                                        }
                                    }
                                    echo $total_efectivo;
                                    ?>
                                </strong></td>
                                <td><strong>
                                    <?php 
                                    $total_nequi = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'nequi') {
                                                $total_nequi = $metodo['cantidad'];
                                            }
                                        }
                                    }
                                    echo $total_nequi;
                                    ?>
                                </strong></td>
                                <td><strong>
                                    <?php 
                                    $total_daviplata = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'daviplata') {
                                                $total_daviplata = $metodo['cantidad'];
                                            }
                                        }
                                    }
                                    echo $total_daviplata;
                                    ?>
                                </strong></td>
                                <td><strong>100.0%</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (count($servicios) == 0): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 3rem; margin-bottom: 16px;">📊</div>
                        <h4>No hay datos para mostrar</h4>
                        <p>Ajusta los filtros o registra algunos servicios para ver las estadísticas por colaborador</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detalle de servicios -->
            <div class="card fade-in">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2 class="card-title mb-0">Detalle de Servicios</h2>
                        <?php if ($fecha_inicio || $fecha_fin || $colaborador_filtro): ?>
                            <p style="color: var(--text-muted); font-size: 14px; margin: 4px 0 0 0;">
                                Filtros aplicados
                                <?php if ($fecha_inicio): ?>- Desde: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?><?php endif; ?>
                                <?php if ($fecha_fin): ?>- Hasta: <?php echo date('d/m/Y', strtotime($fecha_fin)); ?><?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="exportarExcel()" class="btn btn-secondary" style="font-size: 13px;">
                            📊 Exportar Excel
                        </button>
                        <a href="register_service.php" class="btn btn-primary">Nuevo Servicio</a>
                    </div>
                </div>

                <?php if (empty($servicios)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                        <div style="font-size: 4rem; margin-bottom: 24px;">📊</div>
                        <h3 style="color: var(--text-secondary); margin-bottom: 16px;">No se encontraron servicios</h3>
                        <p style="margin-bottom: 24px; color: var(--text-muted);">
                            <?php if ($fecha_inicio || $fecha_fin || $colaborador_filtro): ?>
                                Intenta ajustar los filtros de búsqueda
                            <?php else: ?>
                                Aún no hay servicios registrados
                            <?php endif; ?>
                        </p>
                        <a href="register_service.php" class="btn btn-primary">
                            Registrar Primer Servicio
                        </a>
                    </div>
                <?php else: ?>
                    
                    <!-- Búsqueda en tiempo real -->
                    <div style="margin-bottom: 20px;">
                        <input 
                            type="text" 
                            id="busquedaInstantanea" 
                            class="form-input" 
                            placeholder="Buscar en los resultados..."
                            style="max-width: 400px;"
                        >
                    </div>

                    <div class="table-container">
                        <table class="table" id="serviciosTable">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Colaborador</th>
                                    <th>Tipo</th>
                                    <th>Servicio/Producto</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Registrado por</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?php echo date('d/m/Y', strtotime($servicio['fecha_servicio'])); ?>
                                            </div>
                                            <div style="font-size: 12px; color: var(--text-muted);">
                                                <?php echo date('H:i:s', strtotime($servicio['fecha_servicio'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($servicio['colaborador_nombre']); ?>
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
                                                    <?php echo htmlspecialchars($servicio['servicio_nombre'] ? $servicio['servicio_nombre'] : 'Servicio no especificado'); ?>
                                                </div>
                                                <div style="font-size: 12px; color: var(--text-muted);">
                                                    <?php echo htmlspecialchars($servicio['area_nombre'] ? $servicio['area_nombre'] : ''); ?>
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
                                                $iconos = array(
                                                    'efectivo' => '💵',
                                                    'nequi' => '📱',
                                                    'daviplata' => '💳'
                                                );
                                                echo isset($iconos[$servicio['metodo_pago']]) ? $iconos[$servicio['metodo_pago']] : '💰';
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
                                            <div style="font-size: 13px; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($servicio['registrado_por_nombre']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($servicio['descripcion'])): ?>
                                                <div style="max-width: 150px; font-size: 13px; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars(substr($servicio['descripcion'], 0, 40)); ?>
                                                    <?php if (strlen($servicio['descripcion']) > 40): ?>...<?php endif; ?>
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

                    <!-- Resumen de resultados -->
                    <div style="margin-top: 24px; padding: 20px; background: var(--bg-secondary); border-radius: var(--radius-lg); font-size: 14px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div>
                                <strong style="color: var(--text-primary);">Registros mostrados:</strong>
                                <span style="color: var(--text-secondary);" id="registrosCount"><?php echo count($servicios); ?></span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Total de ingresos:</strong>
                                <span style="color: var(--success-color); font-weight: 600;">
                                    $<?php echo number_format($estadisticas['totales']['total_ingresos'], 0, ',', '.'); ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Efectivo:</strong>
                                <span style="color: var(--text-secondary);">
                                    <?php 
                                    $efectivo = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'efectivo') {
                                                $efectivo = $metodo['total_monto'];
                                            }
                                        }
                                    }
                                    echo '$' . number_format($efectivo, 0, ',', '.');
                                    ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Nequí:</strong>
                                <span style="color: var(--text-secondary);">
                                    <?php 
                                    $nequi = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'nequi') {
                                                $nequi = $metodo['total_monto'];
                                            }
                                        }
                                    }
                                    echo '$' . number_format($nequi, 0, ',', '.');
                                    ?>
                                </span>
                            </div>
                            <div>
                                <strong style="color: var(--text-primary);">Daviplata:</strong>
                                <span style="color: var(--text-secondary);">
                                    <?php 
                                    $daviplata = 0;
                                    if (isset($estadisticas['por_metodo_pago'])) {
                                        foreach ($estadisticas['por_metodo_pago'] as $metodo) {
                                            if ($metodo['metodo_pago'] === 'daviplata') {
                                                $daviplata = $metodo['total_monto'];
                                            }
                                        }
                                    }
                                    echo '$' . number_format($daviplata, 0, ',', '.');
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
        // Función de búsqueda instantánea
        document.getElementById('busquedaInstantanea').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('serviciosTable');
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
            const existingMessage = document.getElementById('noSearchResults');
            
            if (existingMessage) {
                existingMessage.remove();
            }
            
            if (visibleRows === 0 && searchTerm !== '') {
                const messageRow = document.createElement('tr');
                messageRow.id = 'noSearchResults';
                messageRow.innerHTML = '<td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);"><div style="font-size: 2rem; margin-bottom: 16px;">🔍</div><strong>No se encontraron resultados</strong><br>Intenta con otros términos de búsqueda</td>';
                tbody.appendChild(messageRow);
            }
        });

        // Funciones para filtros rápidos de fechas
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

        // Función para exportar a Excel
        function exportarExcel() {
            // Crear URL con parámetros actuales
            const params = new URLSearchParams(window.location.search);
            params.append('export', 'excel');
            
            // Crear enlace temporal para descarga
            const link = document.createElement('a');
            link.href = 'export_excel.php?' + params.toString();
            link.download = 'historial_servicios.xlsx';
            link.click();
        }

        // Gráficas con Chart.js
        document.addEventListener('DOMContentLoaded', function() {
            // Servicios más populares
            <?php if (!empty($estadisticas['servicios_populares'])): ?>
            const serviciosPopularesData = <?php echo json_encode($estadisticas['servicios_populares']); ?>;
            if (serviciosPopularesData.length > 0) {
                const ctx1 = document.getElementById('serviciosPopularesChart');
                if (ctx1) {
                    new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: serviciosPopularesData.map(function(item) { return item.servicio; }),
                            datasets: [{
                                label: 'Cantidad',
                                data: serviciosPopularesData.map(function(item) { return item.cantidad; }),
                                backgroundColor: 'rgba(37, 99, 235, 0.8)',
                                borderColor: 'rgba(37, 99, 235, 1)',
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
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
            <?php endif; ?>

            // Métodos de pago
            <?php if (isset($estadisticas['por_metodo_pago']) && !empty($estadisticas['por_metodo_pago'])): ?>
            const metodosPagoData = <?php echo json_encode($estadisticas['por_metodo_pago']); ?>;
            if (metodosPagoData.length > 0) {
                const ctx2 = document.getElementById('metodosPagoChart');
                if (ctx2) {
                    const metodosLabels = metodosPagoData.map(function(item) {
                        const iconos = {'efectivo': '💵 Efectivo', 'nequi': '📱 Nequí', 'daviplata': '💳 Daviplata'};
                        return iconos[item.metodo_pago] || item.metodo_pago;
                    });
                    
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: metodosLabels,
                            datasets: [{
                                data: metodosPagoData.map(function(item) { return item.cantidad; }),
                                backgroundColor: [
                                    'rgba(34, 197, 94, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(249, 115, 22, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(34, 197, 94, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(249, 115, 22, 1)'
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

            // Por colaborador
            <?php if (isset($estadisticas['por_colaborador']) && !empty($estadisticas['por_colaborador'])): ?>
            const colaboradorData = <?php echo json_encode($estadisticas['por_colaborador']); ?>;
            if (colaboradorData.length > 0) {
                const ctx3 = document.getElementById('colaboradorChart');
                if (ctx3) {
                    new Chart(ctx3, {
                        type: 'bar',
                        data: {
                            labels: colaboradorData.map(function(item) { return item.nombres_completos; }),
                            datasets: [{
                                label: 'Servicios',
                                data: colaboradorData.map(function(item) { return item.total_servicios; }),
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
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
            <?php endif; ?>

            // Actividad por día de la semana
            const diaSemanaData = <?php echo json_encode($estadisticas['por_dia_semana']); ?>;
            const ctx4 = document.getElementById('diaSemanaChart');
            if (ctx4) {
                const diasSemana = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const diasEspanol = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                
                // Crear array con todos los días inicializados en 0
                const actividadPorDia = [0, 0, 0, 0, 0, 0, 0];
                
                if (diaSemanaData && diaSemanaData.length > 0) {
                    diaSemanaData.forEach(function(item) {
                        const index = diasSemana.indexOf(item.dia_semana);
                        if (index !== -1) {
                            actividadPorDia[index] = parseInt(item.cantidad);
                        }
                    });
                }
                
                new Chart(ctx4, {
                    type: 'line',
                    data: {
                        labels: diasEspanol,
                        datasets: [{
                            label: 'Servicios',
                            data: actividadPorDia,
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            borderColor: 'rgba(168, 85, 247, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                            pointBorderColor: 'rgba(168, 85, 247, 1)',
                            pointRadius: 4
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
            
            // Debug: mostrar en consola si las gráficas se están cargando
            console.log('Gráficas inicializadas');
            console.log('Servicios populares:', <?php echo json_encode($estadisticas['servicios_populares']); ?>);
            console.log('Métodos de pago:', <?php echo isset($estadisticas['por_metodo_pago']) ? json_encode($estadisticas['por_metodo_pago']) : 'null'; ?>);
            console.log('Por día semana:', <?php echo json_encode($estadisticas['por_dia_semana']); ?>);
        });

        // Animaciones de entrada
        window.addEventListener('load', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(function(row, index) {
                setTimeout(function() {
                    row.classList.add('fade-in');
                }, index * 20);
            });
        });
    </script>
</body>
</html>