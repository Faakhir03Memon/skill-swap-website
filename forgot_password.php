<?php
session_start();
require_once 'includes/db.php';
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
        
        $subject = "Password Reset Request - SkillSwap";
        $email_content = "Hello,\n\nYou have requested to reset your password for SkillSwap.\n\n";
        $email_content .= "Please click the link below to reset your password:\n";
        $email_content .= $reset_link . "\n\n";
        $email_content .= "If you did not request this, please ignore this email.\n\nRegards,\nSkillSwap Team";
        
        $headers = "From: SkillSwap <info.skillswapp@gmail.com>\r\n";
        $headers .= "Reply-To: info.skillswapp@gmail.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        if (@mail($email, $subject, $email_content, $headers)) {
            $message = "A password reset link has been sent to your email.";
            $msg_type = 'success';
        } else {
            // Fallback for local testing if mail() is not configured in XAMPP
            $message = "Failed to send email. (Local dev testing link: <a href='$reset_link' style='color:#fff;text-decoration:underline'>Click Here</a>)"; 
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
