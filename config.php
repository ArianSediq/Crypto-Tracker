<?php
/**
 * config.php
 * Konfiguration för Cryptotracker-projektet
 */

// Definierar sökvägen till SQLite-databasen.
// __DIR__ refererar till mappen där config.php ligger, och vi antar att databasen ligger i mappen "db".
define('DB_FILE', __DIR__ . '/db/crypto_tracker.sqlite');

// API URL:er för eksternt data-hämtning:
// API_URL_COIN: Använder CoinGecko API för kryptodata.
define('API_URL_COIN', 'https://api.coingecko.com/api/v3/');

// API_URL_NEWS: Använder Crypto News API för kryptorelaterade nyheter.
define('API_URL_NEWS', 'https://cryptonews-api.com/');
?>

