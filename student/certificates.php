<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }
$user_id = $_SESSION['user_id'];
$certs_stmt = $pdo->prepare("SELECT c.*, e.title as exam_title, s.name as skill_name FROM certificates c JOIN exams e ON c.exam_id=e.id JOIN skills s ON e.skill_id=s.id WHERE c.user_id=? ORDER BY c.issued_at DESC");
$certs_stmt->execute([$user_id]);
$certs_list = $certs_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Certificates | SkillSwap</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>
<div class="dashboard-container">
<?php include '../includes/student_sidebar.php'; ?>
<main class="main-content">

  <div class="page-header reveal">
    <div>
      <h1><i class="fas fa-certificate" style="color:var(--warning);margin-right:12px"></i>My Certificates</h1>
      <p>Your verified skill achievements and credentials.</p>
    </div>
    <div class="badge badge-warning" style="font-size:14px;padding:10px 18px"><?= count($certs_list) ?> Earned</div>
  </div>

  <?php if(empty($certs_list)): ?>
  <div class="card reveal" style="text-align:center;padding:100px 40px">
    <i class="fas fa-award" style="font-size:64px;color:var(--text-muted);opacity:0.2;display:block;margin-bottom:20px"></i>
    <h3 style="color:var(--text-muted);font-weight:500;margin-bottom:10px">No Certificates Yet</h3>
    <p style="color:var(--text-muted);font-size:14px;max-width:400px;margin:0 auto 24px">Pass an exam to earn your first verified certificate and boost your ranking!</p>
    <a href="exams.php" class="btn btn-primary"><i class="fas fa-vial"></i> Browse Exams</a>
  </div>
  <?php else: ?>
  <div class="grid-3 reveal">
    <?php foreach($certs_list as $cert): ?>
    <div style="background:linear-gradient(135deg,rgba(26,86,219,0.15),rgba(0,8,30,0.5));border:1px solid rgba(59,130,246,0.25);border-radius:20px;padding:32px;text-align:center;transition:all 0.3s;position:relative;overflow:hidden" onmouseover="this.style.transform='translateY(-6px)';this.style.borderColor='rgba(59,130,246,0.5)'" onmouseout="this.style.transform='translateY(0)';this.style.borderColor='rgba(59,130,246,0.25)'">
      <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--secondary))"></div>
      <div style="width:72px;height:72px;background:linear-gradient(135deg,rgba(245,158,11,0.2),rgba(245,158,11,0.05));border:2px solid rgba(245,158,11,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px">
        <i class="fas fa-award" style="font-size:28px;color:var(--warning)"></i>
      </div>
      <h3 style="font-size:16px;font-weight:700;margin-bottom:6px"><?= htmlspecialchars($cert['exam_title']) ?></h3>
      <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px">Skill: <span style="color:var(--primary-light)"><?= htmlspecialchars($cert['skill_name']) ?></span></p>
      <div style="background:rgba(0,8,30,0.5);border-radius:10px;padding:12px;margin-bottom:18px">
        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">Certificate Code</div>
        <div style="font-family:monospace;font-weight:700;color:var(--warning);font-size:13px;letter-spacing:1px"><?= $cert['certificate_code'] ?></div>
      </div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px">
        <i class="fas fa-calendar-alt" style="margin-right:4px"></i>Issued <?= date('M d, Y', strtotime($cert['issued_at'])) ?>
      </div>
      <a href="view_certificate.php?code=<?= urlencode($cert['certificate_code']) ?>" class="btn btn-primary" style="width:100%"><i class="fas fa-eye"></i> View Certificate</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
