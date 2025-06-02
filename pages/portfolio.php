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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crypto_symbol'])) {
    $crypto = trim($_POST["crypto_symbol"]);
    $amount = trim($_POST["amount"]);
    $purchase_price = trim($_POST["purchase_price"]);
    $user_id = $_SESSION["user_id"];
    
    try {
        $stmt = $db->prepare("INSERT INTO portfolio (user_id, crypto_symbol, amount, purchase_price) VALUES (:user_id, :crypto, :amount, :purchase_price)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':crypto', $crypto, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':purchase_price', $purchase_price, PDO::PARAM_STR);
        $stmt->execute();
        
        // Return JSON response for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Tillgång tillagd i portfolion.']);
            exit;
        }
        
        // Redirect for non-AJAX requests (fallback)
        header("Location: portfolio.php");
        exit;
    } catch(PDOException $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Databasfel: ' . $e->getMessage()]);
            exit;
        }
        // Handle non-AJAX error
        $_SESSION['error'] = 'Ett fel uppstod: ' . $e->getMessage();
        header("Location: portfolio.php");
        exit;
    }
}

// Ta bort innehav
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $portfolio_id = filter_input(INPUT_POST, 'portfolio_id', FILTER_VALIDATE_INT);
    
    try {
        // Verifiera att tillgången tillhör användaren
        $stmt = $db->prepare("SELECT crypto_symbol FROM portfolio WHERE id = ? AND user_id = ?");
        $stmt->execute([$portfolio_id, $_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Ta bort tillgången
            $stmt = $db->prepare("DELETE FROM portfolio WHERE id = ? AND user_id = ?");
            $stmt->execute([$portfolio_id, $_SESSION['user_id']]);
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $result['crypto_symbol'] . ' har tagits bort från din portfolio.']);
                exit;
            }
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['success' => false, 'error' => 'Du har inte behörighet att ta bort denna tillgång.']);
                exit;
            }
        }
    } catch(PDOException $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['success' => false, 'error' => 'Databasfel: ' . $e->getMessage()]);
            exit;
        }
    }
}

