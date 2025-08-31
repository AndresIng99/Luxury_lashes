<?php
// admin/manage_users.php - Gestionar usuarios

session_start();
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Verificar que el usuario sea administrador
requireAdmin();

// Obtener datos del usuario actual
$current_user = getCurrentUser();

// Obtener todos los usuarios
$users = getAllUsers();

// Obtener estadísticas
$stats = getUserStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Luxury Lashes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>💄 Luxury Lashes</h1>
                    <p>Panel de Administración</p>
                </div>
                <div class="user-info">
                    <div class="welcome-text">
                        Hola, <strong><?php echo htmlspecialchars($current_user['nombres_completos']); ?></strong>
                        <br><small>Administrador</small>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        🚪 Cerrar Sesión
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
                    <a href="dashboard.php" class="nav-link">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="create_user.php" class="nav-link">
                        Crear Usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link active">
                        Gestionar Usuarios
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="container">
            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total de Usuarios</div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-number"><?php echo $stats['admin']; ?></div>
                    <div class="stat-label">Administradores Activos</div>
                </div>
                <div class="stat-card colaborador">
                    <div class="stat-number"><?php echo $stats['colaborador']; ?></div>
                    <div class="stat-label">Colaboradores Activos</div>
                </div>
                <div class="stat-card" style="border-left-color: #dc3545;">
                    <div class="stat-number" style="color: #dc3545;"><?php echo $stats['inactivos']; ?></div>
                    <div class="stat-label">Usuarios Inactivos</div>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Panel de usuarios -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <h2 class="card-title mb-0">Usuarios Registrados</h2>
                    <a href="create_user.php" class="btn btn-primary">
                        Agregar Usuario
                    </a>
                </div>

                <?php if (empty($users)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 20px;">👥</div>
                        <h3>No hay usuarios registrados</h3>
                        <p>Comienza creando el primer usuario del sistema.</p>
                        <a href="create_user.php" class="btn btn-primary mt-2">
                            Crear Primer Usuario
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Filtros -->
                    <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="form-input" 
                                                                        placeholder="Buscar por nombre, cédula o email..."
                            style="max-width: 300px;"
                        >
                        <select id="statusFilter" class="form-select" style="max-width: 150px;">
                            <option value="">Todos los estados</option>
                            <option value="activo">Solo activos</option>
                            <option value="inactivo">Solo inactivos</option>
                        </select>
                        <select id="roleFilter" class="form-select" style="max-width: 150px;">
                            <option value="">Todos los roles</option>
                            <option value="admin">Solo admins</option>
                            <option value="colaborador">Solo colaboradores</option>
                        </select>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="table-container">
                        <table class="table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre Completo</th>
                                    <th>Contacto</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha de Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr data-status="<?php echo $user['status']; ?>" data-role="<?php echo $user['role']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['cedula']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($user['nombres_completos']); ?>
                                        </td>
                                        <td>
                                            <div>
                                                <a href="tel:+57<?php echo htmlspecialchars($user['celular']); ?>" 
                                                   style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                                                    📱 <?php echo htmlspecialchars($user['celular']); ?>
                                                </a>
                                            </div>
                                            <div style="margin-top: 5px;">
                                                <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" 
                                                   style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                                                    📧 <?php echo htmlspecialchars($user['email']); ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo $user['role'] === 'admin' ? 'Admin' : 'Colaborador'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="role-badge <?php echo $user['status'] === 'activo' ? 'role-colaborador' : ''; ?>" 
                                                  style="<?php echo $user['status'] === 'inactivo' ? 'background: rgb(220 38 38 / 0.1); color: var(--danger-color);' : ''; ?>">
                                                <?php echo $user['status'] === 'activo' ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo formatDate($user['created_at']); ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                <!-- Botón Editar -->
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                                   class="btn" 
                                                   style="padding: 4px 8px; font-size: 0.75rem; background: var(--primary-color); color: white; text-decoration: none;">
                                                    Editar
                                                </a>
                                                
                                                <!-- Botón Habilitar/Deshabilitar -->
                                                <?php if ($user['id'] != $current_user['id']): ?>
                                                    <form method="POST" action="toggle_user_status.php" style="display: inline;" 
                                                          onsubmit="return confirm('¿Estás seguro de <?php echo $user['status'] === 'activo' ? 'deshabilitar' : 'habilitar'; ?> a <?php echo htmlspecialchars($user['nombres_completos']); ?>?')">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn" style="padding: 4px 8px; font-size: 0.75rem; <?php echo $user['status'] === 'activo' ? 'background: var(--danger-color);' : 'background: var(--success-color);'; ?> color: white;">
                                                            <?php echo $user['status'] === 'activo' ? 'Deshabilitar' : 'Habilitar'; ?>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span style="color: var(--text-muted); font-size: 0.75rem; padding: 4px 8px;">Tu cuenta</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Información adicional -->
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; color: #666;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <strong>Total de usuarios:</strong> <?php echo count($users); ?>
                            </div>
                            <div>
                                <strong>Último registro:</strong> 
                                <?php 
                                if (!empty($users)) {
                                    echo formatDate($users[0]['created_at']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                            <div>
                                <strong>Estado del sistema:</strong> 
                                <span style="color: #28a745; font-weight: bold;">✅ Activo</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información sobre roles -->
            <div class="card fade-in">
                <h3 class="card-title">Información sobre Roles de Usuario</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    <div class="info-card" style="background: rgb(37 99 235 / 0.05); border-color: rgb(37 99 235 / 0.1);">
                        <h4 style="color: var(--primary-color);">
                            <span style="background: rgb(37 99 235 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">👑</span>
                            Administrador
                        </h4>
                        <ul style="margin-left: 0; list-style: none; padding-left: 0;">
                            <li style="margin-bottom: 6px;">• Acceso completo al sistema</li>
                            <li style="margin-bottom: 6px;">• Crear y gestionar usuarios</li>
                            <li style="margin-bottom: 6px;">• Ver estadísticas y reportes</li>
                            <li style="margin-bottom: 0;">• Habilitar/deshabilitar usuarios</li>
                        </ul>
                    </div>
                    
                    <div class="info-card" style="background: rgb(5 150 105 / 0.05); border-color: rgb(5 150 105 / 0.1);">
                        <h4 style="color: var(--success-color);">
                            <span style="background: rgb(5 150 105 / 0.1); padding: 8px; border-radius: 8px; margin-right: 8px;">👤</span>
                            Colaborador
                        </h4>
                        <ul style="margin-left: 0; list-style: none; padding-left: 0;">
                            <li style="margin-bottom: 6px;">• Acceso a funciones básicas</li>
                            <li style="margin-bottom: 6px;">• Pantalla de bienvenida personalizada</li>
                            <li style="margin-bottom: 6px;">• Preparado para nuevas funcionalidades</li>
                            <li style="margin-bottom: 0;">• Acceso controlado y seguro</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Función de búsqueda y filtrado
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            let visibleRows = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                const rowStatus = row.getAttribute('data-status');
                const rowRole = row.getAttribute('data-role');
                
                let matchesSearch = false;
                let matchesStatus = !statusFilter || rowStatus === statusFilter;
                let matchesRole = !roleFilter || rowRole === roleFilter;
                
                // Buscar en todas las celdas de texto de la fila
                if (!searchTerm) {
                    matchesSearch = true;
                } else {
                    for (let j = 0; j < cells.length - 1; j++) { // Excluir columna de acciones
                        if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                            matchesSearch = true;
                            break;
                        }
                    }
                }
                
                // Mostrar u ocultar la fila
                const shouldShow = matchesSearch && matchesStatus && matchesRole;
                row.style.display = shouldShow ? '' : 'none';
                
                if (shouldShow) visibleRows++;
            }
            
            // Mostrar mensaje si no se encuentran resultados
            const tbody = table.getElementsByTagName('tbody')[0];
            const existingMessage = document.getElementById('noResultsMessage');
            
            if (existingMessage) {
                existingMessage.remove();
            }
            
            if (visibleRows === 0) {
                const messageRow = document.createElement('tr');
                messageRow.id = 'noResultsMessage';
                messageRow.innerHTML = '<td colspan="7" style="text-align: center; padding: 40px; color: #666;"><div style="font-size: 2rem; margin-bottom: 10px;">🔍</div><strong>No se encontraron resultados</strong><br>Intenta con otros términos de búsqueda o filtros</td>';
                tbody.appendChild(messageRow);
            }
        }

        // Event listeners para los filtros
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('roleFilter').addEventListener('change', filterTable);

        // Función para copiar información de contacto
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = element.innerHTML;
                element.innerHTML = '✅ Copiado';
                element.style.color = '#28a745';
                
                setTimeout(function() {
                    element.innerHTML = originalText;
                    element.style.color = '#667eea';
                }, 1000);
            });
        }

        // Añadir funcionalidad de copia a los enlaces de email y teléfono
        document.querySelectorAll('a[href^="mailto:"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const email = this.getAttribute('href').replace('mailto:', '');
                copyToClipboard(email, this);
            });
        });

        document.querySelectorAll('a[href^="tel:"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const phone = this.getAttribute('href').replace('tel:+57', '');
                copyToClipboard(phone, this);
            });
        });

        // Resaltar filas de usuarios inactivos
        document.querySelectorAll('tr[data-status="inactivo"]').forEach(function(row) {
            row.style.backgroundColor = '#fff5f5';
            row.style.opacity = '0.8';
        });
    </script>
</body>
</html>