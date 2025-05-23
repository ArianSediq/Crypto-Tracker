<?php
// init
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// auth
if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php"); // omdirigera
    exit;
}
?>
