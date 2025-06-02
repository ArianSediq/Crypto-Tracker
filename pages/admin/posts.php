<?php
require_once '../../php/session.php';
require_once '../../config.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Crypto-Tracker/');
    exit;
}

// Handle post deletion
if (isset($_POST['delete_post'])) {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
        $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$_POST['post_id']]);
        $success_message = "Inlägget har tagits bort";
    } catch(PDOException $e) {
        $error_message = "Kunde inte ta bort inlägget: " . $e->getMessage();
    }
}

// Fetch all posts with user information
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
    $stmt = $db->query("
        SELECT 
            p.*, 
            u.username
        FROM posts p
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Kunde inte hämta inlägg: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Hantera inlägg - Cryptotracker</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    
    <div class="container">
        <div class="white-container">
            <h1>Hantera inlägg</h1>
            
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
                            <th>Titel</th>
                            <th>Författare</th>
                            <th>Skapad</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">Inga inlägg hittades</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= htmlspecialchars($post['title']) ?></td>
                                    <td><?= htmlspecialchars($post['username']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></td>
                                    <td class="actions">
                                        <a href="../discussions.php?post_id=<?= $post['id'] ?>" class="view-btn small">Visa</a>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Är du säker på att du vill ta bort detta inlägg?');">
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" name="delete_post" class="delete-btn small">Ta bort</button>
                                        </form>
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