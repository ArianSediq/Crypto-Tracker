<?php
try {
    $db = new PDO('sqlite:db/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $count = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    echo "Number of posts in database: " . $count;
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 