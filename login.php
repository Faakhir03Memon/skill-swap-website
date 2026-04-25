<?php
session_start();
require_once 'includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND role='student'");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        if (!$user['is_approved']) { $error = "Your payment is pending admin approval."; }
        else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'student';
            header('Location: student/dashboard.php'); exit;
        }
    } else { $error = "Invalid email or password."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login | SkillSwap</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { display:flex; align-items:center; justify-content:center; min-height:100vh; padding:24px; }
.login-wrap { width:100%; max-width:420px; }
.divider-line { display:flex; align-items:center; gap:16px; margin:20px 0; }
.divider-line::before,.divider-line::after { content:''; flex:1; height:1px; background:rgba(59,130,246,0.15); }
.divider-line span { font-size:12px; color:var(--text-muted); }
</style>
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>

<div class="login-wrap">
  <!-- Logo -->
  <div style="text-align:center;margin-bottom:32px">
    <div class="logo" style="font-size:28px;display:block;margin-bottom:8px">⚡ SKILLSWAP</div>
    <p style="color:var(--text-muted);font-size:14px">Sign in to your student account</p>
  </div>

  <div class="card">
    <h2 style="font-size:20px;font-weight:800;margin-bottom:24px">Welcome Back</h2>

    <?php if($error): ?>
    <div class="alert alert-danger" style="margin-bottom:20px"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['registered'])): ?>
    <div class="alert alert-success" style="margin-bottom:20px"><i class="fas fa-check-circle"></i> Account created! Awaiting admin approval.</div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <div style="position:relative">
          <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" style="padding-left:42px" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
        </div>
      </div>
      <div class="form-group">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
          <label style="margin-bottom:0">Password</label>
          <a href="forgot_password.php" style="font-size:12px; color:var(--primary-light); text-decoration:none; font-weight:600">Forgot Password?</a>
        </div>
        <div style="position:relative">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="password" name="password" id="pwd" class="form-control" placeholder="Enter your password" style="padding-left:42px;padding-right:42px" required>
          <i class="fas fa-eye" id="togglePwd" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;cursor:pointer"></i>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;margin-top:8px">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="divider-line"><span>Don't have an account?</span></div>
    <a href="register.php" class="btn btn-outline" style="width:100%;padding:13px"><i class="fas fa-user-plus"></i> Create Account</a>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function() {
  const pwd = document.getElementById('pwd');
  const isText = pwd.type === 'text';
  pwd.type = isText ? 'password' : 'text';
  this.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
  this.style.color = isText ? 'var(--text-muted)' : 'var(--primary-light)';
});
</script>
</body>
</html>
