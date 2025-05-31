<?php
session_start();

// Cache-tid i sekunder
$cache_time = 300; // 5 minuter

function fetchMarketData($symbol) {
    // CryptoCompare API endpoint för historisk data
    $api_url = "https://min-api.cryptocompare.com/data/v2/histoday?fsym={$symbol}&tsym=USD&limit=30";
    
    // Initiera cURL
    $curl = curl_init($api_url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code === 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}

// Kontrollera om vi har cachad data
if (!isset($_SESSION['market_data']) || (time() - $_SESSION['market_cache_time']) > $cache_time) {
    $symbols = ['BTC', 'ETH', 'XRP', 'DOGE', 'ADA']; // Lägg till fler symboler vid behov
    $market_data = [];
    
    foreach ($symbols as $symbol) {
        $data = fetchMarketData($symbol);
        if ($data && isset($data['Data']['Data'])) {
            $market_data[$symbol] = $data['Data']['Data'];
        }
    }
    
    $_SESSION['market_data'] = $market_data;
    $_SESSION['market_cache_time'] = time();
}

// Använd cachad data
$market_data = $_SESSION['market_data'];

// Om detta anropas via AJAX, returnera JSON
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($market_data);
    exit;
}
?> 