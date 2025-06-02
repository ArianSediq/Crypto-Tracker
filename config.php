<?php
/**
 * config.php
 * Konfiguration för Cryptotracker-projektet
 */

//Sökväg till SQLite-databasen (ligger i /db/)
define('DB_FILE', __DIR__ . '/db/crypto_tracker.db');

//API-URL:er
define('API_URL_COIN', 'https://api.coingecko.com/api/v3/');
define('API_URL_NEWS', 'https://cryptonews-api.com/');

//PDO-databasanslutning (SQLite)
try {
    $pdo = new PDO("sqlite:" . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kunde inte ansluta till databasen: " . $e->getMessage());
}
?>