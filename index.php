<?php
require_once 'php/session.php'; // Centralized session management
include 'api/fetch_crypto.php'; // Load API logic
?>

<!DOCTYPE html>
<html lang="sv">

<head>
  <meta charset="UTF-8">
  <title>Cryptotracker</title>
  <!-- Länk: CSS -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
  <?php include 'header.php'; ?> <!-- Header: Navigation -->

  <?php if (!isset($_SESSION["user_id"])) { ?>
    <div class="welcome-section">
      <h1>Välkommen till Cryptotracker</h1>
      <p>En plattform för att följa och hantera kryptovalutor.</p>
      <div class="cta-buttons">
        <a href="pages/register.php" class="btn">Skapa Konto</a>
        <a href="pages/login.php" class="btn">Logga In</a>
      </div>
    </div>
  <?php } else { ?>
    <div class="container">
      <div class="main-content-grid">
        <!-- Crypto Prices Section -->
        <div class="crypto-container">
          <h3>Senaste Kryptopriserna</h3>
          <div class="crypto-scroll-container">
            <button class="scroll-button scroll-left" onclick="scrollCrypto('left')">
              <i class="fas fa-chevron-left"></i>
            </button>
            <div class="crypto-items-grid">
              <?php
              foreach ($data as $coin) {
                $logo_url = "https://static.coincap.io/assets/icons/" . strtolower($coin['symbol']) . "@2x.png";
                echo "<div class='crypto-item'>";
                echo "<img src='{$logo_url}' alt='{$coin['name']} Logo' class='crypto-logo-home'>";
                echo "<div class='crypto-info'>";
                echo "<h4>{$coin['name']} ({$coin['symbol']})</h4>";
                echo "<p>$" . number_format($coin['price_usd'], 2) . "</p>";
                $change = $coin['percent_change_24h'];
                $changeClass = $change >= 0 ? 'positive-change' : 'negative-change';
                $changeIcon = $change >= 0 ? '↑' : '↓';
                echo "<p class='{$changeClass}'>{$changeIcon} " . number_format($change, 2) . "%</p>";
                echo "</div>";
                echo "</div>";
              }
              ?>
            </div>
            <button class="scroll-button scroll-right" onclick="scrollCrypto('right')">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- Market Overview Section -->
        <div class="news-section">
          <h2>Marknadsinformation</h2>
          <div class="market-overview">
            <?php
            $total_market_cap = array_sum(array_column($data, 'market_cap_usd'));
            $total_volume = array_sum(array_column($data, 'volume24'));
            echo "<div class='market-stat'>";
            echo "<h4>Total Marknadsvärde</h4>";
            echo "<p>$" . number_format($total_market_cap, 0) . "</p>";
            echo "</div>";
            echo "<div class='market-stat'>";
            echo "<h4>24h Handelsvolym</h4>";
            echo "<p>$" . number_format($total_volume, 0) . "</p>";
            echo "</div>";
            ?>
          </div>
        </div>

        <!-- News Section -->
        <div class="news-section">
          <h2>Senaste Kryptonyheterna</h2>
          <div id="crypto-news">
            <div class="loading-spinner">
              <p>🔄 Laddar senaste kryptonyheter...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
    function scrollCrypto(direction) {
      const container = document.querySelector('.crypto-items-grid');
      const scrollAmount = 300;
      if (direction === 'left') {
        container.scrollBy({
          left: -scrollAmount,
          behavior: 'smooth'
        });
      } else {
        container.scrollBy({
          left: scrollAmount,
          behavior: 'smooth'
        });
      }
    }
    </script>
    <script src="js/news.js"></script>
  <?php } ?>

  <?php include 'php/footer.php'; ?> <!-- Footer -->
</body>

</html>