<?php
// config/database.php - Configuración de base de datos

// Configuración para desarrollo local (XAMPP)
$local_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'luxury_lashes_db'
];

// Configuración para servidor público (cPanel)
// Descomenta y modifica estas líneas cuando migres al servidor
/*
$production_config = [
    'host' => 'localhost', // o la IP del servidor MySQL
    'username' => 'tu_usuario_mysql',
    'password' => 'tu_contraseña_mysql',
    'database' => 'tu_base_de_datos'
];
*/

// Detectar si estamos en producción o desarrollo
function isProduction() {
    return isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost';
}

// Seleccionar configuración apropiada
if (isProduction()) {
    // En producción, usar configuración de cPanel
    // $config = $production_config;
    $config = $local_config; // Por ahora usar local hasta que configures producción
} else {
    // En desarrollo, usar configuración local
    $config = $local_config;
}

// Crear conexión a la base de datos
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    
    // Configurar PDO para mostrar errores y usar prepared statements
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // En caso de error, mostrar mensaje amigable
    die("Error de conexión a la base de datos. Por favor, verifica la configuración.");
}

// Función para obtener la conexión desde otros archivos
function getConnection() {
    global $pdo;
    return $pdo;
}
?>