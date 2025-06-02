<?php
require_once __DIR__ . '/php/session.php';
?>
<header>
    <nav>
        <div class="logo">
            <a href="/Crypto-Tracker/">CryptoTracker</a>
        </div>
        <button class="mobile-menu-button" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-links">
            <li><a href="/Crypto-Tracker/" <?= $_SERVER['REQUEST_URI'] == '/Crypto-Tracker/' ? 'class="active"' : '' ?>>Hem</a></li>
            <li><a href="/Crypto-Tracker/pages/search.php" <?= strpos($_SERVER['REQUEST_URI'], '/search.php') ? 'class="active"' : '' ?>>Sök</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/Crypto-Tracker/pages/portfolio.php" <?= strpos($_SERVER['REQUEST_URI'], '/portfolio.php') ? 'class="active"' : '' ?>>Portfolio</a></li>
                <li><a href="/Crypto-Tracker/pages/discussions.php" <?= strpos($_SERVER['REQUEST_URI'], '/discussions.php') ? 'class="active"' : '' ?>>Diskussioner</a></li>
                <li><a href="/Crypto-Tracker/pages/profile.php" <?= strpos($_SERVER['REQUEST_URI'], '/profile.php') ? 'class="active"' : '' ?>>Min Profil</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="/Crypto-Tracker/pages/admin/dashboard.php" <?= strpos($_SERVER['REQUEST_URI'], '/admin/') ? 'class="active"' : '' ?>>Admin</a></li>
                <?php endif; ?>
                <li><a href="/Crypto-Tracker/pages/logout.php">Logga ut</a></li>
            <?php else: ?>
                <li><a href="/Crypto-Tracker/pages/login.php" <?= strpos($_SERVER['REQUEST_URI'], '/login.php') ? 'class="active"' : '' ?>>Logga in</a></li>
                <li><a href="/Crypto-Tracker/pages/register.php" <?= strpos($_SERVER['REQUEST_URI'], '/register.php') ? 'class="active"' : '' ?>>Registrera</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<script>
document.querySelector('.mobile-menu-button').addEventListener('click', function() {
    this.classList.toggle('active');
    document.querySelector('.nav-links').classList.toggle('active');
});
</script>