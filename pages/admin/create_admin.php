<?php
require_once '../../php/session.php';
require_once '../../config.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /Crypto-Tracker/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Användarnamn krävs";
    }
    if (empty($email)) {
        $errors[] = "E-post krävs";
    }
    if (empty($password)) {
        $errors[] = "Lösenord krävs";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Lösenorden matchar inte";
    }
    
    if (empty($errors)) {
        try {
            $db = new PDO('sqlite:' . __DIR__ . '/../../db/crypto_tracker.db');
            
            // Check if username exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Användarnamnet är redan taget";
            } else {
                // Create new admin user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'admin', datetime('now'))");
                $stmt->execute([$username, $email, $hashed_password]);
                $success_message = "Administratörskontot har skapats framgångsrikt!";
            }
        } catch(PDOException $e) {
            $errors[] = "Ett fel uppstod vid skapandet av kontot";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Skapa administratör - Cryptotracker</title>
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <?php include '../../header.php'; ?>
    
    <div class="container">
        <div class="white-container">
            <h1>Skapa ny administratör</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="username">Användarnamn:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-post:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Lösenord:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Bekräfta lösenord:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="admin-btn">Skapa administratör</button>
            </form>
        </div>
    </div>
    
    <?php include '../../php/footer.php'; ?>
</body>
</html> 