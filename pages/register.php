<?php
session_start();
include '../config.php';
$db = new SQLite3(DB_FILE);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $query = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
    $query->bindValue(":username", $username, SQLITE3_TEXT);
    $query->bindValue(":password", $password, SQLITE3_TEXT);
    $query->execute();
    header("Location: login.php");
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="container">
        <h2>Registrera konto</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Användarnamn" required>
            <input type="password" name="password" placeholder="Lösenord" required>
            <button type="submit">Registrera</button>
        </form>
    </div>
</body>
</html>
