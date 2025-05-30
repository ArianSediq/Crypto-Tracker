<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../php/session.php");
include("../layout.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// Hantera formulär
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bio'])) {
    $bio = htmlspecialchars($_POST['bio']);

    $stmt = $pdo->prepare("UPDATE users SET bio = :bio WHERE id = :id");
    $stmt->execute([':bio' => $bio, ':id' => $userId]);

    $message = "Biografi uppdaterad!";
}

$stmt = $pdo->prepare("SELECT username, bio FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

$username = $user['username'] ?? '';
$bio = $user['bio'] ?? '';
?>

<div class="container">
    <h2>Min profil</h2>
    <p><strong>Användarnamn:</strong> <?= htmlspecialchars($username) ?></p>

    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="bio">Biografi:</label><br>
        <textarea id="bio" name="bio" rows="4" cols="50"><?= htmlspecialchars($bio) ?></textarea><br><br>
        <button type="submit">Uppdatera</button>
    </form>
</div>

<?php include("../layout_end.php"); ?>
