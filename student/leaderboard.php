<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }
$user_id = $_SESSION['user_id'];
$rankers = $pdo->query("SELECT id, name, ranking_points, created_at FROM users WHERE role='student' AND is_approved=1 ORDER BY ranking_points DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leaderboard | SkillSwap</title>
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
      <h1><i class="fas fa-trophy" style="color:var(--warning);margin-right:12px"></i>Global Leaderboard</h1>
      <p>The best skill swappers on the platform. Where do you rank?</p>
    </div>
  </div>

  <!-- Top 3 Podium -->
  <?php if(count($rankers) >= 3): ?>
  <div class="reveal" style="display:flex;justify-content:center;align-items:flex-end;gap:20px;margin-bottom:40px">
    <!-- 2nd -->
    <div style="text-align:center;flex:1;max-width:180px">
      <div style="background:linear-gradient(135deg,rgba(148,163,184,0.15),rgba(0,8,30,0.5));border:1px solid rgba(148,163,184,0.25);border-radius:16px;padding:24px 16px 20px;position:relative">
        <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#64748b;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px">#2</div>
        <i class="fas fa-crown" style="color:#94a3b8;font-size:24px;margin-bottom:10px;display:block"></i>
        <div class="avatar" style="margin:0 auto 10px;background:linear-gradient(135deg,#64748b,#475569)"><?= strtoupper(substr($rankers[1]['name'],0,1)) ?></div>
        <div style="font-weight:700;font-size:14px;margin-bottom:4px"><?= htmlspecialchars($rankers[1]['name']) ?></div>
        <div style="color:#94a3b8;font-size:13px;font-weight:600"><?= number_format($rankers[1]['ranking_points']) ?> pts</div>
      </div>
    </div>
    <!-- 1st -->
    <div style="text-align:center;flex:1;max-width:200px">
      <div style="background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(0,8,30,0.5));border:2px solid rgba(245,158,11,0.4);border-radius:20px;padding:32px 20px 24px;position:relative;box-shadow:0 0 40px rgba(245,158,11,0.15)">
        <div style="position:absolute;top:-16px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px;color:#000">#1</div>
        <i class="fas fa-crown" style="color:gold;font-size:32px;margin-bottom:12px;display:block"></i>
        <div class="avatar" style="margin:0 auto 12px;width:52px;height:52px;font-size:20px;background:linear-gradient(135deg,#f59e0b,#d97706)"><?= strtoupper(substr($rankers[0]['name'],0,1)) ?></div>
        <div style="font-weight:800;font-size:16px;margin-bottom:4px"><?= htmlspecialchars($rankers[0]['name']) ?></div>
        <div style="color:#f59e0b;font-size:14px;font-weight:700"><?= number_format($rankers[0]['ranking_points']) ?> pts</div>
      </div>
    </div>
    <!-- 3rd -->
    <div style="text-align:center;flex:1;max-width:180px">
      <div style="background:linear-gradient(135deg,rgba(180,120,60,0.12),rgba(0,8,30,0.5));border:1px solid rgba(205,127,50,0.25);border-radius:16px;padding:24px 16px 20px;position:relative">
        <div style="position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#92400e;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:12px">#3</div>
        <i class="fas fa-crown" style="color:#cd7f32;font-size:24px;margin-bottom:10px;display:block"></i>
        <div class="avatar" style="margin:0 auto 10px;background:linear-gradient(135deg,#92400e,#78350f)"><?= strtoupper(substr($rankers[2]['name'],0,1)) ?></div>
        <div style="font-weight:700;font-size:14px;margin-bottom:4px"><?= htmlspecialchars($rankers[2]['name']) ?></div>
        <div style="color:#cd7f32;font-size:13px;font-weight:600"><?= number_format($rankers[2]['ranking_points']) ?> pts</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Full Table -->
  <div class="card reveal">
    <table class="data-table">
      <thead>
        <tr><th>Rank</th><th>Student</th><th>Member Since</th><th>Points</th><th>Badge</th></tr>
      </thead>
      <tbody>
        <?php foreach($rankers as $i => $r): ?>
        <tr style="<?= $r['id']==$user_id?'background:rgba(26,86,219,0.08);':'' ?>">
          <td style="font-weight:800;font-size:16px;width:60px">
            <?php if($i==0): ?><i class="fas fa-crown" style="color:gold"></i>
            <?php elseif($i==1): ?><i class="fas fa-crown" style="color:#94a3b8"></i>
            <?php elseif($i==2): ?><i class="fas fa-crown" style="color:#cd7f32"></i>
            <?php else: ?><span style="color:var(--text-muted)">#<?= $i+1 ?></span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar" style="<?= $r['id']==$user_id?'background:linear-gradient(135deg,var(--primary),var(--royal-bright))':'' ?>"><?= strtoupper(substr($r['name'],0,1)) ?></div>
              <span style="font-weight:600"><?= htmlspecialchars($r['name']) ?><?= $r['id']==$user_id?' <span style="font-size:11px;color:var(--primary-light)">(You)</span>':'' ?></span>
            </div>
          </td>
          <td style="color:var(--text-muted)"><?= date('M Y', strtotime($r['created_at'])) ?></td>
          <td><span style="font-weight:800;font-size:16px;color:var(--secondary)"><?= number_format($r['ranking_points']) ?></span></td>
          <td>
            <?php if($r['ranking_points']>500): ?><span class="badge badge-warning"><i class="fas fa-star"></i> Elite Scholar</span>
            <?php elseif($r['ranking_points']>200): ?><span class="badge badge-primary"><i class="fas fa-fire"></i> Pro Swapper</span>
            <?php else: ?><span class="badge badge-muted">Student</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($rankers)): ?><tr><td colspan="5" style="text-align:center;padding:50px;color:var(--text-muted)">No students ranked yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
