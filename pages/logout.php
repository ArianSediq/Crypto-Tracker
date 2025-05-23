<?php
// start
session_start();

// destroy
session_destroy();

// redirect
header("Location: login.php");
exit;
?>
