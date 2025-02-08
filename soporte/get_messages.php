<?php
session_start();
include 'db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM messages 
                      WHERE user_id = ? 
                      AND id > ? 
                      ORDER BY created_at ASC");
$stmt->execute([$user_id, $last_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
?>