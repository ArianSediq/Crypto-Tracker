<?php
require_once '../php/session.php';
require_once '../config.php';
require_once '../api/fetch_crypto.php';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Sök Kryptovalutor - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- CSS -->
    <script src="../js/script.js?v=<?php echo time(); ?>" defer></script> <!-- JavaScript with cache busting -->
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
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="login-prompt">
                <p>För att skapa inlägg om kryptovalutor behöver du <a href="login.php">logga in</a> eller <a href="register.php">skapa ett konto</a>.</p>
            </div>
        <?php endif; ?>
        
        <div class="search-results" id="crypto-price"></div>
    </div>

    <?php include '../php/footer.php'; ?> <!-- Footer -->
    
    <?php if (!isset($_SESSION['user_id'])): ?>
    <script>
        // Add this to script.js or inline if user is not logged in
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('create-post-btn')) {
                e.preventDefault();
                alert('Du måste vara inloggad för att skapa inlägg. Vänligen logga in eller skapa ett konto.');
                window.location.href = 'login.php';
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

