<?php
session_start();
include 'db/db_connection.php';

// Verificar autenticación
if (!isset($_SESSION['technician_id'])) {
    header("Location: login_technician.php");
    exit();
}

$technician_id = $_SESSION['technician_id'];
$selected_user_id = null;

// Manejar solicitudes AJAX
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    // Obtener usuarios ordenados por mensajes no leídos
    if ($_GET['ajax'] == 'get_users') {
        $stmt = $pdo->prepare("SELECT 
                u.id, 
                u.name, 
                COUNT(m.id) AS unread_count 
            FROM users u
            LEFT JOIN messages m 
                ON u.id = m.user_id 
                AND m.is_read = 0
                AND m.technician_id IS NULL
            GROUP BY u.id
            ORDER BY unread_count DESC");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }
    
    // Obtener mensajes actualizados
    if ($_GET['ajax'] == 'get_messages' && isset($_GET['user_id'])) {
        $user_id = (int)$_GET['user_id'];
        
        // Obtener todos los mensajes
        $stmt = $pdo->prepare("SELECT * FROM messages 
                              WHERE user_id = ? 
                              ORDER BY created_at ASC");
        $stmt->execute([$user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Marcar como leídos y actualizar última lectura
        $pdo->beginTransaction();
        try {
            // Actualizar mensajes no leídos
            $pdo->prepare("UPDATE messages 
                          SET is_read = 1 
                          WHERE user_id = ? 
                          AND technician_id IS NULL")
               ->execute([$user_id]);
            
            // Registrar última lectura
            $pdo->prepare("INSERT INTO technician_messages 
                          (technician_id, user_id, last_read_at) 
                          VALUES (?, ?, NOW()) 
                          ON DUPLICATE KEY UPDATE last_read_at = NOW()")
               ->execute([$technician_id, $user_id]);
            
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error updating read status: " . $e->getMessage());
        }
        
        echo json_encode($messages);
        exit();
    }
}

// Manejar selección de usuario
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $selected_user_id = (int)$_GET['user_id'];
    
    // Validar existencia del usuario
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    if (!$stmt->fetch()) {
        $selected_user_id = null;
    }
}

// Obtener lista inicial de usuarios
$stmt = $pdo->prepare("SELECT 
        u.id, 
        u.name, 
        COUNT(m.id) AS unread_count 
    FROM users u
    LEFT JOIN messages m 
        ON u.id = m.user_id 
        AND m.is_read = 0
        AND m.technician_id IS NULL
    GROUP BY u.id
    ORDER BY unread_count DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar envío de respuestas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message']) && $selected_user_id) {
    $reply_message = trim($_POST['reply_message']);
    
    if (!empty($reply_message)) {
        $stmt = $pdo->prepare("INSERT INTO messages 
                              (user_id, technician_id, message) 
                              VALUES (?, ?, ?)");
        $stmt->execute([$selected_user_id, $technician_id, $reply_message]);
    }
    
    header("Location: technician_panel.php?user_id=" . $selected_user_id);
    exit();
}

// Obtener mensajes iniciales si hay usuario seleccionado
$messages = [];
if ($selected_user_id) {
    $stmt = $pdo->prepare("SELECT * FROM messages 
                          WHERE user_id = ? 
                          ORDER BY created_at ASC");
    $stmt->execute([$selected_user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel Técnico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f0f2f5; }
        .container { display: flex; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .user-list { width: 300px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .chat-container { flex: 1; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .user-item { padding: 15px; cursor: pointer; transition: background 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .user-item:hover { background: #f8f9fa; }
        .user-item.active { background: #e9f5ff; }
        .message { margin: 10px; padding: 12px; border-radius: 8px; max-width: 70%; position: relative; }
        .user-message { background: #e3f2fd; margin-right: auto; }
        .tech-message { background: #c8e6c9; margin-left: auto; }
        .message-time { font-size: 0.8em; color: #666; margin-top: 5px; display: block; }
        #message-form { padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; }
        textarea { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 60px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .unread-badge { background: #ff4444; color: white; padding: 3px 8px; border-radius: 12px; font-size: 0.85em; }
        .no-unread { background: #e9ecef; color: #666; }
        #messages-container { height: 500px; overflow-y: auto; padding: 20px; }
    </style>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    
    <div class="container">
        <div class="user-list">
            <h2 style="padding: 15px; margin: 0; border-bottom: 1px solid #eee;">Usuarios</h2>
            <div id="users-list">
                <?php foreach ($users as $user): ?>
                    <div class="user-item <?php echo ($selected_user_id == $user['id']) ? 'active' : ''; ?>" 
                         onclick="selectUser(<?php echo $user['id']; ?>)">
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                        <span class="unread-badge <?php echo ($user['unread_count'] == 0) ? 'no-unread' : ''; ?>">
                            <?php echo $user['unread_count']; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat-container">
            <?php if ($selected_user_id): ?>
                <div id="messages-container">
                    <div id="messages">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['technician_id'] ? 'tech-message' : 'user-message'; ?>">
                                <?php echo htmlspecialchars($message['message']); ?>
                                <span class="message-time">
                                    <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form id="message-form" method="POST">
                    <textarea name="reply_message" placeholder="Escribe tu respuesta..." required></textarea>
                    <button type="submit">Enviar</button>
                </form>
            <?php else: ?>
                <div style="padding: 20px; color: #666;">
                    Selecciona un usuario para ver los mensajes
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentUserId = <?php echo $selected_user_id ?? 'null'; ?>;
        let autoRefresh = true;

        function selectUser(userId) {
            currentUserId = userId;
            autoRefresh = true;
            history.replaceState(null, null, `?user_id=${userId}`);
            refreshData();
        }

        function refreshData() {
            // Actualizar lista de usuarios
            $.get('technician_panel.php?ajax=get_users', users => {
                $('#users-list').html(users.map(user => `
                    <div class="user-item ${currentUserId == user.id ? 'active' : ''}" 
                         onclick="selectUser(${user.id})">
                        <span>${user.name}</span>
                        <span class="unread-badge ${user.unread_count == 0 ? 'no-unread' : ''}">
                            ${user.unread_count}
                        </span>
                    </div>
                `).join(''));
            });

            // Actualizar mensajes si hay usuario seleccionado
            if (currentUserId && autoRefresh) {
                $.get(`technician_panel.php?ajax=get_messages&user_id=${currentUserId}`, messages => {
                    $('#messages').html(messages.map(msg => `
                        <div class="message ${msg.technician_id ? 'tech-message' : 'user-message'}">
                            ${msg.message}
                            <span class="message-time">
                                ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            </span>
                        </div>
                    `).join(''));
                    
                    // Scroll automático al final
                    const container = document.getElementById('messages-container');
                    container.scrollTop = container.scrollHeight;
                });
            }
        }

        // Enviar mensaje con AJAX
        $('#message-form').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: 'technician_panel.php?user_id=' + currentUserId,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: () => {
                    $('textarea[name="reply_message"]').val('');
                    autoRefresh = true;
                    refreshData();
                }
            });
        });

        // Actualizar cada segundo
        setInterval(refreshData, 1000);
        refreshData(); // Carga inicial
    </script>
</body>
</html>