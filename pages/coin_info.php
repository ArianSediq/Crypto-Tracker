<?php
// Inkludera session och konfiguration
include '../php/session.php';
include '../config.php';


// Get coin ID from URL
$coin_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($coin_id === 0) {
    die("❌ Ogiltig kryptovaluta ID!");
}

// Fetch data from CoinLore API
$api_url = "https://api.coinlore.net/api/ticker/?id=" . $coin_id;
$coin_data = json_decode(file_get_contents($api_url), true)[0];

if (!$coin_data) {
    die("❌ Kryptovalutan hittades inte!");
}
?>

<!DOCTYPE html>
<html lang="sv">

<head>
    <meta charset="UTF-8">
    <title><?php echo $coin_data['name']; ?> Info - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <?php include '../header.php'; ?>

    <div class="coin-info-container">
        <div class="coin-header">
            <div class="coin-info">
                <img src="https://static.coincap.io/assets/icons/<?php echo strtolower($coin_data['symbol']); ?>@2x.png"
                    alt="<?php echo $coin_data['name']; ?> Logo" class="crypto-logo">
                <h2><?php echo $coin_data['name']; ?> (<?php echo $coin_data['symbol']; ?>)</h2>
            </div>

            <!-- Create Post Button -->
            <a href="create_post.php?coin_id=<?php echo $coin_data['id']; ?>" class="create-post-btn">
                📝 Skapa inlägg
            </a>
        </div>

        <div class="coin-details">
            <p><strong>💰 Pris:</strong> $<?php echo number_format($coin_data['price_usd'], 2); ?></p>
            <p><strong>📈 Marknadsvärde:</strong> $<?php echo number_format($coin_data['market_cap_usd'], 0); ?></p>
            <p><strong>🔄 24h Volym:</strong> $<?php echo number_format($coin_data['volume24'], 0); ?></p>
            <p><strong>📊 Rank:</strong> <?php echo $coin_data['rank']; ?></p>
            <p><strong>📉 24h Förändring:</strong> <?php echo $coin_data['percent_change_24h']; ?>%</p>
        </div>
    </div>

    <?php include '../php/footer.php'; ?>
</body>

</html>