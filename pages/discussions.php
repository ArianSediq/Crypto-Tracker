<?php
require_once '../php/session.php';
require_once '../config.php';
require_once '../api/fetch_crypto.php';

// Sort the cryptocurrency data alphabetically by name
usort($data, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hämta alla inlägg med användarnamn
    $stmt = $db->query("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Databasfel: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Diskussioner - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container">
        
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Formulär för att skapa nytt inlägg -->
            <div class="create-post">
            <h1>Kryptodiskussioner</h1>
                <h2>Skapa nytt inlägg</h2>
                <form action="../api/create_post.php" method="POST" enctype="multipart/form-data">
                    <select name="crypto_symbol" required>
                        <?php foreach ($data as $coin): ?>
                            <option value="<?= htmlspecialchars($coin['symbol']) ?>">
                                <?= htmlspecialchars($coin['name']) ?> (<?= htmlspecialchars($coin['symbol']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" name="title" placeholder="Titel på inlägget" required>
                    
                    <textarea name="content" placeholder="Skriv din analys eller diskussion här..." required></textarea>
                    
                    <div class="file-upload">
                        <label for="image">Bild (valfritt):</label>
                        <input type="file" name="image" id="image" accept="image/*">
                    </div>
                    
                    <button type="submit">Publicera</button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Filter och sökfunktioner -->
        <div class="filter-section">
            <form action="" method="GET" class="filter-form">
                <select name="crypto" onchange="this.form.submit()">
                    <option value="">Alla kryptovalutor</option>
                    <?php foreach ($data as $coin): ?>
                        <option value="<?= htmlspecialchars($coin['symbol']) ?>" 
                                <?= isset($_GET['crypto']) && $_GET['crypto'] === $coin['symbol'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($coin['name']) ?> (<?= htmlspecialchars($coin['symbol']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="search" placeholder="Sök i inlägg..." 
                       value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                
                <button type="submit">Sök</button>
            </form>
        </div>
        
        <!-- Lista med inlägg -->
        <div class="posts-grid">
            <?php 
            foreach ($posts as $post):
                // Filtrera baserat på kryptovaluta
                if (isset($_GET['crypto']) && !empty($_GET['crypto']) && $post['crypto_symbol'] !== $_GET['crypto']) {
                    continue;
                }
                
                // Filtrera baserat på sökterm
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = strtolower($_GET['search']);
                    if (strpos(strtolower($post['title']), $search) === false && 
                        strpos(strtolower($post['content']), $search) === false) {
                        continue;
                    }
                }
            ?>
                <div class="post-card">
                    <div class="post-header">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <span class="post-meta">
                            av <?= htmlspecialchars($post['username']) ?> | 
                            <?= htmlspecialchars($post['crypto_symbol']) ?> | 
                            <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?>
                        </span>
                    </div>
                    
                    <div class="post-content">
                        <?php 
                        // Visa bara en del av innehållet först
                        $content = htmlspecialchars($post['content']);
                        if (strlen($content) > 200) {
                            echo substr($content, 0, 200) . '...';
                        } else {
                            echo $content;
                        }
                        ?>
                    </div>
                    
                    <?php if ($post['image_url']): ?>
                        <div class="post-image">
                            <img src="<?= htmlspecialchars('../' . $post['image_url']) ?>" alt="Inläggsbild">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-actions">
                        <button onclick="viewPost(<?= $post['id'] ?>)" class="view-btn">Läs mer</button>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <form action="../api/delete_post.php" method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="delete-btn">Ta bort</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
    
    <!-- Modal för att visa hela inlägg -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="postContent"></div>
        </div>
    </div>
    
    <script>
    function viewPost(postId) {
        fetch(`../api/get_post.php?id=${postId}`)
            .then(response => response.json())
            .then(data => {
                const modal = document.getElementById('postModal');
                const content = document.getElementById('postContent');
                content.innerHTML = `
                    <h2>${data.title}</h2>
                    <p class="post-meta">
                        av ${data.username} | 
                        ${data.crypto_symbol} | 
                        ${new Date(data.created_at).toLocaleString('sv-SE')}
                    </p>
                    <div class="post-content">${data.content}</div>
                    ${data.image_url ? `<div class="post-image">
                        <img src="../${data.image_url}" alt="Inläggsbild">
                    </div>` : ''}
                `;
                modal.style.display = "block";
                document.body.classList.add('modal-open');
            });
    }
    
    // Stäng modal när man klickar på X
    document.querySelector('.close').onclick = function() {
        document.getElementById('postModal').style.display = "none";
        document.body.classList.remove('modal-open');
    }
    
    // Stäng modal när man klickar utanför
    window.onclick = function(event) {
        const modal = document.getElementById('postModal');
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.classList.remove('modal-open');
        }
    }
    </script>
    
    <?php include '../php/footer.php'; ?>
</body>
</html> 