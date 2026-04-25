<?php
require_once 'includes/db.php';

echo "<h2>Database Setup & Migration</h2>";

try {
    // 1. Add new columns to users table (compatible with all MySQL versions)
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('subscription_plan', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN subscription_plan INT DEFAULT 0");
        echo "<p style='color: green;'>✓ Added 'subscription_plan' column.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ 'subscription_plan' column already exists.</p>";
    }

    if (!in_array('lecture_limit', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN lecture_limit INT DEFAULT 0");
        echo "<p style='color: green;'>✓ Added 'lecture_limit' column.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ 'lecture_limit' column already exists.</p>";
    }

    if (!in_array('is_approved', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
        echo "<p style='color: green;'>✓ Added 'is_approved' column.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ 'is_approved' column already exists.</p>";
    }

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

    // 3. Ensure Admin table exists and is populated
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✓ Admin table checked/created.</p>";

    // Remove old admin from users table if exists to avoid conflicts
    $pdo->exec("DELETE FROM users WHERE email = 'skill@admin.com'");

    $admin_email = 'skill@admin.com';
    $admin_pass = password_hash('skill@access.com', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
    $stmt->execute([$admin_email]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO admin (name, email, password) VALUES ('Admin', ?, ?)");
        $stmt->execute([$admin_email, $admin_pass]);
        echo "<p style='color: green;'>✓ Admin user (skill@admin.com) created in 'admin' table with password 'skill@access.com'.</p>";
    } else {
        $pdo->prepare("UPDATE admin SET password = ? WHERE email = ?")->execute([$admin_pass, $admin_email]);
        echo "<p style='color: blue;'>ℹ Admin user in 'admin' table updated with password 'skill@access.com'.</p>";
    }

    echo "<hr><h3 style='color: green;'>✅ Setup Complete!</h3>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "<p><a href='register.php'>Go to Register Page</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
