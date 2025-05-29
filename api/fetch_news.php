<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your NewsData.io API key
$api_key = "pub_c61d3a7a99b7410a83adb461b2d98cdc";
$cache_lifetime = 600; // Cache expiration (10 minutes)

// Check if session cache exists and is still valid
if (isset($_SESSION["news_cache"]) && (time() - $_SESSION["news_timestamp"]) < $cache_lifetime) {
    header("Content-Type: application/json");
    echo json_encode($_SESSION["news_cache"]);
    exit();
}

// Define API URL correctly
$api_url = "https://newsdata.io/api/1/news?apikey=$api_key&q=cryptocurrency&language=en&size=7";





// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Execute API request
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if (!$response || $curl_error) {
    die(json_encode(["error" => "❌ cURL error: $curl_error"]));
}

// Decode JSON response
$news_data = json_decode($response, true);

usort($news_data["results"], function($a, $b) {
    return strtotime($b["pubDate"]) - strtotime($a["pubDate"]);
});

// Handle invalid API response
if (!$news_data || !isset($news_data["results"])) {
    die(json_encode(["error" => "❌ JSON decoding failed!"]));
}

// Store API response in session cache
$_SESSION["news_cache"] = $news_data["results"];
$_SESSION["news_timestamp"] = time();

// Return fresh news data
header("Content-Type: application/json");
echo json_encode($_SESSION["news_cache"]);
?>
