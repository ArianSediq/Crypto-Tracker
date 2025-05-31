<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Du måste vara inloggad för att skapa inlägg.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Endast POST-metoden är tillåten.']);
    exit;
}

// Validera input
$crypto_symbol = filter_input(INPUT_POST, 'crypto_symbol', FILTER_SANITIZE_STRING);
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);

if (!$crypto_symbol || !$title || !$content) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Alla obligatoriska fält måste fyllas i.']);
    exit;
}

// Hantera bilduppladdning
$image_url = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Endast JPEG, PNG och GIF-bilder är tillåtna.']);
        exit;
    }
    
    // Skapa uploads-mapp om den inte finns
    $upload_dir = __DIR__ . '/../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generera unikt filnamn
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('post_') . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;
    
    // Flytta uppladdad fil
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $image_url = 'uploads/' . $file_name;
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Kunde inte spara bilden.']);
        exit;
    }
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Skapa inlägget
    $stmt = $db->prepare("
        INSERT INTO posts (user_id, crypto_symbol, title, content, image_url) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $crypto_symbol,
        $title,
        $content,
        $image_url
    ]);
    
    // Omdirigera tillbaka till diskussionssidan
    header('Location: ../pages/discussions.php');
    
} catch(PDOException $e) {
    // Ta bort uppladdad bild om databasfelet uppstår
    if ($image_url && file_exists(__DIR__ . '/../' . $image_url)) {
        unlink(__DIR__ . '/../' . $image_url);
    }
    
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Databasfel: ' . $e->getMessage()]);
}
?> 