<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/send_email.php';

// Silently ensure the columns exist so the token gets saved properly
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL");
    $pdo->exec("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL");
} catch (Exception $e) { /* Ignore if they already exist */ }

$message = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='student'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        
        $pdo->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE id=?")->execute([$token, $expiry, $user['id']]);
        
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        
        // Try to send via PHPMailer script
        if (sendResetEmail($email, $reset_link)) {
            $message = "A password reset link has been sent to your email.";
            $msg_type = 'success';
        } else {
            // Fallback for local testing
            $message = "<div style='width:100%; display:block; line-height:1.5'>
                          Failed to send email. <br>
                          <span style='font-size:12px; opacity:0.8'>Local Dev Link: </span>
                          <a href='$reset_link' style='color:#fff; text-decoration:underline; font-weight:bold'>Click Here</a>
                        </div>"; 
            $msg_type = 'warning';
        }
    } else {
        // Generic message for security
        $message = "If an account with that email exists, a password reset link has been sent.";
        $msg_type = 'info';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Forgot Password | SkillSwap</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:24px; }
.login-wrap { width:100%; max-width:420px; }
</style>
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>
<div class="login-wrap">
  <div style="text-align:center;margin-bottom:32px">
    <div class="logo" style="font-size:28px;display:block;margin-bottom:8px">⚡ SKILLSWAP</div>
    <p style="color:var(--text-muted);font-size:14px">Reset your password</p>
  </div>
  <div class="card">
    <h2 style="font-size:20px;font-weight:800;margin-bottom:24px">Forgot Password</h2>
    <?php if($message): ?>
    <div class="alert alert-<?= $msg_type ?>" style="margin-bottom:20px"><?= $message ?></div>
    <?php endif; ?>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;line-height:1.6">Enter your email address and we'll send you a link to reset your password.</p>
    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <div style="position:relative">
          <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" style="padding-left:42px" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px">Send Reset Link</button>
    </form>
    <div style="text-align:center;margin-top:20px">
      <a href="login.php" style="color:var(--text-muted);text-decoration:none;font-size:13px;font-weight:600"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>
