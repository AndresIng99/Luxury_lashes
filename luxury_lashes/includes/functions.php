<?php
// includes/functions.php - Funciones auxiliares del sistema (ACTUALIZADO)

require_once __DIR__ . '/../config/database.php';

// Función para validar login (actualizada para verificar estado)
function validateLogin($cedula, $password) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT id, cedula, nombres_completos, password, role, status FROM users WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Verificar que el usuario esté activo
            if ($user['status'] === 'inactivo') {
                return ['error' => 'Usuario deshabilitado. Contacta al administrador.'];
            }
            return $user;
        }
        
        return false;
        
    } catch(PDOException $e) {
        return false;
    }
}

// Función para obtener información del usuario
function getUserById($id) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
        
    } catch(PDOException $e) {
        return false;
    }
}

// Función para crear un nuevo usuario
function createUser($data) {
    $pdo = getConnection();
    
    try {
        // Verificar que la cédula no exista
        $check = $pdo->prepare("SELECT id FROM users WHERE cedula = ?");
        $check->execute([$data['cedula']]);
        
        if ($check->rowCount() > 0) {
            return ['success' => false, 'message' => 'La cédula ya está registrada'];
        }
        
        // Verificar que el email no exista
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$data['email']]);
        
        if ($check->rowCount() > 0) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Encriptar contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insertar usuario (por defecto activo)
        $stmt = $pdo->prepare("INSERT INTO users (cedula, nombres_completos, celular, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, 'activo')");
        
        $result = $stmt->execute([
            $data['cedula'],
            $data['nombres_completos'],
            $data['celular'],
            $data['email'],
            $hashedPassword,
            $data['role']
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Usuario creado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al crear el usuario'];
        }
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

// Función para obtener todos los usuarios (actualizada)
function getAllUsers() {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT id, cedula, nombres_completos, celular, email, role, status, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch(PDOException $e) {
        return [];
    }
}

// Función para contar usuarios por rol y estado (actualizada)
function getUserStats() {
    $pdo = getConnection();
    
    try {
        // Estadísticas por rol
        $stmt = $pdo->prepare("SELECT role, COUNT(*) as total FROM users WHERE status = 'activo' GROUP BY role");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $stats = ['admin' => 0, 'colaborador' => 0, 'total_activos' => 0];
        
        foreach ($results as $row) {
            $stats[$row['role']] = $row['total'];
            $stats['total_activos'] += $row['total'];
        }
        
        // Total general (incluyendo inactivos)
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $total = $stmt->fetch();
        $stats['total'] = $total['total'];
        
        // Usuarios inactivos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'inactivo'");
        $stmt->execute();
        $inactivos = $stmt->fetch();
        $stats['inactivos'] = $inactivos['total'];
        
        return $stats;
        
    } catch(PDOException $e) {
        return ['admin' => 0, 'colaborador' => 0, 'total' => 0, 'total_activos' => 0, 'inactivos' => 0];
    }
}

// NUEVA FUNCIÓN: Obtener usuario por ID para editar
function getUserForEdit($userId) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT id, cedula, nombres_completos, celular, email, role, status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
        
    } catch(PDOException $e) {
        return false;
    }
}

// NUEVA FUNCIÓN: Actualizar usuario
function updateUser($userId, $data) {
    $pdo = getConnection();
    
    try {
        // Obtener datos actuales del usuario
        $currentUser = getUserForEdit($userId);
        if (!$currentUser) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Verificar que la cédula no exista en otro usuario
        if ($data['cedula'] !== $currentUser['cedula']) {
            $check = $pdo->prepare("SELECT id FROM users WHERE cedula = ? AND id != ?");
            $check->execute([$data['cedula'], $userId]);
            
            if ($check->rowCount() > 0) {
                return ['success' => false, 'message' => 'La cédula ya está registrada en otro usuario'];
            }
        }
        
        // Verificar que el email no exista en otro usuario
        if ($data['email'] !== $currentUser['email']) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$data['email'], $userId]);
            
            if ($check->rowCount() > 0) {
                return ['success' => false, 'message' => 'El email ya está registrado en otro usuario'];
            }
        }
        
        // Preparar la consulta de actualización
        if (!empty($data['password'])) {
            // Actualizar con nueva contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET cedula = ?, nombres_completos = ?, celular = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $params = [
                $data['cedula'],
                $data['nombres_completos'],
                $data['celular'],
                $data['email'],
                $hashedPassword,
                $data['role'],
                $userId
            ];
        } else {
            // Actualizar sin cambiar contraseña
            $stmt = $pdo->prepare("UPDATE users SET cedula = ?, nombres_completos = ?, celular = ?, email = ?, role = ? WHERE id = ?");
            $params = [
                $data['cedula'],
                $data['nombres_completos'],
                $data['celular'],
                $data['email'],
                $data['role'],
                $userId
            ];
        }
        
        $result = $stmt->execute($params);
        
        if ($result) {
            return ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar el usuario'];
        }
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

// FUNCIONES PARA EL SISTEMA DE SERVICIOS

