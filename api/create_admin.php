<?php
require_once '../config.php';
require_once '../php/session.php';

// Only allow this script to run from the command line or by existing admins
if (php_sapi_name() !== 'cli' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || php_sapi_name() === 'cli') {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get input data
        if (php_sapi_name() === 'cli') {
            // Command line input
            $username = readline("Enter username: ");
            $email = readline("Enter email: ");
            $password = readline("Enter password: ");
        } else {
            // Web input
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
        }

        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception('All fields are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if username or email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            throw new Exception('Username or email already exists');
        }

        // Create admin user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, 'admin', CURRENT_TIMESTAMP)
        ");
        
        $stmt->execute([$username, $email, $hashedPassword]);

        $message = "Admin user '$username' created successfully";
        
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
        }

    } catch (Exception $e) {
        if (php_sapi_name() === 'cli') {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
} 