// Hämta innehav
$user_id = $_SESSION["user_id"];
$stmt = $db->prepare("SELECT id, crypto_symbol, amount, purchase_price FROM portfolio WHERE user_id = :user_id");
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
    <div id="success-message" class="success-message"></div>
    <div class="container">
        <!-- Lägg till ny tillgång -->
        <div class="add-asset-form">
            <h1>Min Kryptoportfolio</h1>
            <h2>Lägg till ny tillgång</h2>
            <form id="add-asset-form" method="POST">
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
                        <label for="purchase_price">Totalt köppris (USD)</label>
                        <input type="number" id="purchase_price" name="purchase_price" step="0.01" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="action-button">
                        <span class="button-icon">+</span>
                        <span class="button-text">Lägg till i portfolio</span>
                    </button>
                </div>
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
                            $total_purchase_value = isset($asset['purchase_price']) ? $asset['purchase_price'] : 0;
                            $profit_loss = $total_value - $total_purchase_value;
                            $profit_loss_percent = $total_purchase_value > 0 ? ($profit_loss / $total_purchase_value) * 100 : 0;
                            ?>
                            
                            <div class="asset-header">
                                <h3><?= htmlspecialchars($asset['crypto_symbol']) ?></h3>
                                <form class="delete-form" onsubmit="return deleteAsset(event, <?= $asset['id'] ?>, '<?= htmlspecialchars($asset['crypto_symbol']) ?>')">
                                    <input type="hidden" name="portfolio_id" value="<?= $asset['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
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
                                        <?= $profit_loss >= 0 ? '+' : '' ?>$<?= number_format($profit_loss, 2) ?>
                                        (<?= number_format($profit_loss_percent, 2) ?>%)
                                    </span>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Portfolio Historik -->
                <div class="portfolio-history">
                    <h2>Portfolio Historik</h2>
                    <div class="history-grid">
                        <?php foreach ($portfolioEntries as $asset): ?>
                            <?php
                            $current_price = 0;
                            $coin_name = '';
                            foreach ($data as $coin) {
                                if ($coin['symbol'] === $asset['crypto_symbol']) {
                                    $current_price = $coin['price_usd'];
                                    $coin_name = $coin['name'];
                                    break;
                                }
                            }
                            
                            // Calculate per-coin prices and profit/loss
                            $amount = floatval($asset['amount']);
                            $per_coin_purchase = isset($asset['purchase_price']) ? ($asset['purchase_price'] / $amount) : 0;
                            $total_current_value = $amount * $current_price;
                            $total_purchase_value = isset($asset['purchase_price']) ? $asset['purchase_price'] : 0;
                            $profit_loss = $total_current_value - $total_purchase_value;
                            $profit_loss_percentage = $total_purchase_value > 0 ? ($profit_loss / $total_purchase_value) * 100 : 0;
                            ?>
                            <div class="history-item">
                                <div class="history-container coin-info">
                                    <h3>Kryptovaluta</h3>
                                    <p><?= htmlspecialchars($coin_name) ?> (<?= htmlspecialchars($asset['crypto_symbol']) ?>)</p>
                                    <small>Antal: <?= number_format($amount, 8) ?></small>
                                </div>
                                <div class="history-container purchase-info">
                                    <h3>Köpt för</h3>
                                    <p>$<?= isset($asset['purchase_price']) ? number_format($asset['purchase_price'], 2) : '0.00' ?></p>
                                    <small>Per mynt: $<?= number_format($per_coin_purchase, 2) ?></small>
                                </div>
                                <div class="history-container current-info">
                                    <h3>Nuvarande pris</h3>
                                    <p>$<?= number_format($current_price, 2) ?></p>
                                    <small>Totalt värde: $<?= number_format($total_current_value, 2) ?></small>
                                </div>
                                <div class="history-container profit-loss-info <?= $profit_loss >= 0 ? 'profit' : 'loss' ?>">
                                    <h3>Vinst/Förlust</h3>
                                    <p><?= $profit_loss >= 0 ? '+' : '' ?>$<?= number_format($profit_loss, 2) ?></p>
                                    <small><?= number_format($profit_loss_percentage, 2) ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-portfolio">
                    <p>Din portfolio är tom. Börja med att lägga till några tillgångar!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.getElementById('add-asset-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('portfolio.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMessage = document.getElementById('success-message');
                const cryptoName = document.getElementById('crypto_symbol').options[document.getElementById('crypto_symbol').selectedIndex].text;
                successMessage.textContent = cryptoName + ' har lagts till i din portfolio!';
                successMessage.classList.add('show');
                
                // Reset form
                document.getElementById('add-asset-form').reset();
                
                // Hide message and reload after delay
                setTimeout(() => {
                    successMessage.classList.remove('show');
                    setTimeout(() => {
                        window.location.reload();
                    }, 300); // Wait for fade out animation
                }, 2000);
            } else {
                alert('Ett fel uppstod: ' + (data.error || 'Okänt fel'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ett fel uppstod när tillgången skulle läggas till.');
        });
    });
    
    function deleteAsset(event, portfolioId, symbol) {
        event.preventDefault();
        
        if (!confirm('Är du säker på att du vill ta bort ' + symbol + ' från din portfolio?')) {
            return false;
        }
        
        const formData = new FormData();
        formData.append('portfolio_id', portfolioId);
        formData.append('action', 'delete');
        
        fetch('portfolio.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMessage = document.getElementById('success-message');
                successMessage.textContent = data.message;
                successMessage.classList.add('show');
                
                // Hide message and reload after delay
                setTimeout(() => {
                    successMessage.classList.remove('show');
                    setTimeout(() => {
                        window.location.reload();
                    }, 300); // Wait for fade out animation
                }, 2000);
            } else {
                alert('Ett fel uppstod: ' + (data.error || 'Okänt fel'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ett fel uppstod när tillgången skulle tas bort.');
        });
        
        return false;
    }
    </script>
    
    <?php include '../php/footer.php'; ?>
</body>
</html>


