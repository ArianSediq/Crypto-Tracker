<?php
// Inkludera config.php för att få DB_FILE-konstanten
include 'config.php';

// Öppna databasen med den definierade DB_FILE (LÄGG IN DENNA och ta bort kommentar)
//$db = new SQLite3(DB_FILE);

// Ange testanvändarens uppgifter:
$username = 'testuser';         // Byt ut åt önskat användarnamn
$password_plain = 'password';   // Byt ut åt önskat lösenord

// Generera ett säkert hashat lösenord med BCRYPT
$hashedPassword = password_hash($password_plain, PASSWORD_BCRYPT);

// Förbered SQL-fråga för att infoga användaren
$stmt = $db->prepare("INSERT INTO users (username, password, role, created_at) VALUES (:username, :password, 'user', datetime('now'))");
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

// Kör frågan
if ($stmt->execute()) {
    echo "Användare skapad: $username, lösenord: $password_plain";
} else {
    echo "Fel uppstod vid skapande av användare.";
}
?>
