<?php
require_once '../../php/session.php';
require_once '../../config.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Crypto-Tracker/');
    exit;
}

// Create a new database connection for fresh data
try {
    $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
    // Enable error reporting
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kunde inte ansluta till databasen: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Cryptotracker</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    
    <div class="container">
        <div class="white-container">
            <h1>Admin Dashboard</h1>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <h3>Användare</h3>
                    <?php
                    try {
                        $stmt = $db->query("SELECT COUNT(*) FROM users");
                        $userCount = $stmt->fetchColumn();
                        echo "<p>$userCount totalt</p>";
                    } catch(PDOException $e) {
                        echo "<p>Kunde inte hämta data: " . $e->getMessage() . "</p>";
                    }
                    ?>
                </div>
                
                <div class="stat-card">
                    <h3>Inlägg</h3>
                    <?php
                    try {
                        $stmt = $db->query("SELECT COUNT(*) FROM posts");
                        $postCount = $stmt->fetchColumn();
                        echo "<p>$postCount totalt</p>";
                    } catch(PDOException $e) {
                        echo "<p>Kunde inte hämta data: " . $e->getMessage() . "</p>";
                    }
                    ?>
                </div>
            </div>

            <div class="admin-actions">
                <h2>Administratörsåtgärder</h2>
                <div class="action-buttons">
                    <a href="users.php" class="admin-btn">Hantera användare</a>
                    <a href="posts.php" class="admin-btn">Hantera inlägg</a>
                    <a href="create_admin.php" class="admin-btn">Skapa ny admin</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../php/footer.php'; ?>
</body>
</html> 