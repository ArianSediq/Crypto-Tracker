<?php
session_start();
include '../config.php';
$db = new SQLite3(DB_FILE);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST["username"]));
    $password = $_POST["password"];

    // Server-side validation: Username
    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        die("❌ Ogiltigt användarnamn.");
    }

    // Server-side validation: Password
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
        die("❌ Ogiltigt lösenord. Måste vara minst 8 tecken långt och innehålla en siffra.");
    }

    // Prevent duplicate usernames
    $check_query = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $check_query->bindValue(":username", $username, SQLITE3_TEXT);
    $result = $check_query->execute()->fetchArray()[0];

    if ($result > 0) {
        die("⚠ Användarnamnet är redan taget. Välj ett annat!");
    }

    // Secure password hash
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $query = $db->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')");
    $query->bindValue(":username", $username, SQLITE3_TEXT);
    $query->bindValue(":password", $hashed_password, SQLITE3_TEXT);

    if ($query->execute()) {
        header("Location: login.php"); // Redirect on success
        exit();
    } else {
        die("⚠ Fel vid registrering, försök igen!");
    }
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
        <form id="register-form" method="post">
            <input type="text" id="username" name="username" placeholder="Användarnamn" required>
            <input type="password" id="password" name="password" placeholder="Lösenord" required>
            <button type="submit">Registrera</button>
            <div id="error-message"></div> <!-- Error container for client-side validation -->
        </form>


    </div>
    
    <script src="../js/validate.js"></script>

    <?php include '../php/footer.php'; ?>
</body>

</html>