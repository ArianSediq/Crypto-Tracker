<?php
session_start();
include '../config.php';
$db = $pdo;

// Om POST, försök logga in
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $query = $db->prepare("SELECT * FROM users WHERE username = :username");
    $query->bindParam(":username", $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($result && password_verify($password, $result["password"])) {
        $_SESSION["user_id"] = $result["id"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Felaktigt användarnamn eller lösenord!";
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Logga in - Kryptotracker</title>
  <!-- Absolut CSS-sökväg (eftersom denna fil ligger i /pages/) -->
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <?php include '../header.php'; ?>
  
  <div class="container">
      <h2>Logga in</h2>
      
      <?php
      // Visa eventuellt felmeddelande
      if (isset($error)) {
          echo "<p style='color: red;'>$error</p>";
      }
      ?>
      
      <form method="post" action="login.php">
          <input type="text" name="username" placeholder="Användarnamn" required>
          <input type="password" name="password" placeholder="Lösenord" required>
          <button type="submit">Logga in</button>
      </form>
  </div>
  
  <?php include '../php/footer.php'; ?>
</body>
</html>
