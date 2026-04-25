<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$my_skills = $pdo->prepare("SELECT s.name, us.type, us.proficiency_level FROM user_skills us JOIN skills s ON us.skill_id=s.id WHERE us.user_id=?");
$my_skills->execute([$user_id]);
$skills_list = $my_skills->fetchAll();

$wanted_stmt = $pdo->prepare("SELECT skill_id FROM user_skills WHERE user_id=? AND type='seeking'");
$wanted_stmt->execute([$user_id]);
$wanted_ids = $wanted_stmt->fetchAll(PDO::FETCH_COLUMN);

$matches = [];
if (!empty($wanted_ids)) {
    $in = str_repeat('?,', count($wanted_ids)-1).'?';
    $ms = $pdo->prepare("SELECT u.id,u.name,u.ranking_points,s.name as skill_name FROM users u JOIN user_skills us ON u.id=us.user_id JOIN skills s ON us.skill_id=s.id WHERE us.skill_id IN ($in) AND us.type='offering' AND u.id!=? ORDER BY u.ranking_points DESC LIMIT 5");
    $ms->execute(array_merge($wanted_ids, [$user_id]));
    $matches = $ms->fetchAll();
}

$exams = $pdo->query("SELECT e.*, s.name as skill_name FROM exams e JOIN skills s ON e.skill_id=s.id LIMIT 3")->fetchAll();
$my_certs_count = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE user_id=?"); $my_certs_count->execute([$user_id]); $certs_count = $my_certs_count->fetchColumn();
$my_results_count = $pdo->prepare("SELECT COUNT(*) FROM results WHERE user_id=?"); $my_results_count->execute([$user_id]); $exams_taken = $my_results_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard | SkillSwap</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div><div class="blob blob-3"></div></div>
<div class="dashboard-container">
<?php include '../includes/student_sidebar.php'; ?>
<main class="main-content">

  <!-- Header -->
  <div class="page-header reveal">
    <div>
      <h1>Welcome back, <?= htmlspecialchars($user['name']) ?>! 👋</h1>
      <p>Ready to swap some skills today? Here's your overview.</p>
    </div>
    <div style="display:flex;align-items:center;gap:16px">
      <div style="text-align:right">
        <div style="font-weight:700;color:var(--primary-light);font-size:16px"><?= number_format($user['ranking_points']) ?> pts</div>
        <div style="color:var(--text-muted);font-size:12px">Ranking Points</div>
      </div>
      <div class="avatar lg"><i class="fas fa-user"></i></div>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid reveal">
    <div class="stat-card">
      <div class="icon blue"><i class="fas fa-star"></i></div>
      <div class="value"><?= count($skills_list) ?></div>
      <div class="label">My Skills</div>
    </div>
    <div class="stat-card">
      <div class="icon cyan"><i class="fas fa-trophy"></i></div>
      <div class="value"><?= number_format($user['ranking_points']) ?></div>
      <div class="label">Points</div>
    </div>
    <div class="stat-card">
      <div class="icon green"><i class="fas fa-award"></i></div>
      <div class="value"><?= $certs_count ?></div>
      <div class="label">Certificates</div>
    </div>
    <div class="stat-card">
      <div class="icon amber"><i class="fas fa-vial"></i></div>
      <div class="value"><?= $exams_taken ?></div>
      <div class="label">Exams Taken</div>
    </div>
  </div>

  <div class="grid-2 reveal" style="margin-bottom:24px">
    <!-- My Skills -->
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
        <h3 style="font-size:16px;font-weight:700"><i class="fas fa-star" style="color:var(--warning);margin-right:8px"></i>My Skills</h3>
        <a href="profile.php" class="btn btn-outline" style="padding:6px 14px;font-size:12px">Manage</a>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach($skills_list as $sk): ?>
        <span class="skill-tag <?= $sk['type'] ?>">
          <i class="fas fa-<?= $sk['type']=='offering'?'chalkboard-teacher':'book-open' ?>"></i>
          <?= htmlspecialchars($sk['name']) ?>
          <span style="opacity:0.6;font-size:10px"><?= ucfirst($sk['type']) ?></span>
        </span>
        <?php endforeach; ?>
        <?php if(empty($skills_list)): ?><p style="color:var(--text-muted);font-size:13px">No skills yet. <a href="profile.php" style="color:var(--primary-light)">Add some!</a></p><?php endif; ?>
      </div>
    </div>

    <!-- Subscription -->
    <div class="card" style="background:linear-gradient(135deg,rgba(26,86,219,0.12),rgba(0,8,30,0.4))">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:18px"><i class="fas fa-crown" style="color:var(--warning);margin-right:8px"></i>Subscription Plan</h3>
      <div style="text-align:center;padding:10px 0">
        <div style="font-size:40px;font-weight:900;color:white;margin-bottom:4px">Rs <?= $user['subscription_plan'] ?></div>
        <div style="color:var(--primary-light);font-weight:600;font-size:16px;margin-bottom:16px">
          <?php
            if($user['subscription_plan']==199) echo "Basic Plan";
            elseif($user['subscription_plan']==299) echo "Standard Plan";
            elseif($user['subscription_plan']==499) echo "Premium Plan";
            else echo "No Active Plan";
          ?>
        </div>
        <div style="background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);border-radius:10px;padding:12px">
          <span style="color:var(--text-muted);font-size:13px">Daily Lecture Limit:</span>
          <span style="color:var(--secondary);font-weight:700;margin-left:6px"><?= $user['lecture_limit'] ?> Lectures</span>
        </div>
      </div>
    </div>
  </div>

  <!-- AI Matches -->
  <div class="card reveal" style="margin-bottom:24px">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:18px"><i class="fas fa-magic" style="color:var(--primary-light);margin-right:8px"></i>AI-Matched Partners</h3>
    <?php if(empty($matches)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted)">
      <i class="fas fa-search" style="font-size:36px;opacity:0.3;display:block;margin-bottom:12px"></i>
      <p>Add skills you want to learn to see AI matches!</p>
      <a href="profile.php" class="btn btn-primary" style="margin-top:16px">Add Seeking Skills</a>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach($matches as $m): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:rgba(0,20,60,0.3);border:1px solid rgba(59,130,246,0.1);border-radius:12px">
        <div style="display:flex;align-items:center;gap:14px">
          <div class="avatar"><?= strtoupper(substr($m['name'],0,1)) ?></div>
          <div>
            <div style="font-weight:700"><?= htmlspecialchars($m['name']) ?></div>
            <div style="font-size:12px;color:var(--text-muted)">Can teach: <span style="color:var(--primary-light)"><?= htmlspecialchars($m['skill_name']) ?></span></div>
          </div>
        </div>
        <div style="text-align:right">
          <div style="font-size:13px;color:var(--secondary);font-weight:700;margin-bottom:6px"><?= number_format($m['ranking_points']) ?> pts</div>
          <a href="#" class="btn btn-primary" style="padding:6px 14px;font-size:12px">Request Swap</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Available Exams -->
  <div class="card reveal">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
      <h3 style="font-size:16px;font-weight:700"><i class="fas fa-vial" style="color:var(--primary-light);margin-right:8px"></i>Available Exams</h3>
      <a href="exams.php" class="btn btn-outline" style="padding:6px 14px;font-size:12px">View All</a>
    </div>
    <?php if(empty($exams)): ?><p style="color:var(--text-muted)">No exams available yet.</p>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach($exams as $exam): ?>
      <div style="padding:20px;background:rgba(0,20,60,0.4);border:1px solid rgba(59,130,246,0.12);border-radius:14px;transition:all 0.3s" onmouseover="this.style.borderColor='rgba(59,130,246,0.35)'" onmouseout="this.style.borderColor='rgba(59,130,246,0.12)'">
        <div style="color:var(--primary-light);font-weight:700;margin-bottom:6px"><?= htmlspecialchars($exam['title']) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:14px"><?= htmlspecialchars($exam['skill_name']) ?></div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:11px;color:var(--text-muted)">Pass: <strong style="color:white"><?= $exam['passing_score'] ?>%</strong></span>
          <a href="take_exam.php?id=<?= $exam['id'] ?>" class="btn btn-primary" style="padding:6px 14px;font-size:12px">Start</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
