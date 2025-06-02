<?php
require_once '../../php/session.php';
require_once '../../config.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Crypto-Tracker/');
    exit;
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
        
        // Start transaction to ensure both operations complete or none do
        $db->beginTransaction();
        
        // First delete all posts by this user
        $stmt = $db->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Then delete the user
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$_POST['user_id']]);
        
        // Commit the transaction
        $db->commit();
        
        $success_message = "Användaren och alla tillhörande inlägg har tagits bort";
    } catch(PDOException $e) {
        // Rollback on error
        $db->rollBack();
        $error_message = "Kunde inte ta bort användaren: " . $e->getMessage();
    }
}

// Handle role changes
if (isset($_POST['change_role'])) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
        $success_message = "Användarens roll har uppdaterats";
    } catch(PDOException $e) {
        $error_message = "Kunde inte uppdatera användarens roll: " . $e->getMessage();
    }
}

// Fetch all users
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
    $stmt = $db->query("
        SELECT 
            u.id, 
            u.username, 
            u.email, 
            u.role, 
            u.created_at,
            (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count
        FROM users u 
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Kunde inte hämta användare: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Hantera användare - Cryptotracker</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    
    <div class="container">
        <div class="white-container">
            <h1>Hantera användare</h1>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Användarnamn</th>
                            <th>Email</th>
                            <th>Roll</th>
                            <th>Skapad</th>
                            <th>Inlägg</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Inga användare hittades</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($user['created_at'])) ?></td>
                                    <td><?= $user['post_count'] ?></td>
                                    <td class="actions">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="new_role" value="admin">
                                                <button type="submit" name="change_role" class="admin-btn small">Gör admin</button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Är du säker på att du vill ta bort denna användare och alla deras inlägg?');">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="delete_user" class="delete-btn small">Ta bort</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../../php/footer.php'; ?>
</body>
</html> 