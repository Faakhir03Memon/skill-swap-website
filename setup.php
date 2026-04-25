<?php
require_once 'includes/db.php';

echo "<h2>Database Setup & Migration</h2>";

try {
    // 1. Add new columns to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS subscription_plan INT DEFAULT 0");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS lecture_limit INT DEFAULT 0");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_approved TINYINT(1) DEFAULT 0");
    echo "<p style='color: green;'>✓ Users table updated with subscription fields.</p>";

    // 2. Insert Test User (check@gmail.com / 1234568)
    $email = 'check@gmail.com';
    $password = password_hash('1234568', PASSWORD_DEFAULT);
    $name = 'Test User';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_approved) VALUES (?, ?, ?, 'student', 1)");
        $stmt->execute([$name, $email, $password]);
        echo "<p style='color: green;'>✓ Test user (check@gmail.com) created with password 1234568.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Test user (check@gmail.com) already exists.</p>";
    }

    // 3. Ensure Admin exists and is approved
    $admin_email = 'skill@admin.com';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    if (!$stmt->fetch()) {
        $admin_pass = password_hash('skill@admin.com', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_approved) VALUES ('Admin', ?, ?, 'admin', 1)");
        $stmt->execute([$admin_email, $admin_pass]);
        echo "<p style='color: green;'>✓ Admin user (skill@admin.com) created.</p>";
    } else {
        $pdo->exec("UPDATE users SET is_approved = 1 WHERE email = '$admin_email'");
        echo "<p style='color: blue;'>ℹ Admin user updated to be approved.</p>";
    }

    echo "<h3>Setup Complete!</h3>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
