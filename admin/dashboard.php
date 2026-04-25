<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}
if (isset($_GET['approve_id'])) {
    $pdo->prepare("UPDATE users SET is_approved=1 WHERE id=?")->execute([(int)$_GET['approve_id']]);
    header("Location: dashboard.php?msg=approved"); exit;
}
if (isset($_GET['reject_id'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_GET['reject_id']]);
    header("Location: dashboard.php?msg=rejected"); exit;
}
$total_users  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_skills = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$total_exams  = $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$total_certs  = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$pending_users = $pdo->query("SELECT * FROM users WHERE is_approved=0 AND transaction_id IS NOT NULL AND transaction_id!='' ORDER BY created_at DESC")->fetchAll();
$recent_users  = $pdo->query("SELECT * FROM users WHERE role='student' AND is_approved=1 ORDER BY created_at DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard | SkillSwap</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>
<div class="dashboard-container">
<?php include '../includes/admin_sidebar.php'; ?>
<main class="main-content">

  <!-- Header -->
  <div class="page-header reveal">
    <div>
      <h1>Admin Dashboard</h1>
      <p>Welcome back, <strong style="color:var(--primary-light)"><?= htmlspecialchars($_SESSION['user_name']) ?></strong> — Here's your platform overview.</p>
    </div>
    <div style="display:flex;align-items:center;gap:12px">
      <div class="avatar lg"><i class="fas fa-user-shield"></i></div>
    </div>
  </div>

  <!-- Alerts -->
  <?php if(isset($_GET['msg']) && $_GET['msg']=='approved'): ?>
  <div class="alert alert-success reveal"><i class="fas fa-check-circle"></i> User payment approved successfully!</div>
  <?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg']=='rejected'): ?>
  <div class="alert alert-danger reveal"><i class="fas fa-times-circle"></i> User rejected and removed.</div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-grid reveal">
    <div class="stat-card">
      <div class="icon blue"><i class="fas fa-users"></i></div>
      <div class="value"><?= $total_users ?></div>
      <div class="label">Total Students</div>
    </div>
    <div class="stat-card">
      <div class="icon cyan"><i class="fas fa-lightbulb"></i></div>
      <div class="value"><?= $total_skills ?></div>
      <div class="label">Skills Listed</div>
    </div>
    <div class="stat-card">
      <div class="icon amber"><i class="fas fa-file-alt"></i></div>
      <div class="value"><?= $total_exams ?></div>
      <div class="label">Active Exams</div>
    </div>
    <div class="stat-card">
      <div class="icon green"><i class="fas fa-award"></i></div>
      <div class="value"><?= $total_certs ?></div>
      <div class="label">Certificates Issued</div>
    </div>
  </div>

  <!-- Pending Approvals -->
  <div class="card reveal" style="margin-bottom:28px;border-left:3px solid var(--warning);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:17px;font-weight:700"><i class="fas fa-clock" style="color:var(--warning);margin-right:10px"></i>Pending Payment Approvals</h3>
      <span class="badge badge-warning"><?= count($pending_users) ?> Pending</span>
    </div>
    <table class="data-table">
      <thead><tr><th>Student</th><th>Plan</th><th>Method</th><th>Transaction ID</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($pending_users as $p): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar"><?= strtoupper(substr($p['name'],0,1)) ?></div>
              <div><strong><?= htmlspecialchars($p['name']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($p['email']) ?></small></div>
            </div>
          </td>
          <td><span class="badge badge-primary">Rs <?= $p['subscription_plan'] ?>/mo</span></td>
          <td style="color:var(--primary-light);font-weight:600"><?= htmlspecialchars($p['payment_method']) ?></td>
          <td style="font-family:monospace;color:#10b981;font-weight:700;font-size:15px"><?= htmlspecialchars($p['transaction_id']) ?></td>
          <td>
            <div style="display:flex;gap:8px">
              <a href="dashboard.php?approve_id=<?= $p['id'] ?>" class="btn btn-success" style="padding:7px 14px;font-size:12px"><i class="fas fa-check"></i> Approve</a>
              <a href="dashboard.php?reject_id=<?= $p['id'] ?>" class="btn btn-danger" style="padding:7px 14px;font-size:12px" onclick="return confirm('Reject and delete this user?')"><i class="fas fa-times"></i> Reject</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($pending_users)): ?><tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)"><i class="fas fa-check-circle" style="color:var(--success);font-size:24px;margin-bottom:8px;display:block"></i>No pending approvals</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent Students -->
  <div class="card reveal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="font-size:17px;font-weight:700"><i class="fas fa-user-graduate" style="color:var(--primary-light);margin-right:10px"></i>Recently Joined Students</h3>
      <a href="users.php" class="btn btn-outline" style="padding:8px 16px;font-size:13px">View All</a>
    </div>
    <table class="data-table">
      <thead><tr><th>Student</th><th>Email</th><th>Joined</th><th>Points</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($recent_users as $u): ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar"><?= strtoupper(substr($u['name'],0,1)) ?></div><strong><?= htmlspecialchars($u['name']) ?></strong></div></td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
          <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
          <td><span style="color:var(--secondary);font-weight:700"><?= number_format($u['ranking_points']) ?> pts</span></td>
          <td><a href="users.php" style="color:var(--primary-light)"><i class="fas fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($recent_users)): ?><tr><td colspan="5" style="text-align:center;color:var(--text-muted)">No students yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script>
document.querySelectorAll('.reveal').forEach((el,i)=>{
  setTimeout(()=>el.classList.add('active'), i*80);
});
</script>
</body>
</html>
