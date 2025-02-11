<?php
require_once __DIR__ . '/../../includes/seguridad.php';
require_once __DIR__ . '/../../includes/config.php';

verificarSesion();
require 'db/db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validar campos obligatorios
        $title = trim($_POST['title'] ?? '');
        $rawTags = $_POST['tags'] ?? [];
        $file = $_FILES['video'] ?? null;

        // Convertir a array y filtrar
        $tags = is_array($rawTags) ? array_filter(array_map('trim', $rawTags)) : [];
        
        // Validaciones básicas
        if (empty($title)) {
            throw new Exception("El título es requerido");
        }
        
        if (count($tags) < 1) {
            throw new Exception("Al menos una etiqueta es requerida");
        }
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error en la subida del archivo: " . ($file['error'] ?? 'Archivo no recibido'));
        }

        // Validar tipo de archivo
        $allowedTypes = ['video/mp4'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Solo se permiten archivos MP4");
        }

        // Procesar tags
        $tags = array_slice($tags, 0, 3);
        $cleanTags = array_map(function($tag) {
            return preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]/', '', $tag);
        }, $tags);
        
        $tagsString = implode(',', $cleanTags);

        // Generar nombre único
        $filename = 'vid_' . uniqid() . '.mp4';
        $targetPath = __DIR__ . '../../../../videos/contenido/' . $filename;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Error al guardar el archivo");
        }

        // Insertar en base de datos
        $stmt = $pdo->prepare("INSERT INTO videos (title, filename, tags) VALUES (?, ?, ?)");
        if (!$stmt->execute([$title, $filename, $tagsString])) {
            @unlink($targetPath); // Eliminar archivo si falla la inserción
            throw new Exception("Error al guardar en la base de datos");
        }

        $success = "Video subido exitosamente!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Video</title>
    <style>
        .upload-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #1a1a1a;
            border-radius: 10px;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        input[type="text"], 
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            background: #333;
            border: 1px solid #444;
            color: white;
            border-radius: 5px;
        }

        button[type="submit"] {
            background: #ff0050;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
        }

        .error {
            color: #ff4444;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ff4444;
            border-radius: 5px;
        }

        .success {
            color: #00ff88;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #00ff88;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="upload-form">
        <h2>Subir nuevo video</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Título del video:</label>
                <input type="text" name="title" required 
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Archivo de video (MP4):</label>
                <input type="file" name="video" accept="video/mp4" required>
            </div>

            <div class="form-group">
                <label>Etiquetas (máximo 3):</label>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <input type="text" name="tags[]" 
                           placeholder="Etiqueta <?= $i + 1 ?>" 
                           value="<?= htmlspecialchars($_POST['tags'][$i] ?? '') ?>">
                <?php endfor; ?>
            </div>

            <button type="submit">Subir Video</button>
        </form>
    </div>
    <script>
        // Limpiar campo de archivo después de éxito
        <?php if ($success): ?>
            document.getElementById('fileInput').value = '';
        <?php endif; ?>
        
        // Prevenir doble submit
        let formSubmitted = false;
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                alert('El archivo ya se está subiendo');
            }
            formSubmitted = true;
        });
    </script>
</body>
</html>