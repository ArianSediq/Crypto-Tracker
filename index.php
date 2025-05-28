<!DOCTYPE html>
<html lang="sv">

<head>
  <meta charset="UTF-8">
  <title>Cryptotracker</title>
  <!-- Länk: CSS -->
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <?php include 'header.php'; ?> <!-- header: nav -->

  <?php if (!isset($_SESSION["user_id"])) { ?>
    <div class="container">
      <h1>Välkommen till Cryptotracker</h1> <!-- rubrik -->
      <p>En plattform för att följa och hantera kryptovalutor.</p> <!-- beskrivning -->
      <div class="cta-buttons"> <!-- knappar -->
        <a href="pages/register.php" class="btn">Skapa Konto</a>
        <a href="pages/login.php" class="btn">Logga In</a>
      </div>
    </div>
    
    <?php } else { ?>
    <div class="container">
      <h1>Välkommen till Cryptotracker</h1> <!-- rubrik -->
      <p>Du är nu inloggad!</p>
      <?php } ?>
</div>


  <?php include 'php/footer.php'; ?> <!-- footer -->
</body>

</html>