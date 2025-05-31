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
$portfolio_id = filter_input(INPUT_POST, 'portfolio_id', FILTER_VALIDATE_INT);

if (!$portfolio_id) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Ogiltig portfolio-ID.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verifiera att tillgången tillhör användaren
    $stmt = $db->prepare("SELECT user_id FROM portfolio WHERE id = ?");
    $stmt->execute([$portfolio_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result || $result['user_id'] !== $_SESSION['user_id']) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Du har inte behörighet att ta bort denna tillgång.']);
        exit;
    }
    
    // Ta bort tillgången
    $stmt = $db->prepare("DELETE FROM portfolio WHERE id = ? AND user_id = ?");
    $stmt->execute([$portfolio_id, $_SESSION['user_id']]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Tillgång borttagen från portfolion.']);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Databasfel: ' . $e->getMessage()]);
}
?> 