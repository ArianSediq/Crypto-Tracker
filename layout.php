<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Cryptotracker</title>
    <link rel="stylesheet" href="/new%20website/Crypto-Tracker/css/styles.css">
</head>
<body>

<?php include("header.php"); ?>

<main class="main-content">
