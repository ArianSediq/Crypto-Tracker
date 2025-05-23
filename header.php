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
            <li><a href="/Cryptotracker/index.php">Hem</a></li>
            <li><a href="/Cryptotracker/pages/dashboard.php">Dashboard</a></li>
            <li><a href="/Cryptotracker/pages/portfolio.php">Portfölj</a></li>
            <li><a href="/Cryptotracker/pages/search.php">Sök</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- Visas enbart när användare är inloggad -->
                <li><a href="/Cryptotracker/pages/logout.php">Logga ut</a></li>
            <?php else: ?>
                
                <!-- Visas om ingen är inloggad -->
                <li><a href="/Cryptotracker/pages/login.php">Logga in</a></li>
                <li><a href="/Cryptotracker/pages/register.php">Registrera</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<!-- HEADER SLUT -->

