<?php
session_start();

// Kontrollera om användaren är admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hämta alla användare
    $users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Hämta alla posts
    $posts = $db->query("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Databasfel: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container">
        <h1>Admin Panel</h1>
        
        <!-- Användarhantering -->
        <section class="admin-section">
            <h2>Användarhantering</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Användarnamn</th>
                        <th>Email</th>
                        <th>Roll</th>
                        <th>Skapad</th>
                        <th>Åtgärder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form action="../api/admin_actions.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="delete-btn">Ta bort</button>
                                    </form>
                                    <form action="../api/admin_actions.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_role">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="role-btn">
                                            <?= $user['role'] === 'admin' ? 'Gör till användare' : 'Gör till admin' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Innehållsmoderering -->
        <section class="admin-section">
            <h2>Innehållsmoderering</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Användare</th>
                        <th>Krypto</th>
                        <th>Titel</th>
                        <th>Skapad</th>
                        <th>Åtgärder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['id']) ?></td>
                            <td><?= htmlspecialchars($post['username']) ?></td>
                            <td><?= htmlspecialchars($post['crypto_symbol']) ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                            <td>
                                <form action="../api/admin_actions.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="delete-btn">Ta bort</button>
                                </form>
                                <button onclick="viewPost(<?= $post['id'] ?>)" class="view-btn">Visa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Statistik -->
        <section class="admin-section">
            <h2>Statistik</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Totalt antal användare</h3>
                    <p><?= count($users) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Totalt antal inlägg</h3>
                    <p><?= count($posts) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Aktiva användare idag</h3>
                    <p>
                        <?php
                        $today = $db->query("
                            SELECT COUNT(DISTINCT user_id) as count 
                            FROM posts 
                            WHERE date(created_at) = date('now')
                        ")->fetchColumn();
                        echo $today;
                        ?>
                    </p>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Modal för att visa inlägg -->
    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="postContent"></div>
        </div>
    </div>
    
    <script>
    // Funktion för att visa inlägg i modal
    function viewPost(postId) {
        fetch(`../api/get_post.php?id=${postId}`)
            .then(response => response.json())
            .then(data => {
                const modal = document.getElementById('postModal');
                const content = document.getElementById('postContent');
                content.innerHTML = `
                    <h2>${data.title}</h2>
                    <p><strong>Användare:</strong> ${data.username}</p>
                    <p><strong>Krypto:</strong> ${data.crypto_symbol}</p>
                    <div class="post-content">${data.content}</div>
                    ${data.image_url ? `<img src="${data.image_url}" alt="Post image">` : ''}
                `;
                modal.style.display = "block";
            });
    }
    
    // Stäng modal när man klickar på X
    document.querySelector('.close').onclick = function() {
        document.getElementById('postModal').style.display = "none";
    }
    
    // Stäng modal när man klickar utanför
    window.onclick = function(event) {
        const modal = document.getElementById('postModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
    
    <?php include '../php/footer.php'; ?>
</body>
</html>
