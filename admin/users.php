<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([(int)$_GET['delete_id']]);
    header("Location: users.php?msg=deleted"); exit;
}

$users = $pdo->query("SELECT * FROM users WHERE role='student' ORDER BY ranking_points DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Users | SkillSwap Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>
<div class="dashboard-container">
<?php include '../includes/admin_sidebar.php'; ?>
<main class="main-content">

  <div class="page-header reveal">
    <div>
      <h1><i class="fas fa-users" style="color:var(--primary-light);margin-right:12px"></i>Manage Users</h1>
      <p>View and manage all registered students on the platform.</p>
    </div>
    <div class="badge badge-primary" style="font-size:14px;padding:10px 18px"><?= count($users) ?> Students</div>
  </div>

  <?php if(isset($_GET['msg'])&&$_GET['msg']=='deleted'): ?>
  <div class="alert alert-danger reveal"><i class="fas fa-trash"></i> User deleted successfully.</div>
  <?php endif; ?>

  <div class="card reveal">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Student</th>
          <th>Email</th>
          <th>Plan</th>
          <th>Rank Points</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $i=>$u): ?>
        <tr>
          <td style="color:var(--text-muted);font-weight:600"><?= $i+1 ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar"><?= strtoupper(substr($u['name'],0,1)) ?></div>
              <strong><?= htmlspecialchars($u['name']) ?></strong>
            </div>
          </td>
          <td style="color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge badge-primary">Rs <?= $u['subscription_plan'] ?></span></td>
          <td><span style="color:var(--secondary);font-weight:700"><?= number_format($u['ranking_points']) ?> pts</span></td>
          <td>
            <?php if($u['is_approved']): ?>
              <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
            <?php else: ?>
              <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
            <?php endif; ?>
          </td>
          <td style="color:var(--text-muted)"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="users.php?delete_id=<?= $u['id'] ?>" class="btn btn-danger" style="padding:6px 12px;font-size:12px" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?>
        <tr><td colspan="8" style="text-align:center;padding:50px;color:var(--text-muted)"><i class="fas fa-users" style="font-size:32px;opacity:0.3;display:block;margin-bottom:12px"></i>No students registered yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
