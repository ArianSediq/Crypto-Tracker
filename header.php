<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- HEADER START -->
<header>
    <div class="logo">Cryptotracker</div>

    <nav>
        <ul>
            <!-- Länkar pekar alltid från root med fast sökväg -->
            <li><a href="/new%20website/Crypto-Tracker/index.php">Hem</a></li>
            <li><a href="/new%20website/Crypto-Tracker/pages/dashboard.php">Dashboard</a></li>
            <li><a href="/new%20website/Crypto-Tracker/pages/portfolio.php">Portfölj</a></li>
            <li><a href="/new%20website/Crypto-Tracker/pages/search.php">Sök</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/new%20website/Crypto-Tracker/pages/profile.php">Min profil</a></li>
                <li><a href="/new%20website/Crypto-Tracker/pages/logout.php">Logga ut</a></li>
            <?php else: ?>
                <li><a href="/new%20website/Crypto-Tracker/pages/login.php">Logga in</a></li>
                <li><a href="/new%20website/Crypto-Tracker/pages/register.php">Registrera</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<!-- HEADER SLUT -->