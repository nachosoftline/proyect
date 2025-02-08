<?php
session_start();
require 'db/db.php';

if (!isset($_SESSION['email'])) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// Validar el ID del video
$videoId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($videoId === false) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$stmt = $pdo->prepare("SELECT filename FROM videos WHERE id = ? AND status = 1");
$stmt->execute([$videoId]);
$video = $stmt->fetch();

if (!$video) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

$filePath = 'contenido/' . $video['filename'];

// Verificar si el archivo existe
if (!file_exists($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Prevenir descarga directa
header('Content-Type: video/mp4');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');

// Buffer de salida
readfile($filePath);
exit;