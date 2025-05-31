<?php
// Initiera session & konfiguration
include '../php/session.php';
include '../config.php';

$db = new SQLite3(DB_FILE);

// Lägg till nytt innehav
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add'])) {
    $crypto = trim($_POST["crypto_symbol"]);
    $amount = trim($_POST["amount"]);
    $user_id = $_SESSION["user_id"];
    
    $stmt = $db->prepare("INSERT INTO portfolio (user_id, crypto_symbol, amount) VALUES (:user_id, :crypto, :amount)");
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':crypto', $crypto, SQLITE3_TEXT);
    $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
    $stmt->execute();
}

// Ta bort innehav
if (isset($_GET["delete"])) {
    $delete_id = (int)$_GET["delete"];
    $stmt = $db->prepare("DELETE FROM portfolio WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $delete_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION["user_id"], SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: portfolio.php");
    exit;
}

// Hämta innehav
$user_id = $_SESSION["user_id"];
$stmt = $db->prepare("SELECT id, crypto_symbol, amount FROM portfolio WHERE user_id = :user_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$portfolioEntries = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $portfolioEntries[] = $row;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Min Portfolio - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- CSS -->
</head>
<body>
    <?php include '../header.php'; ?> <!-- Header -->
    <div class="container">
        <h1>Min Kryptoportfolio</h1>
        
        <!-- Lägg till ny tillgång -->
        <div class="add-asset-form">
            <h2>Lägg till ny tillgång</h2>
            <form action="../api/add_to_portfolio.php" method="POST">
                <select name="crypto_symbol" required>
                    <?php foreach ($data as $coin): ?>
                        <option value="<?= htmlspecialchars($coin['symbol']) ?>">
                            <?= htmlspecialchars($coin['name']) ?> (<?= htmlspecialchars($coin['symbol']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="amount" step="0.00000001" placeholder="Antal" required>
                <input type="number" name="purchase_price" step="0.01" placeholder="Köppris (USD)" required>
                <button type="submit">Lägg till</button>
            </form>
        </div>
        
        <!-- Visa portfolio -->
        <div class="portfolio-overview">
            <h2>Mina Tillgångar</h2>
            <?php if (!empty($portfolioEntries)): ?>
                <div class="portfolio-grid">
                    <?php foreach ($portfolioEntries as $asset): ?>
                        <div class="portfolio-item">
                            <?php
                            // Hitta aktuellt pris från API-data
                            $current_price = 0;
                            foreach ($data as $coin) {
                                if ($coin['symbol'] === $asset['crypto_symbol']) {
                                    $current_price = $coin['price_usd'];
                                    break;
                                }
                            }
                            
                            $total_value = $asset['amount'] * $current_price;
                            $profit_loss = $total_value - ($asset['amount'] * $asset['purchase_price']);
                            $profit_loss_percent = ($profit_loss / ($asset['amount'] * $asset['purchase_price'])) * 100;
                            ?>
                            
                            <h3><?= htmlspecialchars($asset['crypto_symbol']) ?></h3>
                            <p>Antal: <?= number_format($asset['amount'], 8) ?></p>
                            <p>Köppris: $<?= number_format($asset['purchase_price'], 2) ?></p>
                            <p>Nuvarande värde: $<?= number_format($total_value, 2) ?></p>
                            <p class="<?= $profit_loss >= 0 ? 'profit' : 'loss' ?>">
                                <?= $profit_loss >= 0 ? '+' : '' ?><?= number_format($profit_loss, 2) ?> USD
                                (<?= number_format($profit_loss_percent, 2) ?>%)
                            </p>
                            <form action="../api/remove_from_portfolio.php" method="POST" class="delete-form">
                                <input type="hidden" name="portfolio_id" value="<?= $asset['id'] ?>">
                                <button type="submit" class="delete-btn">Ta bort</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Portfolio värde över tid (chart) -->
                <div class="portfolio-chart">
                    <h2>Portfoliovärde över tid</h2>
                    <canvas id="portfolioChart"></canvas>
                </div>
            <?php else: ?>
                <p>Din portfolio är tom. Börja med att lägga till några tillgångar!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Enkel chart för att visa portfoliovärde
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
    
    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>


