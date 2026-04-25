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
            $subscription_plan = $_POST['plan'];
            $lecture_limit = 0;
            if ($subscription_plan == 199) $lecture_limit = 3;
            elseif ($subscription_plan == 299) $lecture_limit = 5;
            elseif ($subscription_plan == 499) $lecture_limit = 7;

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, subscription_plan, lecture_limit, is_approved) VALUES (?, ?, ?, 'student', ?, ?, 0)");
            if ($stmt->execute([$name, $email, $hashed_pass, $subscription_plan, $lecture_limit])) {
                $success = "Registration successful! Your account is pending admin approval after payment.";
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
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="glass reveal active" style="width: 100%; max-width: 480px; padding: 48px; position: relative; z-index: 1;">
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="index.php" class="logo" style="text-decoration: none; font-size: 32px;">SKILLSWAP</a>
            <p style="color: var(--text-muted); margin-top: 12px; font-weight: 300;">Join the community and start swapping skills.</p>
        </div>

        <?php if($error): ?>
            <div style="background: rgba(244, 63, 94, 0.1); color: var(--accent); padding: 14px; border-radius: 12px; margin-bottom: 24px; text-align: center; font-size: 14px; border: 1px solid rgba(244, 63, 94, 0.2);">
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
            <div class="form-group" style="margin-top: 15px;">
                <label>Select Your Plan</label>
                <div style="display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 8px;">
                    <label class="plan-card" style="display: flex; justify-content: space-between; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer; transition: 0.3s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="plan" value="199" required>
                            <div>
                                <div style="font-weight: 600;">Basic Plan</div>
                                <div style="font-size: 12px; color: var(--text-muted);">3 Lectures / Day</div>
                            </div>
                        </div>
                        <div style="font-weight: bold; color: var(--primary-bright);">199rs</div>
                    </label>
                    <label class="plan-card" style="display: flex; justify-content: space-between; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer; transition: 0.3s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="plan" value="299">
                            <div>
                                <div style="font-weight: 600;">Standard Plan</div>
                                <div style="font-size: 12px; color: var(--text-muted);">5 Lectures / Day</div>
                            </div>
                        </div>
                        <div style="font-weight: bold; color: var(--primary-bright);">299rs</div>
                    </label>
                    <label class="plan-card" style="display: flex; justify-content: space-between; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; cursor: pointer; transition: 0.3s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="plan" value="499">
                            <div>
                                <div style="font-weight: 600;">Premium Plan</div>
                                <div style="font-size: 12px; color: var(--text-muted);">7 Lectures / Day</div>
                            </div>
                        </div>
                        <div style="font-weight: bold; color: var(--primary-bright);">499rs</div>
                    </label>
                </div>
            </div>
            <style>
                .plan-card:has(input:checked) {
                    border-color: var(--primary-bright) !important;
                    background: rgba(124, 58, 237, 0.1);
                }
            </style>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@university.edu" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 14px;">Create Account</button>
        </form>

        <p style="text-align: center; margin-top: 32px; color: var(--text-muted); font-size: 14px;">
            Already have an account? <a href="login.php" style="color: var(--primary-bright); text-decoration: none; font-weight: 600;">Sign In</a>
        </p>
    </div>

    <script src="assets/js/animations.js"></script>
</body>
</html>
