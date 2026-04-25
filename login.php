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
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: student/dashboard.php');
        }
        exit;
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
    <div class="glass" style="width: 100%; max-width: 400px; padding: 40px; animation: fadeIn 0.5s ease-out;">
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="index.php" class="logo" style="text-decoration: none;">SKILLSWAP</a>
            <p style="color: var(--text-muted); margin-top: 10px;">Welcome back! Please login.</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Login to Account</button>
        </form>

        <p style="text-align: center; margin-top: 25px; color: var(--text-muted); font-size: 14px;">
            Don't have an account? <a href="register.php" style="color: var(--primary); text-decoration: none;">Register</a>
        </p>
    </div>
</body>
</html>
