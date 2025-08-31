<?php
// admin/check_user_permissions.php - Verificar permisos antes de editar usuarios críticos

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Esta función verifica si un usuario puede ser editado/eliminado
function canEditUser($userId, $currentUserId) {
    $pdo = getConnection();
    
    try {
        // Obtener información del usuario a editar
        $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userToEdit = $stmt->fetch();
        
        if (!$userToEdit) {
            return ['can_edit' => false, 'reason' => 'Usuario no encontrado'];
        }
        
        // No permitir editar al último administrador
        if ($userToEdit['role'] === 'admin') {
            $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND status = 'activo'");
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['admin_count'] <= 1) {
                return ['can_edit' => false, 'reason' => 'No se puede editar al último administrador activo del sistema'];
            }
        }
        
        return ['can_edit' => true, 'reason' => ''];
        
    } catch(PDOException $e) {
        return ['can_edit' => false, 'reason' => 'Error en la base de datos'];
    }
}

// Si se llama vía AJAX para verificar permisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_user_id'])) {
    header('Content-Type: application/json');
    
    $userId = intval($_POST['check_user_id']);
    $currentUser = getCurrentUser();
    
    $result = canEditUser($userId, $currentUser['id']);
    
    echo json_encode($result);
    exit;
}
?>