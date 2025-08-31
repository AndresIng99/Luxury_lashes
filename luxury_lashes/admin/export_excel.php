<?php
// admin/export_excel.php - Exportar historial de servicios a Excel

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Verificar que se solicite exportación
if (!isset($_GET['export']) || $_GET['export'] !== 'excel') {
    header('Location: services_history.php');
    exit;
}

// Obtener filtros
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$colaborador_filtro = $_GET['colaborador'] ?? '';
$tipo_filtro = $_GET['tipo'] ?? '';
$metodo_pago_filtro = $_GET['metodo_pago'] ?? '';

// Obtener datos
$servicios = getHistorialServiciosAvanzado(
    $colaborador_filtro ?: null, 
    $fecha_inicio ?: null, 
    $fecha_fin ?: null, 
    $tipo_filtro ?: null, 
    $metodo_pago_filtro ?: null
);

$estadisticas = getEstadisticasAvanzadas(
    $colaborador_filtro ?: null, 
    $fecha_inicio ?: null, 
    $fecha_fin ?: null
);

// Configurar headers para descarga de Excel
$filename = 'historial_servicios_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Crear el archivo CSV
$output = fopen('php://output', 'w');

// BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir información del reporte
fputcsv($output, ['REPORTE DE HISTORIAL DE SERVICIOS - LUXURY LASHES'], ';');
fputcsv($output, ['Generado el: ' . date('d/m/Y H:i:s')], ';');
fputcsv($output, [''], ';'); // Línea vacía

// Escribir filtros aplicados
fputcsv($output, ['FILTROS APLICADOS:'], ';');
if ($fecha_inicio) fputcsv($output, ['Fecha inicial: ' . date('d/m/Y', strtotime($fecha_inicio))], ';');
if ($fecha_fin) fputcsv($output, ['Fecha final: ' . date('d/m/Y', strtotime($fecha_fin))], ';');
if ($colaborador_filtro) {
    $colaborador_nombre = '';
    $colaboradores = getColaboradoresActivos();
    foreach ($colaboradores as $colab) {
        if ($colab['id'] == $colaborador_filtro) {
            $colaborador_nombre = $colab['nombres_completos'];
            break;
        }
    }
    fputcsv($output, ['Colaborador: ' . $colaborador_nombre], ';');
}
if ($tipo_filtro) fputcsv($output, ['Tipo: ' . ucfirst($tipo_filtro)], ';');
if ($metodo_pago_filtro) fputcsv($output, ['Método de pago: ' . ucfirst($metodo_pago_filtro)], ';');
fputcsv($output, [''], ';'); // Línea vacía

// Escribir estadísticas generales
fputcsv($output, ['RESUMEN ESTADÍSTICO:'], ';');
fputcsv($output, ['Total de registros: ' . $estadisticas['totales']['total_registros']], ';');
fputcsv($output, ['Total de ingresos: $' . number_format($estadisticas['totales']['total_ingresos'], 0, ',', '.')], ';');

// Estadísticas por tipo
foreach ($estadisticas['por_tipo'] as $tipo) {
    fputcsv($output, [ucfirst($tipo['tipo']) . 's: ' . $tipo['cantidad'] . ' (Total: $' . number_format($tipo['total_monto'], 0, ',', '.') . ')'], ';');
}

// Estadísticas por método de pago
if (!empty($estadisticas['por_metodo_pago'])) {
    fputcsv($output, [''], ';'); // Línea vacía
    fputcsv($output, ['POR MÉTODO DE PAGO:'], ';');
    foreach ($estadisticas['por_metodo_pago'] as $metodo) {
        fputcsv($output, [ucfirst($metodo['metodo_pago']) . ': ' . $metodo['cantidad'] . ' registros ($' . number_format($metodo['total_monto'], 0, ',', '.') . ')'], ';');
    }
}

fputcsv($output, [''], ';'); // Línea vacía
fputcsv($output, [''], ';'); // Línea vacía

// Escribir encabezados de la tabla de datos
fputcsv($output, [
    'Fecha',
    'Hora',
    'Colaborador',
    'Tipo',
    'Área',
    'Servicio/Producto',
    'Método de Pago',
    'Monto',
    'Registrado por',
    'Descripción'
], ';');

// Escribir los datos
foreach ($servicios as $servicio) {
    $row = [
        date('d/m/Y', strtotime($servicio['fecha_servicio'])),
        date('H:i:s', strtotime($servicio['fecha_servicio'])),
        $servicio['colaborador_nombre'],
        ucfirst($servicio['tipo']),
        $servicio['area_nombre'] ?? '',
        $servicio['tipo'] === 'servicio' ? 
            ($servicio['servicio_nombre'] ?? 'No especificado') : 
            ($servicio['producto_personalizado'] ?? 'No especificado'),
        ucfirst($servicio['metodo_pago']),
        '$' . number_format($servicio['monto'], 0, ',', '.'),
        $servicio['registrado_por_nombre'],
        $servicio['descripcion'] ?? 'Sin descripción'
    ];
    
    fputcsv($output, $row, ';');
}

// Estadísticas adicionales al final
fputcsv($output, [''], ';'); // Línea vacía
fputcsv($output, [''], ';'); // Línea vacía
fputcsv($output, ['SERVICIOS MÁS SOLICITADOS:'], ';');
foreach ($estadisticas['servicios_populares'] as $servicio_popular) {
    fputcsv($output, [
        $servicio_popular['servicio'] . ' (' . $servicio_popular['area'] . '): ' . $servicio_popular['cantidad'] . ' veces'
    ], ';');
}

if (!empty($estadisticas['productos_populares'])) {
    fputcsv($output, [''], ';'); // Línea vacía
    fputcsv($output, ['PRODUCTOS MÁS VENDIDOS:'], ';');
    foreach ($estadisticas['productos_populares'] as $producto_popular) {
        fputcsv($output, [
            $producto_popular['producto_personalizado'] . ': ' . $producto_popular['cantidad'] . ' veces'
        ], ';');
    }
}

if (!empty($estadisticas['por_colaborador'])) {
    fputcsv($output, [''], ';'); // Línea vacía
    fputcsv($output, ['ESTADÍSTICAS POR COLABORADOR:'], ';');
    foreach ($estadisticas['por_colaborador'] as $colab_stat) {
        fputcsv($output, [
            $colab_stat['nombres_completos'] . ': ' . 
            $colab_stat['total_servicios'] . ' servicios ($' . 
            number_format($colab_stat['total_ingresos'], 0, ',', '.') . ')'
        ], ';');
    }
}

// Actividad por día de la semana
if (!empty($estadisticas['por_dia_semana'])) {
    fputcsv($output, [''], ';'); // Línea vacía
    fputcsv($output, ['ACTIVIDAD POR DÍA DE LA SEMANA:'], ';');
    $dias_espanol = [
        'Sunday' => 'Domingo',
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado'
    ];
    
    foreach ($estadisticas['por_dia_semana'] as $dia_stat) {
        $dia_nombre = $dias_espanol[$dia_stat['dia_semana']] ?? $dia_stat['dia_semana'];
        fputcsv($output, [$dia_nombre . ': ' . $dia_stat['cantidad'] . ' servicios'], ';');
    }
}

// Pie del reporte
fputcsv($output, [''], ';'); // Línea vacía
fputcsv($output, [''], ';'); // Línea vacía
fputcsv($output, ['Reporte generado por: Luxury Lashes Management System'], ';');
fputcsv($output, ['Fecha de generación: ' . date('d/m/Y H:i:s')], ';');
fputcsv($output, ['Total de registros exportados: ' . count($servicios)], ';');

fclose($output);
exit;
?>