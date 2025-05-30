<?php
// Starta sessionen om den inte redan är igång
session_start();

// Ta bort alla sessionsvariabler
session_unset();

// Förstör sessionen
session_destroy();

// Skicka användaren till startsidan
header("Location: ../index.php");
exit;
?>

