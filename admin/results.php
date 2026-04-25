<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}
$results = $pdo->query("SELECT r.*, u.name as user_name, e.title as exam_title, e.passing_score FROM results r JOIN users u ON r.user_id=u.id JOIN exams e ON r.exam_id=e.id ORDER BY r.created_at DESC")->fetchAll();
$total_pass = count(array_filter($results, fn($r)=>$r['status']=='pass'));
$total_fail = count($results) - $total_pass;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Exam Results | SkillSwap Admin</title>
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
      <h1><i class="fas fa-poll" style="color:var(--primary-light);margin-right:12px"></i>Exam Results</h1>
      <p>Track all student exam performance across the platform.</p>
    </div>
  </div>

  <div class="stats-grid reveal" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px">
    <div class="stat-card">
      <div class="icon blue"><i class="fas fa-file-alt"></i></div>
      <div class="value"><?= count($results) ?></div>
      <div class="label">Total Attempts</div>
    </div>
    <div class="stat-card">
      <div class="icon green"><i class="fas fa-check-circle"></i></div>
      <div class="value"><?= $total_pass ?></div>
      <div class="label">Passed</div>
    </div>
    <div class="stat-card">
      <div class="icon" style="background:rgba(239,68,68,0.15);color:#ef4444"><i class="fas fa-times-circle"></i></div>
      <div class="value"><?= $total_fail ?></div>
      <div class="label">Failed</div>
    </div>
  </div>

  <div class="card reveal">
    <table class="data-table">
      <thead><tr><th>Student</th><th>Exam</th><th>Score</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach($results as $res): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar"><?= strtoupper(substr($res['user_name'],0,1)) ?></div>
              <strong><?= htmlspecialchars($res['user_name']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars($res['exam_title']) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="flex:1;background:rgba(255,255,255,0.05);border-radius:4px;height:6px;max-width:80px">
                <div style="width:<?= $res['score'] ?>%;height:100%;background:<?= $res['status']=='pass'?'#10b981':'#ef4444' ?>;border-radius:4px"></div>
              </div>
              <span style="font-weight:700;color:<?= $res['status']=='pass'?'#10b981':'#ef4444' ?>"><?= $res['score'] ?>%</span>
            </div>
          </td>
          <td>
            <span class="badge <?= $res['status']=='pass'?'badge-success':'badge-danger' ?>">
              <i class="fas fa-<?= $res['status']=='pass'?'check':'times' ?>"></i>
              <?= ucfirst($res['status']) ?>
            </span>
          </td>
          <td style="color:var(--text-muted)"><?= date('M d, Y H:i', strtotime($res['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($results)): ?>
        <tr><td colspan="5" style="text-align:center;padding:50px;color:var(--text-muted)"><i class="fas fa-poll" style="font-size:32px;opacity:0.3;display:block;margin-bottom:12px"></i>No exam results yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
