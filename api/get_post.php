<?php
session_start();

// Validera input
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$post_id) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Ogiltig post-ID.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hämta inlägg med användarnamn
    $stmt = $db->prepare("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.id = ?
    ");
    
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Inlägget kunde inte hittas.']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($post);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Databasfel: ' . $e->getMessage()]);
}
?> 