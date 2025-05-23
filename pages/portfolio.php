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
    <title>Min Portfölj - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- CSS -->
</head>
<body>
    <?php include '../header.php'; ?> <!-- Header -->
    <div class="container">
        <h2>Min Portfölj</h2>
        
        <!-- Formulär: Lägg till innehav -->
        <form method="POST" action="portfolio.php">
            <input type="text" name="crypto_symbol" placeholder="Kryptovaluta (ex. bitcoin)" required>
            <input type="number" step="0.0001" name="amount" placeholder="Mängd" required>
            <button type="submit" name="add">Lägg till</button>
        </form>
        
        <!-- Tabell: Visar innehav -->
        <table style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Krypto</th>
                    <th>Mängd</th>
                    <th>Åtgärd</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($portfolioEntries as $entry): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entry["id"]); ?></td>
                    <td><?php echo htmlspecialchars($entry["crypto_symbol"]); ?></td>
                    <td><?php echo htmlspecialchars($entry["amount"]); ?></td>
                    <td>
                        <a href="portfolio.php?delete=<?php echo $entry["id"]; ?>">Ta bort</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>


