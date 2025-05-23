<?php
// Kontroll: Endast admin får komma åt denna sida
include '../php/session.php';
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Adminpanel - Kryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- CSS -->
</head>
<body>
    <?php include '../header.php'; ?> <!-- Header -->
    <div class="container">
        <h2>Adminpanel</h2>
        <p>Här kan administratören hantera användare och innehåll.</p>
        <ul>
            <li><a href="manage_users.php">Hantera användare</a></li>
            <li><a href="manage_content.php">Hantera innehåll</a></li>
        </ul>
    </div>
    <?php include '../php/footer.php'; ?> <!-- Footer -->
</body>
</html>
