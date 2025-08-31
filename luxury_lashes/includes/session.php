<?php
// includes/session.php - Manejo de sesiones

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

// Función para verificar si el usuario es colaborador
function isColaborador() {
    return isLoggedIn() && $_SESSION['user_role'] === 'colaborador';
}

// Función para obtener datos del usuario logueado
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'cedula' => $_SESSION['user_cedula'],
        'nombres_completos' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ];
}

// Función para iniciar sesión del usuario
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_cedula'] = $user['cedula'];
    $_SESSION['user_name'] = $user['nombres_completos'];
    $_SESSION['user_role'] = $user['role'];
    
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
}

// Función para cerrar sesión
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

// Función para requerir login (redirige si no está logueado)
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

// Función para requerir rol de administrador
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: ../user/welcome.php');
        exit;
    }
}

// Función para requerir rol de colaborador
function requireColaborador() {
    requireLogin();
    
    if (!isColaborador()) {
        header('Location: ../admin/dashboard.php');
        exit;
    }
}

// Función para redirigir según el rol del usuario
function redirectByRole() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
    
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
        exit;
    } else if (isColaborador()) {
        header('Location: user/welcome.php');
        exit;
    }
}
?>