<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Du måste vara inloggad för att utföra denna åtgärd.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Endast POST-metoden är tillåten.']);
    exit;
}

// Validera input
$crypto_symbol = filter_input(INPUT_POST, 'crypto_symbol', FILTER_SANITIZE_STRING);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$purchase_price = filter_input(INPUT_POST, 'purchase_price', FILTER_VALIDATE_FLOAT);

if (!$crypto_symbol || !$amount || !$purchase_price || $amount <= 0 || $purchase_price <= 0) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Ogiltiga värden angivna.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lägg till i portfolio
    $stmt = $db->prepare("INSERT INTO portfolio (user_id, crypto_symbol, amount, purchase_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $crypto_symbol, $amount, $purchase_price]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Tillgång tillagd i portfolion.']);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Databasfel: ' . $e->getMessage()]);
}
?> 