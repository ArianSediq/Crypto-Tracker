<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $db = new PDO('sqlite:' . __DIR__ . '/../db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userId = $_SESSION['user_id'];
    $message = "";
    $error = "";
    $user = null;

    // Hantera formulär för profiluppdatering
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['bio'])) {
            $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
            
            $stmt = $db->prepare("UPDATE users SET bio = ? WHERE id = ?");
            $stmt->execute([$bio, $userId]);
            
            $message = "Din profil har uppdaterats!";
        }

        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file = $_FILES['profile_image'];
            
            if (!in_array($file['type'], $allowed_types)) {
                $error = "Endast JPG, PNG och GIF-bilder är tillåtna.";
            } else {
                $upload_dir = __DIR__ . '/../uploads/profile_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'profile_' . $userId . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old profile image if exists
                    $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $old_image = $stmt->fetchColumn();
                    
                    if ($old_image && file_exists(__DIR__ . '/../' . $old_image)) {
                        unlink(__DIR__ . '/../' . $old_image);
                    }
                    
                    // Update database with new image path
                    $image_path = 'uploads/profile_images/' . $new_filename;
                    $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$image_path, $userId]);
                    
                    $message = "Profilbild har uppdaterats!";
                } else {
                    $error = "Ett fel uppstod vid uppladdning av bilden.";
                }
            }
        }
    }

    // Hämta användarinformation
    $stmt = $db->prepare("SELECT username, email, bio, created_at, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Kunde inte hitta användarinformation.');
    }

} catch(PDOException $e) {
    $error = "Databasfel: " . $e->getMessage();
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Min Profil - Cryptotracker</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php include '../header.php'; ?>
    
    <div class="container">
        
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-info">
                        <h1>Min Profil</h1>
                        <h2><?= htmlspecialchars($user['username']) ?></h2>
                    </div>
                    <div class="profile-image-container">
                        <img src="<?= $user['profile_image'] ? '../' . htmlspecialchars($user['profile_image']) : '../images/default-profile.png' ?>" 
                             alt="" class="profile-image">
                        <form method="POST" enctype="multipart/form-data" class="profile-image-form">
                            <label for="profile_image" class="upload-label">
                                <span>Ändra bild</span>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*" onchange="this.form.submit()" style="display: none;">
                            </label>
                        </form>
                    </div>
                </div>
                
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Medlem sedan:</strong> <?= date('Y-m-d', strtotime($user['created_at'])) ?></p>
                
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="bio">Om mig:</label>
                        <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Uppdatera profil</button>
                </form>
            </div>

            <!-- Portfolio översikt -->
            <div class="dashboard-section">
                <h2>Min Portfolio Översikt</h2>
                <?php
                try {
                    $stmt = $db->prepare("
                        SELECT crypto_symbol, SUM(amount) as total_amount 
                        FROM portfolio 
                        WHERE user_id = ? 
                        GROUP BY crypto_symbol
                    ");
                    $stmt->execute([$userId]);
                    $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($portfolio) > 0): ?>
                        <div class="portfolio-summary">
                            <?php foreach ($portfolio as $holding): ?>
                                <div class="holding-card">
                                    <h3><?= htmlspecialchars($holding['crypto_symbol']) ?></h3>
                                    <p>Antal: <?= number_format($holding['total_amount'], 8) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Du har inga kryptovalutor i din portfolio än.</p>
                    <?php endif;
                } catch(PDOException $e) {
                    echo "<p class='error'>Kunde inte hämta portfolio: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>

            <!-- Senaste aktivitet -->
            <div class="dashboard-section">
                <h2>Senaste Aktivitet</h2>
                <?php
                try {
                    $stmt = $db->prepare("
                        SELECT p.*, u.username 
                        FROM posts p
                        JOIN users u ON p.user_id = u.id
                        WHERE p.user_id = ? 
                        ORDER BY p.created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$userId]);
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($posts) > 0): ?>
                        <div class="activity-list">
                            <?php foreach ($posts as $post): ?>
                                <div class="activity-item">
                                    <div class="activity-content">
                                        <p><?= htmlspecialchars($post['title']) ?></p>
                                        <small><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
                                    </div>
                                    <div class="activity-actions">
                                        <button onclick="viewPost(<?= $post['id'] ?>)" class="view-btn small">Visa</button>
                                        <form action="../api/delete_post.php" method="POST" style="display: inline;" onsubmit="return confirm('Är du säker på att du vill ta bort detta inlägg?');">
                                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                            <button type="submit" class="delete-btn small">Ta bort</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Du har inte skapat några inlägg än.</p>
                    <?php endif;
                } catch(PDOException $e) {
                    echo "<p class='error'>Kunde inte hämta aktivitet: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal för att visa inlägg -->
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
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Kunde inte ladda inlägget. Försök igen senare.');
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
