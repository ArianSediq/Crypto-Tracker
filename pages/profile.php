<?php
session_start();
require_once("../config.php");
require_once("../php/session.php");

$username = $_SESSION['username'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bio'])) {
    $bio = htmlspecialchars($_POST['bio']);

    $stmt = $pdo->prepare("UPDATE users SET bio = :bio WHERE username = :username");
    $stmt->execute([':bio' => $bio, ':username' => $username]);

    $message = "Biografi uppdaterad!";
}

$stmt = $pdo->prepare("SELECT bio FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

$bio = $user['bio'] ?? '';
?>

<?php include("../header.php"); ?>

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

<?php include("../php/footer.php"); ?>