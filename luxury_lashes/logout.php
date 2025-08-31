<?php
// logout.php - Cerrar sesión

session_start();
require_once 'includes/session.php';

// Cerrar sesión del usuario
logoutUser();

// Mensaje de confirmación
$_SESSION['success_message'] = 'Sesión cerrada exitosamente';

// Redirigir al login
header('Location: index.php');
exit;
?>