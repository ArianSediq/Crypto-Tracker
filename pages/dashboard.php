<?php
// Inkludera session och konfiguration
include '../php/session.php';
include '../config.php';

// Använd PDO-anslutningen från config.php
$db = $pdo;

// Hämta användardata
$user_id = $_SESSION['user_id'];
$userQuery = $db->prepare("SELECT * FROM users WHERE id = :user_id");
$userQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$userQuery->execute();
$userResult = $userQuery->fetch(PDO::FETCH_ASSOC);
$username = $userResult ? $userResult['username'] : 'Användare';

// Hämta portföljinnehav för den inloggade användaren
$portfolioQuery = $db->prepare("SELECT crypto_symbol, amount FROM portfolio WHERE user_id = :user_id");
$portfolioQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$portfolioQuery->execute();
$portfolioEntries = $portfolioQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cryptotracker</title>
    <!-- Använd korrekt sökväg, absolut eller relativ beroende på din mappstruktur -->
    <link rel="stylesheet" href="../css/styles.css"> 
</head>
<body>
    <?php include '../header.php'; ?> <!-- Header -->
    <div class="container">
        <!-- Gamla dashboard-informationen -->
        <h2>Dashboard</h2>
        <p>Välkommen till din dashboard! Här får du en översikt över dina senaste kryptoinnehav samt aktuella uppdateringar.</p>
        <nav class="page-nav">
            <a href="portfolio.php" class="btn">Portfölj</a>
            <a href="search.php" class="btn">Sök Kryptovalutor</a>
            <?php 
            // Om användaren är admin – visa admin-panel-länk
            if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') { 
                echo '<a href="admin.php" class="btn">Adminpanel</a>';
            }
            ?>
        </nav>
        
        <!-- Ny personaliserad hälsning -->
        <h3>Välkommen, <?php echo htmlspecialchars($username); ?>!</h3>
        
        <!-- Dashboard-section: Portföljöversikt -->
        <section class="dashboard-section">
            <h3>Din Portfölj</h3>
            <?php if (count($portfolioEntries) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Kryptovaluta</th>
                            <th>Mängd</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($portfolioEntries as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['crypto_symbol']); ?></td>
                                <td><?php echo htmlspecialchars($entry['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Du har inga innehav just nu. Besök <a href="portfolio.php">portföljsidan</a> för att lägga till innehav.</p>
            <?php endif; ?>
        </section>
        
        <!-- Dashboard-section: Senaste nyheter -->
        <section class="dashboard-section">
            <h3>Senaste Nyheter</h3>
            <p>Här visas de senaste kryptonyheterna. (API-integration planeras)</p>
            <ul>
                <li>Nyhet 1: Kryptomarknaden visar tecken på återhämtning.</li>
                <li>Nyhet 2: Stora investeringar syns i blockchain-sektorn.</li>
                <li>Nyhet 3: Experterna diskuterar framtidens digitala ekonomi.</li>
            </ul>
        </section>
    </div>
    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>

