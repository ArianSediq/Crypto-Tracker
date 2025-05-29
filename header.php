<?php if (isset($_SESSION['user_id'])): ?>

    <!-- Visas enbart när användare är inloggad -->
    <li><a href="/Crypto-Tracker/pages/profile.php">Min profil</a></li>
    <li><a href="/Crypto-Tracker/pages/logout.php">Logga ut</a></li>

<?php else: ?>

    <!-- Visas om ingen är inloggad -->
    <li><a href="/Crypto-Tracker/pages/login.php">Logga in</a></li>
    <li><a href="/Crypto-Tracker/pages/register.php">Registrera</a></li>

<?php endif; ?>
