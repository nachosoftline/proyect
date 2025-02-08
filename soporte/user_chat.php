<?php
session_start();
include 'db/db_connection.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener nombre de usuario
if (!isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $_SESSION['username'] = $user['name'] ?? 'Usuario';
    echo $_SESSION['username'] . " - " . $user['name'];
}

// Manejar envío de mensajes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
    }
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat de Soporte</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        #messages-container {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin: 10px 0;
            padding: 12px;
            border-radius: 8px;
            max-width: 70%;
            position: relative;
        }
        .user-message {
            background: #c8e6c9;
            margin-left: auto;
        }
        .tech-message {
            background: #e3f2fd;
            margin-right: auto;
        }
        .message-time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
            display: block;
        }
        #message-form {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        #message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-header">
            <h1>Chat de Soporte</h1>
        </div>
        
        <div id="messages-container">
            <div id="messages"></div>
        </div>
        
        <form id="message-form">
            <input type="text" id="message-input" placeholder="Escribe tu mensaje..." required autofocus>
            <button type="submit">Enviar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let lastMessageId = 0;
        let autoScroll = true;

        function loadMessages() {
            $.ajax({
                url: 'get_messages.php',
                data: { 
                    last_id: lastMessageId,
                    user_id: <?php echo $user_id; ?>
                },
                success: function(messages) {
                    if(messages.length > 0) {
                        messages.forEach(msg => {
                            const messageClass = msg.technician_id ? 'tech-message' : 'user-message';
                            $('#messages').append(`
                                <div class="message ${messageClass}">
                                    ${msg.message}
                                    <span class="message-time">
                                        ${new Date(msg.created_at).toLocaleTimeString([], {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </span>
                                </div>
                            `);
                            lastMessageId = msg.id;
                        });
                        
                        if(autoScroll) {
                            const container = document.getElementById('messages-container');
                            container.scrollTop = container.scrollHeight;
                        }
                    }
                }
            });
        }

        // Enviar mensaje con AJAX
        $('#message-form').submit(function(e) {
            e.preventDefault();
            const message = $('#message-input').val().trim();
            
            if(message) {
                $.ajax({
                    type: 'POST',
                    url: 'user_chat.php',
                    data: { message: message },
                    success: function() {
                        $('#message-input').val('');
                        autoScroll = true;
                        loadMessages();
                    }
                });
            }
        });

        // Controlar scroll para mantener autoScroll
        $('#messages-container').on('scroll', function() {
            const container = this;
            autoScroll = (container.scrollHeight - container.scrollTop === container.clientHeight);
        });

        // Actualizar mensajes cada segundo
        setInterval(loadMessages, 1000);
        loadMessages(); // Carga inicial
    </script>
</body>
</html>