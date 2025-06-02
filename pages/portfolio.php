<?php
// Initiera session & konfiguration
include '../php/session.php';
include '../config.php';
require_once '../api/fetch_crypto.php';

// Sort the cryptocurrency data alphabetically by name
usort($data, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

$db = $pdo;

// Lägg till nytt innehav
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $crypto = trim($_POST["crypto_symbol"]);
    $amount = trim($_POST["amount"]);
    $user_id = $_SESSION["user_id"];
    
    $stmt = $db->prepare("INSERT INTO portfolio (user_id, crypto_symbol, amount) VALUES (:user_id, :crypto, :amount)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':crypto', $crypto, PDO::PARAM_STR);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->execute();
}

// Ta bort innehav
if (isset($_GET["delete"])) {
    $delete_id = (int)$_GET["delete"];
    $stmt = $db->prepare("DELETE FROM portfolio WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();
    header("Location: portfolio.php");
    exit;
}

// Hämta innehav
$user_id = $_SESSION["user_id"];
$stmt = $db->prepare("SELECT id, crypto_symbol, amount FROM portfolio WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$portfolioEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Min Portfolio - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="container">
        
        
        <!-- Lägg till ny tillgång -->
        <div class="add-asset-form">
            <h1>Min Kryptoportfolio</h1>
            <h2>Lägg till ny tillgång</h2>
            <form action="../api/add_to_portfolio.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="crypto_symbol">Kryptovaluta</label>
                        <select name="crypto_symbol" id="crypto_symbol" required>
                            <option value="" disabled selected>Välj kryptovaluta</option>
                            <?php foreach ($data as $coin): ?>
                                <option value="<?= htmlspecialchars($coin['symbol']) ?>">
                                    <?= htmlspecialchars($coin['name']) ?> (<?= htmlspecialchars($coin['symbol']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Antal</label>
                        <input type="number" id="amount" name="amount" step="0.00000001" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="purchase_price">Köppris (USD)</label>
                        <input type="number" id="purchase_price" name="purchase_price" step="0.01" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Lägg till i portfolio</button>
            </form>
        </div>
        
        <!-- Portfolio översikt -->
        <div class="portfolio-overview">
            <h2>Mina Tillgångar</h2>
            <?php if (!empty($portfolioEntries)): ?>
                <div class="portfolio-grid">
                    <?php foreach ($portfolioEntries as $asset): ?>
                        <div class="portfolio-item">
                            <?php
                            $current_price = 0;
                            foreach ($data as $coin) {
                                if ($coin['symbol'] === $asset['crypto_symbol']) {
                                    $current_price = $coin['price_usd'];
                                    break;
                                }
                            }
                            
                            $total_value = $asset['amount'] * $current_price;
                            $profit_loss = isset($asset['purchase_price']) ? 
                                         $total_value - ($asset['amount'] * $asset['purchase_price']) : 0;
                            $profit_loss_percent = isset($asset['purchase_price']) && $asset['purchase_price'] > 0 ? 
                                                 ($profit_loss / ($asset['amount'] * $asset['purchase_price'])) * 100 : 0;
                            ?>
                            
                            <div class="asset-header">
                                <h3><?= htmlspecialchars($asset['crypto_symbol']) ?></h3>
                                <form action="../api/remove_from_portfolio.php" method="POST" class="delete-form">
                                    <input type="hidden" name="portfolio_id" value="<?= $asset['id'] ?>">
                                    <button type="submit" class="delete-btn" title="Ta bort">×</button>
                                </form>
                            </div>
                            
                            <div class="asset-details">
                                <p>
                                    <span class="label">Antal:</span>
                                    <span class="value"><?= number_format($asset['amount'], 8) ?></span>
                                </p>
                                <?php if (isset($asset['purchase_price'])): ?>
                                <p>
                                    <span class="label">Köppris:</span>
                                    <span class="value">$<?= number_format($asset['purchase_price'], 2) ?></span>
                                </p>
                                <?php endif; ?>
                                <p>
                                    <span class="label">Nuvarande värde:</span>
                                    <span class="value">$<?= number_format($total_value, 2) ?></span>
                                </p>
                                <?php if (isset($asset['purchase_price'])): ?>
                                <p class="profit-loss <?= $profit_loss >= 0 ? 'profit' : 'loss' ?>">
                                    <span class="label">Vinst/Förlust:</span>
                                    <span class="value">
                                        <?= $profit_loss >= 0 ? '+' : '' ?><?= number_format($profit_loss, 2) ?> USD
                                        (<?= number_format($profit_loss_percent, 2) ?>%)
                                    </span>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Portfolio värde över tid -->
                <div class="portfolio-chart">
                    <h2>Portfoliovärde över tid</h2>
                    <canvas id="portfolioChart"></canvas>
                </div>
            <?php else: ?>
                <div class="empty-portfolio">
                    <p>Din portfolio är tom. Börja med att lägga till några tillgångar!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Portfolio chart
    const ctx = document.getElementById('portfolioChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array.from({length: 7}, (_, i) => {
                const d = new Date();
                d.setDate(d.getDate() - (6 - i));
                return d.toLocaleDateString('sv-SE');
            }),
            datasets: [{
                label: 'Portfoliovärde (USD)',
                data: [/* Här skulle historiska värden komma */],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
    
    <?php include '../php/footer.php'; ?>
</body>
</html>


