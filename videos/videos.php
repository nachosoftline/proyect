<?php
session_start();
require 'db/db.php';

if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$search = $_GET['search'] ?? '';

$query = "SELECT id, title, tags FROM videos WHERE status = 1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR tags LIKE ?)";
    $params[] = "%$search%"; // Agregar el primer parámetro
    $params[] = "%$search%"; // Agregar el segundo parámetro
}

$query .= " LIMIT 9"; // Mover el límite aquí

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTech - Videos</title>
    <style>
        :root {
            --primary-color: #ff0050;
            --background-dark: #0a0a0a;
            --card-background: #1a1a1a;
        }

        body {
            background: var(--background-dark);
            color: white;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding-top: 80px;
        }

        .search-bar {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(0,0,0,0.9);
            padding: 10px 20px;
            border-radius: 30px;
            width: 90%;
            max-width: 600px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .search-bar input {
            width: calc(100% - 120px);
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            background: #222;
            color: white;
            font-size: 16px;
            outline: none;
        }

        .search-bar button {
            width: 100px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .search-bar button:hover {
            background: #ff0033;
            transform: scale(1.05);
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .video-card {
            background: var(--card-background);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(255, 0, 80, 0.2);
        }

        .thumbnail-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 aspect ratio */
            background: #000;
        }

        .thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 0, 80, 0.8);
            border: none;
            color: white;
            font-size: 2em;
            padding: 15px 25px;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .video-card:hover .play-button {
            opacity: 1;
        }

        .video-info {
            padding: 15px;
        }

        .video-title {
            margin: 0 0 10px 0;
            font-size: 1.1em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag {
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            transition: background 0.3s ease;
        }

        .tag:hover {
            background: var(--primary-color);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
        }

        .modal-content {
            position: relative;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 1000px;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
        }

        #videoPlayer {
            width: 100%;
            height: 70vh;
            object-fit: contain;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 3;
        }
    </style>
</head>
<body>
    <div class="search-bar">
        <form method="GET">
            <input type="text" name="search" placeholder="Buscar videos..." 
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <div class="video-grid">
        <?php foreach($videos as $video): ?>
        <div class="video-card" onclick="openModal(<?= $video['id'] ?>, '<?= addslashes($video['title']) ?>')">
            <div class="thumbnail-container">
                <video class="thumbnail-source" style="display:none;" 
                       data-id="<?= $video['id'] ?>" 
                       preload="metadata">
                    <source src="serve_video.php?id=<?= $video['id'] ?>" type="video/mp4">
                </video>
                <canvas class="thumbnail" id="canvas-<?= $video['id'] ?>"></canvas>
                <button class="play-button">▶</button>
            </div>
            <div class="video-info">
                <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                <div class="tags">
                    <?php foreach(explode(',', $video['tags']) as $tag): ?>
                        <span class="tag">#<?= trim(htmlspecialchars($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="modal" id="videoModal">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <video id="videoPlayer">
                Tu navegador no soporta videos HTML5
            </video>
        </div>
    </div>

    <script>
        // Generar miniaturas automáticamente
        document.querySelectorAll('.thumbnail-source').forEach(video => {
            const canvas = document.querySelector(`#canvas-${video.dataset.id}`);
            const ctx = canvas.getContext('2d');
            
            video.addEventListener('loadedmetadata', () => {
                // Configurar canvas con las dimensiones del video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Intentar capturar el frame inicial
                video.currentTime = 0.1;
            });

            video.addEventListener('seeked', () => {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            });

            video.addEventListener('error', (e) => {
                console.error('Error cargando video:', video.src, e);
                canvas.style.background = '#333';
            });
        });

        // Control del modal
        function openModal(id, title) {
            const modal = document.getElementById('videoModal');
            const player = document.getElementById('videoPlayer');
            player.src = `serve_video.php?id=${id}`;
            player.controls = true;
            modal.style.display = 'block';
            
            player.play().catch(error => {
                console.log('La reproducción automática fue bloqueada:', error);
            });
        }

        function closeModal() {
            const player = document.getElementById('videoPlayer');
            player.pause();
            player.src = '';
            document.getElementById('videoModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('videoModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>