<?php
session_start();
include '../config.php';
// Use the PDO connection from config.php
$db = $pdo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST["username"]));
    $email = trim(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL));
    $password = $_POST["password"];

    // Server-side validation: Username
    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        die("❌ Ogiltigt användarnamn.");
    }

    // Server-side validation: Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("❌ Ogiltig e-postadress.");
    }

    // Server-side validation: Password
    if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
        die("❌ Ogiltigt lösenord. Måste vara minst 8 tecken långt och innehålla en siffra.");
    }

    // Prevent duplicate usernames
    $check_query = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
    $check_query->bindParam(":username", $username, PDO::PARAM_STR);
    $check_query->bindParam(":email", $email, PDO::PARAM_STR);
    $result = $check_query->execute();
    $count = $check_query->fetchColumn();

    if ($count > 0) {
        die("⚠ Användarnamnet eller e-postadressen är redan registrerad.");
    }

    // Secure password hash
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $query = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
    $query->bindParam(":username", $username, PDO::PARAM_STR);
    $query->bindParam(":email", $email, PDO::PARAM_STR);
    $query->bindParam(":password", $hashed_password, PDO::PARAM_STR);

    if ($query->execute()) {
        $_SESSION['register_success'] = true;
        $_SESSION['new_username'] = $username;
        header("Location: login.php");
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
        <h6>Registrera konto</h6>
        <form id="register-form" method="post">
            <input type="text" id="username" name="username" placeholder="Användarnamn" required>
            <input type="email" id="email" name="email" placeholder="E-postadress" required>
            <input type="password" id="password" name="password" placeholder="Lösenord" required>
            <button type="submit">Registrera</button>
            <div id="error-message"></div> <!-- Error container for client-side validation -->
        </form>
    </div>
    
    <script src="../js/validate.js"></script>

    <?php include '../php/footer.php'; ?>
</body>

</html>