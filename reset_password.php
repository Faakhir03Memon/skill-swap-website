<?php
session_start();
require_once 'includes/db.php';
$message = ''; $msg_type = ''; $valid_token = false; $user_id = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token=? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $valid_token = true;
        $user_id = $user['id'];
    } else {
        $message = "Invalid or expired password reset link.";
        $msg_type = 'danger';
    }
} else {
    header("Location: login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if ($password === $confirm) {
        if (strlen($password) >= 6) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?")->execute([$hashed, $user_id]);
            $message = "Password updated successfully! You can now login.";
            $msg_type = 'success';
            $valid_token = false; // hide form
        } else {
            $message = "Password must be at least 6 characters long.";
            $msg_type = 'danger';
        }
    } else {
        $message = "Passwords do not match.";
        $msg_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reset Password | SkillSwap</title>
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
    <p style="color:var(--text-muted);font-size:14px">Create a new password</p>
  </div>
  <div class="card">
    <h2 style="font-size:20px;font-weight:800;margin-bottom:24px">Reset Password</h2>
    <?php if($message): ?>
    <div class="alert alert-<?= $msg_type ?>" style="margin-bottom:20px"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if($valid_token): ?>
    <form method="POST">
      <div class="form-group">
        <label>New Password</label>
        <div style="position:relative">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="password" name="password" id="pwd" class="form-control" placeholder="Enter new password" style="padding-left:42px;padding-right:42px" required minlength="6">
          <i class="fas fa-eye" id="togglePwd" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;cursor:pointer"></i>
        </div>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <div style="position:relative">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" style="padding-left:42px" required minlength="6">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px">Reset Password</button>
    </form>
    <?php endif; ?>
    <div style="text-align:center;margin-top:20px">
      <a href="login.php" style="color:var(--text-muted);text-decoration:none;font-size:13px;font-weight:600"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </div>
  </div>
</div>
<script>
const togglePwd = document.getElementById('togglePwd');
if(togglePwd) {
  togglePwd.addEventListener('click', function() {
    const pwd = document.getElementById('pwd');
    const isText = pwd.type === 'text';
    pwd.type = isText ? 'password' : 'text';
    this.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    this.style.color = isText ? 'var(--text-muted)' : 'var(--primary-light)';
  });
}
</script>
</body>
</html>
