<?php
session_start();

// Kontrollera admin-behörighet
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Du har inte behörighet att utföra denna åtgärd.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Endast POST-metoden är tillåten.']);
    exit;
}

// Validera action
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
if (!in_array($action, ['delete_user', 'toggle_role', 'delete_post'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Ogiltig åtgärd.']);
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    switch ($action) {
        case 'delete_user':
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            if (!$user_id || $user_id === $_SESSION['user_id']) {
                throw new Exception('Ogiltig användar-ID eller försök att ta bort egen användare.');
            }
            
            // Ta bort användaren och allt relaterat innehåll
            $db->beginTransaction();
            
            // Ta bort användarens posts
            $db->prepare("DELETE FROM posts WHERE user_id = ?")->execute([$user_id]);
            
            // Ta bort användarens portfolio
            $db->prepare("DELETE FROM portfolio WHERE user_id = ?")->execute([$user_id]);
            
            // Ta bort användarens favoriter
            $db->prepare("DELETE FROM favorites WHERE user_id = ?")->execute([$user_id]);
            
            // Ta bort användaren
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            
            $db->commit();
            $message = 'Användaren har tagits bort.';
            break;
            
        case 'toggle_role':
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            if (!$user_id || $user_id === $_SESSION['user_id']) {
                throw new Exception('Ogiltig användar-ID eller försök att ändra egen roll.');
            }
            
            // Hämta nuvarande roll
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $current_role = $stmt->fetchColumn();
            
            // Växla mellan admin och user
            $new_role = $current_role === 'admin' ? 'user' : 'admin';
            $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
            
            $message = 'Användarens roll har uppdaterats.';
            break;
            
        case 'delete_post':
            $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
            if (!$post_id) {
                throw new Exception('Ogiltig post-ID.');
            }
            
            // Ta bort inlägget
            $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
            $message = 'Inlägget har tagits bort.';
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch(Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?> 