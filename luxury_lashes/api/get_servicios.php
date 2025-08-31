<?php
// api/get_servicios.php - API para obtener servicios por área

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario esté logueado
requireLogin();

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener área ID
$areaId = intval($_POST['area_id'] ?? 0);

if ($areaId === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de área inválido']);
    exit;
}

try {
    $servicios = getServiciosPorArea($areaId);
    echo json_encode($servicios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>