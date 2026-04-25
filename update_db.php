<?php
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL");
    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database or columns already exist: " . $e->getMessage();
}
?>
