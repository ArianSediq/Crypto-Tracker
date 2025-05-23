<?php
// Säkerställ att sessionen startar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- HEADER START -->
<header>
    <div class="logo">Cryptotracker</div> <!-- logotyp -->
    <nav>
        <ul>
            <li><a href="/Crypto-Tracker/index.php">Hem</a></li>
            <li><a href="/Crypto-Tracker/pages/dashboard.php">Dashboard</a></li>
            <li><a href="/Crypto-Tracker/pages/portfolio.php">Portfölj</a></li>
            <li><a href="/Crypto-Tracker/pages/search.php">Sök</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Visas enbart när användare är inloggad -->
                <li><a href="/Crypto-Tracker/pages/logout.php">Logga ut</a></li>
            <?php else: ?>
                
                <!-- Visas om ingen är inloggad -->
                <li><a href="/Crypto-Tracker/pages/login.php">Logga in</a></li>
                <li><a href="/Crypto-Tracker/pages/register.php">Registrera</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<!-- HEADER SLUT -->

