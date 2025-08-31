<?php
// admin/toggle_user_status.php - Cambiar estado de usuario (habilitar/deshabilitar)

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Verificar que sea una petición POST con datos válidos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    $_SESSION['error_message'] = 'Solicitud inválida';
    header('Location: manage_users.php');
    exit;
}

$userId = intval($_POST['user_id']);

// Verificar que no sea el usuario actual (no puede deshabilitarse a sí mismo)
$currentUser = getCurrentUser();
if ($userId == $currentUser['id']) {
    $_SESSION['error_message'] = 'No puedes deshabilitar tu propia cuenta';
    header('Location: manage_users.php');
    exit;
}

// Cambiar estado del usuario
$result = toggleUserStatus($userId);

if ($result['success']) {
    $_SESSION['success_message'] = $result['message'];
} else {
    $_SESSION['error_message'] = $result['message'];
}

// Redirigir de vuelta a la gestión de usuarios
header('Location: manage_users.php');
exit;
?>