<?php
session_start(); // Ensure session is started for login tracking
include 'api/fetch_crypto.php'; // Load API logic
?>

<!DOCTYPE html>
<html lang="sv">

<head>
  <meta charset="UTF-8">
  <title>Cryptotracker</title>
  <!-- Länk: CSS -->
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'header.php'; ?> <!-- Header: Navigation -->

  <?php if (!isset($_SESSION["user_id"])) { ?>
    <div class="container">
      <h1>Välkommen till Cryptotracker</h1>
      <p>En plattform för att följa och hantera kryptovalutor.</p>
      <div class="cta-buttons">
        <a href="pages/register.php" class="btn">Skapa Konto</a>
        <a href="pages/login.php" class="btn">Logga In</a>
      </div>
    </div>
  <?php } else { ?>

    <!-- Crypto Prices Section -->
    <div class="crypto-container">
      <h3>Latest Crypto Prices</h3>
      <?php
      foreach ($data as $coin) {
        $logo_url = "https://static.coincap.io/assets/icons/" . strtolower($coin['symbol']) . "@2x.png";
        echo "<div class='crypto-item'>";
        echo "<img src='{$logo_url}' alt='{$coin['name']} Logo' class='crypto-logo-home'>";
        echo "<p><strong>{$coin['name']}:</strong> $" . number_format($coin['price_usd'], 2) . "</p>";
        echo "</div>";
      }
      ?>
    </div>

    <!-- User Welcome Section -->
    <div class="container">
      <h1>Välkommen till Cryptotracker</h1>
      <p>Du är nu inloggad!</p>
    </div>

  <?php } ?>

  <?php include 'php/footer.php'; ?> <!-- Footer -->
</body>

</html>