// Función para obtener todas las áreas de servicio
function getAreasServicio() {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM areas_servicio WHERE activo = 'si' ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Función para obtener servicios por área
function getServiciosPorArea($areaId) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM servicios WHERE area_id = ? AND activo = 'si' ORDER BY nombre");
        $stmt->execute([$areaId]);
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Función para obtener todos los colaboradores activos
function getColaboradoresActivos() {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT id, cedula, nombres_completos FROM users WHERE status = 'activo' ORDER BY nombres_completos");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Función para registrar un servicio
function registrarServicio($data) {
    $pdo = getConnection();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO registros_servicios 
            (colaborador_id, registrado_por, tipo, area_id, servicio_id, producto_personalizado, descripcion, metodo_pago, monto, fecha_servicio) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['colaborador_id'],
            $data['registrado_por'],
            $data['tipo'],
            $data['area_id'] ?? null,
            $data['servicio_id'] ?? null,
            $data['producto_personalizado'] ?? null,
            $data['descripcion'] ?? null,
            $data['metodo_pago'],
            $data['monto'],
            $data['fecha_servicio']
        ]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Servicio registrado exitosamente'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el servicio'];
        }
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

// Función para obtener historial de servicios
function getHistorialServicios($colaboradorId = null, $limit = 50) {
    $pdo = getConnection();
    
    try {
        if ($colaboradorId) {
            $stmt = $pdo->prepare("
                SELECT rs.*, 
                       uc.nombres_completos as colaborador_nombre,
                       ur.nombres_completos as registrado_por_nombre,
                       a.nombre as area_nombre,
                       s.nombre as servicio_nombre
                FROM registros_servicios rs
                LEFT JOIN users uc ON rs.colaborador_id = uc.id
                LEFT JOIN users ur ON rs.registrado_por = ur.id
                LEFT JOIN areas_servicio a ON rs.area_id = a.id
                LEFT JOIN servicios s ON rs.servicio_id = s.id
                WHERE rs.colaborador_id = ?
                ORDER BY rs.fecha_servicio DESC
                LIMIT ?
            ");
            $stmt->execute([$colaboradorId, $limit]);
        } else {
            $stmt = $pdo->prepare("
                SELECT rs.*, 
                       uc.nombres_completos as colaborador_nombre,
                       ur.nombres_completos as registrado_por_nombre,
                       a.nombre as area_nombre,
                       s.nombre as servicio_nombre
                FROM registros_servicios rs
                LEFT JOIN users uc ON rs.colaborador_id = uc.id
                LEFT JOIN users ur ON rs.registrado_por = ur.id
                LEFT JOIN areas_servicio a ON rs.area_id = a.id
                LEFT JOIN servicios s ON rs.servicio_id = s.id
                ORDER BY rs.fecha_servicio DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
        }
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Función para obtener estadísticas de servicios
function getEstadisticasServicios($colaboradorId = null) {
    $pdo = getConnection();
    
    try {
        $where = $colaboradorId ? "WHERE colaborador_id = ?" : "";
        $params = $colaboradorId ? [$colaboradorId] : [];
        
        // Total de servicios
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registros_servicios $where");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Total por tipo
        $stmt = $pdo->prepare("SELECT tipo, COUNT(*) as cantidad FROM registros_servicios $where GROUP BY tipo");
        $stmt->execute($params);
        $porTipo = $stmt->fetchAll();
        
        // Total de ingresos
        $stmt = $pdo->prepare("SELECT SUM(monto) as total_ingresos FROM registros_servicios $where");
        $stmt->execute($params);
        $ingresos = $stmt->fetch()['total_ingresos'] ?? 0;
        
        return [
            'total_servicios' => $total,
            'por_tipo' => $porTipo,
            'total_ingresos' => $ingresos
        ];
    } catch(PDOException $e) {
        return [
            'total_servicios' => 0,
            'por_tipo' => [],
            'total_ingresos' => 0
        ];
    }
}

// NUEVA FUNCIÓN: Cambiar estado de usuario
function toggleUserStatus($userId) {
    $pdo = getConnection();
    
    try {
        // Obtener estado actual
        $stmt = $pdo->prepare("SELECT status, nombres_completos FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Cambiar estado
        $newStatus = $user['status'] === 'activo' ? 'inactivo' : 'activo';
        
        $update = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $result = $update->execute([$newStatus, $userId]);
        
        if ($result) {
            $action = $newStatus === 'activo' ? 'habilitado' : 'deshabilitado';
            return [
                'success' => true, 
                'message' => "Usuario {$user['nombres_completos']} {$action} exitosamente",
                'new_status' => $newStatus
            ];
        } else {
            return ['success' => false, 'message' => 'Error al cambiar el estado del usuario'];
        }
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

// Función para validar formato de cédula (solo números)
function validateCedula($cedula) {
    return preg_match('/^[0-9]{6,12}$/', $cedula);
}

// Función para validar formato de email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para validar formato de celular
function validateCelular($celular) {
    return preg_match('/^[0-9]{10,15}$/', $celular);
}

// Función para limpiar datos de entrada
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para formatear fecha
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Función para generar mensaje de alerta HTML
function showAlert($type, $message) {
    $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
    return "<div class='alert $alertClass'>$message</div>";
}
?>