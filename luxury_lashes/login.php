<?php
// login.php - Procesar formulario de login

session_start();
require_once 'includes/functions.php';
require_once 'includes/session.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Obtener y limpiar datos del formulario
$cedula = cleanInput($_POST['cedula'] ?? '');
$password = $_POST['password'] ?? '';

// Validaciones básicas
if (empty($cedula) || empty($password)) {
    $_SESSION['error_message'] = 'Por favor, completa todos los campos';
    header('Location: index.php');
    exit;
}

// Validar formato de cédula
if (!validateCedula($cedula)) {
    $_SESSION['error_message'] = 'Formato de cédula inválido. Solo se permiten números de 6 a 12 dígitos';
    header('Location: index.php');
    exit;
}

// Intentar validar login
$result = validateLogin($cedula, $password);

if (is_array($result) && isset($result['error'])) {
    // Usuario deshabilitado
    $_SESSION['error_message'] = $result['error'];
    header('Location: index.php');
    exit;
} elseif ($result) {
    // Login exitoso - iniciar sesión del usuario
    loginUser($result);
    
    // Redirigir según el rol
    if ($result['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/welcome.php');
    }
    exit;
    
} else {
    // Login fallido
    $_SESSION['error_message'] = 'Cédula o contraseña incorrectos';
    header('Location: index.php');
    exit;
}
?>