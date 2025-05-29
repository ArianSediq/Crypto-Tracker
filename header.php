<?php
// Säkerställ att sessionen är igång
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- HEADER START -->
<header>
    <div class="logo">Cryptotracker</div>

    <nav>
        <ul>
            <!-- Länkar som alltid visas -->
            <li><a href="../index.php">Hem</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="portfolio.php">Portfölj</a></li>
            <li><a href="search.php">Sök</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Endast inloggad användare ser dessa -->
                <li><a href="profile.php">Min profil</a></li>
                <li><a href="logout.php">Logga ut</a></li>
            <?php else: ?>
                <!-- Gäller för besökare som inte är inloggade -->
                <li><a href="login.php">Logga in</a></li>
                <li><a href="register.php">Registrera</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<!-- HEADER SLUT -->
