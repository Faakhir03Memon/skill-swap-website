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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}</style>
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>

<div style="width:100%;max-width:420px;position:relative;z-index:1">
  <div style="text-align:center;margin-bottom:32px">
    <div class="logo" style="font-size:28px;display:block;margin-bottom:8px">⚡ SKILLSWAP</div>
    <p style="color:var(--text-muted);font-size:14px">Secure Admin Portal</p>
  </div>

  <div class="card reveal active">
    <div style="text-align:center;margin-bottom:28px">
      <div style="width:60px;height:60px;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
        <i class="fas fa-user-shield" style="font-size:24px;color:var(--warning)"></i>
      </div>
      <h2 style="font-size:20px;font-weight:800">Admin Login</h2>
      <p style="color:var(--text-muted);font-size:13px;margin-top:4px">Authorized personnel only</p>
    </div>

    <?php if($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Admin Email</label>
        <div style="position:relative">
          <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="email" name="email" class="form-control" placeholder="skill@admin.com" value="skill@admin.com" style="padding-left:42px" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div style="position:relative">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
          <input type="password" name="password" id="apwd" class="form-control" placeholder="••••••••" style="padding-left:42px;padding-right:42px" required>
          <i class="fas fa-eye" id="toggleApwd" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;cursor:pointer"></i>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;margin-top:8px;background:linear-gradient(135deg,#d97706,#f59e0b)">
        <i class="fas fa-shield-alt"></i> Secure Login
      </button>
    </form>
  </div>

  <p style="text-align:center;margin-top:20px;font-size:13px">
    <a href="login.php" style="color:var(--text-muted);text-decoration:none"><i class="fas fa-arrow-left" style="margin-right:5px"></i> Back to Student Login</a>
  </p>
</div>

<script>
document.getElementById('toggleApwd').addEventListener('click', function() {
  const pwd = document.getElementById('apwd');
  const isText = pwd.type === 'text';
  pwd.type = isText ? 'password' : 'text';
  this.className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
});
</script>
</body>
</html>
