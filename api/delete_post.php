<?php
require_once '../php/session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Du måste vara inloggad för att ta bort inlägg.']);
    exit;
}

// Check if post_id was provided
if (!isset($_POST['post_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Inget inläggs-ID angivet.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if the user is authorized to delete this post
    $stmt = $db->prepare("SELECT user_id, image_url FROM posts WHERE id = ?");
    $stmt->execute([$_POST['post_id']]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Inlägget kunde inte hittas.']);
        exit;
    }
    
    // Check if user is authorized (post owner or admin)
    if ($post['user_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Du har inte behörighet att ta bort detta inlägg.']);
        exit;
    }
    
    // Delete the post's image if it exists
    if ($post['image_url'] && file_exists(__DIR__ . '/../' . $post['image_url'])) {
        unlink(__DIR__ . '/../' . $post['image_url']);
    }
    
    // Delete the post
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$_POST['post_id']]);
    
    // Redirect back to the previous page
    $referer = $_SERVER['HTTP_REFERER'] ?? '/Crypto-Tracker/pages/discussions.php';
    header('Location: ' . $referer);
    exit;
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Databasfel: ' . $e->getMessage()]);
    exit;
} 