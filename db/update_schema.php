<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/crypto_tracker.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/add_bio_column.sql');
    $db->exec($sql);
    
    echo "✅ Database schema updated successfully!\n";
    
} catch(PDOException $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
} 