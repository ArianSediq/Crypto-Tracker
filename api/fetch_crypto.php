<?php
session_start(); // Start session for caching

// Cache duration (in seconds)
$cache_time = 120;

if (!isset($_SESSION['crypto_data']) || (time() - $_SESSION['cache_time']) > $cache_time) {
    $api_url = "https://api.coinlore.net/api/tickers/?limit=100";

    // Initialize cURL session
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Check for successful request
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);

        // Validate JSON structure
        if ($data !== null && isset($data['data'])) {
            $_SESSION['crypto_data'] = $data['data']; // Store only 'data' array
            $_SESSION['cache_time'] = time();
        } else {
            echo "⚠ JSON decoding failed.";
            $_SESSION['crypto_data'] = [];
        }
    } else {
        echo "⚠ API Request Failed with HTTP Code: $http_code";
        $_SESSION['crypto_data'] = [];
    }
}

// Use cached data
$data = $_SESSION['crypto_data'];
?>

