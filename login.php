<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== 'admin' && $user['is_approved'] == 0) {
            $error = "Your account is pending approval. Please wait for admin confirmation.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: student/dashboard.php');
            }
            exit;
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="glass reveal active" style="width: 100%; max-width: 420px; padding: 48px; position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="index.php" class="logo" style="text-decoration: none; font-size: 32px;">SKILLSWAP</a>
            <p style="color: var(--text-muted); margin-top: 12px; font-weight: 300;">Welcome back! Please login to your account.</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(244, 63, 94, 0.1); color: var(--accent); padding: 14px; border-radius: 12px; margin-bottom: 24px; text-align: center; font-size: 14px; border: 1px solid rgba(244, 63, 94, 0.2);">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@university.edu" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 14px;">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 32px; color: var(--text-muted); font-size: 14px;">
            Don't have an account yet? <a href="register.php" style="color: var(--primary-bright); text-decoration: none; font-weight: 600;">Create Account</a>
        </p>
    </div>

    <script src="assets/js/animations.js"></script>
</body>
</html>
