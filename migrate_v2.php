<?php
require_once 'includes/db.php';

try {
    // 1. Add new columns to users table
    $sql = "ALTER TABLE users 
            ADD COLUMN plan INT DEFAULT 0,
            ADD COLUMN payment_status ENUM('pending', 'approved') DEFAULT 'pending',
            ADD COLUMN lectures_per_day INT DEFAULT 0,
            ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'inactive'";
    $pdo->exec($sql);
    echo "Columns added successfully.\n";
} catch (Exception $e) {
    echo "Columns might already exist or error: " . $e->getMessage() . "\n";
}

try {
    // 2. Insert test user
    $email = 'check@gmail.com';
    $password = '1234568';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Delete if exists to avoid unique constraint error
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, payment_status, status, plan, lectures_per_day) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test User', $email, $hashedPassword, 'student', 'approved', 'active', 499, 7]);
    
    echo "Test user 'check@gmail.com' created successfully.\n";
} catch (Exception $e) {
    echo "Error creating test user: " . $e->getMessage() . "\n";
}
?>
