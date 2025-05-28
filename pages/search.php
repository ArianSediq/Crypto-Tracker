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

    <div class="container">
        <h2>Sök Kryptovalutor</h2>
        <input type="text" id="crypto-symbol" placeholder="Ange kryptovaluta (ex. bitcoin)">
        <button id="search-button">Sök</button>
        <p id="crypto-price"></p>
    </div>

    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>

