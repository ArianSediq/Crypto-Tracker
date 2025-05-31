<?php
// Skapa koppling till SQLite-databasen
try {
    $db_path = __DIR__ . '/crypto_tracker.db';
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Läs in och kör SQL-schemat
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $db->exec($sql);
    
    // Skapa en administratör om det inte finns någon
    $check_admin = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    
    if ($check_admin == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $admin_password, 'admin@cryptotracker.com', 'admin']);
    }
    
    echo "✅ Databasen har initierats framgångsrikt!\n";
    echo "Standard admin-konto:\n";
    echo "Användarnamn: admin\n";
    echo "Lösenord: admin123\n";
    echo "Var god ändra lösenordet efter första inloggningen.\n";
    
} catch(PDOException $e) {
    die("❌ Databasfel: " . $e->getMessage());
}


