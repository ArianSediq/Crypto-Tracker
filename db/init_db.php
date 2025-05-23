<?php
include '../config.php';

// Öppna databasen
$db = new SQLite3(DB_FILE);

// Börja med att sätta en busy timeout på 5000 ms (5 sekunder)
$db->busyTimeout(5000);

// Fortsätt med att skapa tabellerna
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS portfolio (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    crypto_symbol TEXT NOT NULL,
    amount REAL NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

// Om du har andra tabeller, t.ex. reviews:
$db->exec("CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    crypto_symbol TEXT,
    review TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

echo "Databasen och tabellerna har skapats!";
?>


