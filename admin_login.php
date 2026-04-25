<?php
session_start();
require_once 'includes/db.php';

$error = '';

// Auto-setup admin table and user if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = 'skill@admin.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hashed_pass = password_hash('skill@access.com', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admin (name, email, password) VALUES ('Admin', 'skill@admin.com', ?)")->execute([$hashed_pass]);
    }
} catch (PDOException $e) {
    // Ignore if it fails due to permissions, but it shouldn't
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_name'] = $admin['name'];
        $_SESSION['user_role'] = $admin['role'];
        header('Location: admin/dashboard.php');
        exit;
    } else {
        $error = "Invalid admin email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="bg-blobs">
        <div class="blob blob-1" style="background: var(--warning);"></div>
        <div class="blob blob-2" style="background: var(--accent);"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="glass reveal active" style="width: 100%; max-width: 420px; padding: 48px; position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border: 1px solid rgba(245, 158, 11, 0.3);">
                <i class="fas fa-user-shield" style="font-size: 28px; color: var(--warning);"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Admin Portal</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Secure login for administrators only.</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(244, 63, 94, 0.1); color: var(--accent); padding: 14px; border-radius: 12px; margin-bottom: 24px; text-align: center; font-size: 14px; border: 1px solid rgba(244, 63, 94, 0.2);">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" class="form-control" placeholder="skill@admin.com" required value="skill@admin.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 14px; background: linear-gradient(135deg, var(--warning), #d97706);">
                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Secure Login
            </button>
        </form>

        <p style="text-align: center; margin-top: 32px; font-size: 13px;">
            <a href="login.php" style="color: var(--text-muted); text-decoration: none;"><i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Student Login</a>
        </p>
    </div>

    <script src="assets/js/animations.js"></script>
</body>
</html>
