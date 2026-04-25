<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}
$certs = $pdo->query("SELECT c.*, u.name as user_name, e.title as exam_title, s.name as skill_name FROM certificates c JOIN users u ON c.user_id=u.id JOIN exams e ON c.exam_id=e.id JOIN skills s ON e.skill_id=s.id ORDER BY c.issued_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Certificates | SkillSwap Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>
<div class="dashboard-container">
<?php include '../includes/admin_sidebar.php'; ?>
<main class="main-content">
  <div class="page-header reveal">
    <div><h1><i class="fas fa-award" style="color:var(--warning);margin-right:12px"></i>All Certificates</h1>
    <p>All certificates issued to students on the platform.</p></div>
    <div class="badge badge-warning" style="font-size:14px;padding:10px 18px"><?= count($certs) ?> Issued</div>
  </div>
  <div class="card reveal">
    <table class="data-table">
      <thead><tr><th>Student</th><th>Exam</th><th>Skill</th><th>Certificate Code</th><th>Issued On</th></tr></thead>
      <tbody>
        <?php foreach($certs as $c): ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar"><?= strtoupper(substr($c['user_name'],0,1)) ?></div><strong><?= htmlspecialchars($c['user_name']) ?></strong></div></td>
          <td><?= htmlspecialchars($c['exam_title']) ?></td>
          <td><span class="badge badge-primary"><?= htmlspecialchars($c['skill_name']) ?></span></td>
          <td style="font-family:monospace;color:var(--warning);font-weight:700"><?= $c['certificate_code'] ?></td>
          <td style="color:var(--text-muted)"><?= date('M d, Y', strtotime($c['issued_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($certs)): ?><tr><td colspan="5" style="text-align:center;padding:50px;color:var(--text-muted)">No certificates issued yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
