<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }
$user_id = $_SESSION['user_id'];
$exams = $pdo->query("SELECT e.*, s.name as skill_name, s.category, (SELECT COUNT(*) FROM questions WHERE exam_id=e.id) as q_count FROM exams e JOIN skills s ON e.skill_id=s.id ORDER BY e.created_at DESC")->fetchAll();
$passed_stmt = $pdo->prepare("SELECT exam_id FROM results WHERE user_id=? AND status='pass'"); $passed_stmt->execute([$user_id]); $passed_ids = $passed_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Take Exams | SkillSwap</title>
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
      <h1><i class="fas fa-vial" style="color:var(--primary-light);margin-right:12px"></i>Available Exams</h1>
      <p>Prove your skills and earn verified certificates.</p>
    </div>
    <div class="badge badge-success" style="font-size:14px;padding:10px 18px"><i class="fas fa-award"></i> <?= count($passed_ids) ?> Passed</div>
  </div>

  <?php if(empty($exams)): ?>
  <div class="card reveal" style="text-align:center;padding:80px">
    <i class="fas fa-vial" style="font-size:52px;color:var(--text-muted);opacity:0.3;display:block;margin-bottom:16px"></i>
    <h3 style="color:var(--text-muted);font-weight:400">No exams available yet.</h3>
    <p style="color:var(--text-muted);font-size:13px;margin-top:8px">Check back soon — the admin will add exams shortly.</p>
  </div>
  <?php else: ?>
  <div class="grid-3 reveal">
    <?php foreach($exams as $exam): ?>
    <?php $passed = in_array($exam['id'], $passed_ids); ?>
    <div style="background:var(--glass-bg);border:1px solid <?= $passed?'rgba(16,185,129,0.25)':'var(--glass-border)' ?>;border-radius:20px;padding:28px;backdrop-filter:blur(20px);transition:all 0.3s;position:relative;overflow:hidden" onmouseover="this.style.borderColor='rgba(59,130,246,0.35)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='<?= $passed?'rgba(16,185,129,0.25)':'var(--glass-border)' ?>'; this.style.transform='translateY(0)'">
      <?php if($passed): ?>
      <div style="position:absolute;top:16px;right:16px"><span class="badge badge-success"><i class="fas fa-check"></i> Passed</span></div>
      <?php endif; ?>
      <div style="margin-bottom:16px">
        <div style="width:44px;height:44px;background:rgba(26,86,219,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
          <i class="fas fa-file-alt" style="color:var(--primary-light);font-size:18px"></i>
        </div>
        <h3 style="font-size:16px;font-weight:700;margin-bottom:6px;color:white"><?= htmlspecialchars($exam['title']) ?></h3>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:14px"><?= htmlspecialchars($exam['description'] ?? '') ?></p>
      </div>
      <div style="background:rgba(0,8,30,0.4);border-radius:10px;padding:12px;margin-bottom:18px">
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
          <span style="color:var(--text-muted)">Skill</span>
          <span class="badge badge-primary" style="font-size:10px"><?= htmlspecialchars($exam['skill_name']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
          <span style="color:var(--text-muted)">Questions</span>
          <span style="color:white;font-weight:600"><?= $exam['q_count'] ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px">
          <span style="color:var(--text-muted)">Passing Score</span>
          <span style="color:var(--success);font-weight:700"><?= $exam['passing_score'] ?>%</span>
        </div>
      </div>
      <?php if($exam['q_count']>0): ?>
      <a href="take_exam.php?id=<?= $exam['id'] ?>" class="btn btn-<?= $passed?'outline':'primary' ?>" style="width:100%">
        <i class="fas fa-<?= $passed?'redo':'play' ?>"></i> <?= $passed?'Retake Exam':'Start Exam' ?>
      </a>
      <?php else: ?>
      <button class="btn btn-outline" style="width:100%;cursor:not-allowed;opacity:0.5" disabled><i class="fas fa-ban"></i> No Questions Yet</button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
