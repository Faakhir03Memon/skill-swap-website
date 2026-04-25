<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            if ($stmt->execute([$name, $email, $hashed_pass])) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="glass" style="width: 100%; max-width: 450px; padding: 40px; animation: fadeIn 0.5s ease-out;">
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="index.php" class="logo" style="text-decoration: none;">SKILLSWAP</a>
            <p style="color: var(--text-muted); margin-top: 10px;">Create your account and start swapping.</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(244, 63, 94, 0.2); color: var(--accent); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background: rgba(16, 185, 129, 0.2); color: var(--success); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px;">
                <?php echo $success; ?>
                <br><a href="login.php" style="color: white; font-weight: bold;">Login now</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Create Account</button>
        </form>

        <p style="text-align: center; margin-top: 25px; color: var(--text-muted); font-size: 14px;">
            Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none;">Login</a>
        </p>
    </div>
</body>
</html>
