<?php
$host = 'localhost';
$db   = 'skill_swap';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);

     // Auto-migrate: add missing columns to users table
     $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
     if (!in_array('subscription_plan', $cols)) {
         $pdo->exec("ALTER TABLE users ADD COLUMN subscription_plan INT DEFAULT 0");
     }
     if (!in_array('lecture_limit', $cols)) {
         $pdo->exec("ALTER TABLE users ADD COLUMN lecture_limit INT DEFAULT 0");
     }
     if (!in_array('is_approved', $cols)) {
         $pdo->exec("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
     }
     if (!in_array('payment_status', $cols)) {
         $pdo->exec("ALTER TABLE users ADD COLUMN payment_status ENUM('pending','paid') DEFAULT 'pending'");
     }
} catch (\PDOException $e) {
     // For development, we show the error. In production, we'd log it and show a generic message.
     // die($e->getMessage());
     error_log($e->getMessage());
}
?>
