<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Sök Kryptovalutor - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- CSS -->
    <script src="../js/script.js" defer></script> <!-- JavaScript -->
</head>
<body>
    <?php include '../header.php'; ?> <!-- Header -->

    <div class="container search-container">
        <h2>Sök Kryptovalutor</h2>
        <div class="search-box">
            <input type="text" id="crypto-symbol" placeholder="Sök efter namn eller symbol (t.ex. Bitcoin eller BTC)">
            <button id="search-button">Sök</button>
        </div>
        <div class="search-status" id="search-status"></div>
        <div class="search-results" id="crypto-price"></div>
    </div>

    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>

