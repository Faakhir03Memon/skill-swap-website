<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }
$user_id = $_SESSION['user_id'];

// All users who are offering skills (not current user)
$offerings = $pdo->prepare("
    SELECT u.id, u.name, u.ranking_points, s.name as skill_name, s.category, us.proficiency_level
    FROM users u
    JOIN user_skills us ON u.id = us.user_id
    JOIN skills s ON us.skill_id = s.id
    WHERE us.type = 'offering' AND u.id != ? AND u.is_approved = 1
    ORDER BY u.ranking_points DESC
");
$offerings->execute([$user_id]);
$all_offerings = $offerings->fetchAll();

// Get unique categories
$cats = array_unique(array_column($all_offerings, 'category'));
sort($cats);
$filter_cat = $_GET['cat'] ?? '';
$search = trim($_GET['q'] ?? '');

$filtered = array_filter($all_offerings, function($o) use ($filter_cat, $search) {
    $cat_ok = !$filter_cat || $o['category'] === $filter_cat;
    $q_ok   = !$search || stripos($o['skill_name'],$search)!==false || stripos($o['name'],$search)!==false;
    return $cat_ok && $q_ok;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Skill Exchange | SkillSwap</title>
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
      <h1><i class="fas fa-exchange-alt" style="color:var(--primary-light);margin-right:12px"></i>Skill Exchange</h1>
      <p>Find students who can teach the skills you want to learn.</p>
    </div>
    <div class="badge badge-primary" style="font-size:14px;padding:10px 18px"><?= count($filtered) ?> Matches</div>
  </div>

  <!-- Search & Filter -->
  <div class="card reveal" style="margin-bottom:24px">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap">
      <div style="flex:1;min-width:200px;position:relative">
        <i class="fas fa-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px"></i>
        <input type="text" name="q" class="form-control" placeholder="Search skills or people..." value="<?= htmlspecialchars($search) ?>" style="padding-left:40px">
      </div>
      <select name="cat" class="form-control" style="max-width:200px">
        <option value="">All Categories</option>
        <?php foreach($cats as $c): ?><option value="<?= $c ?>" <?= $filter_cat==$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
      <?php if($filter_cat||$search): ?><a href="marketplace.php" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a><?php endif; ?>
    </form>
  </div>

  <!-- Results Grid -->
  <?php if(empty($filtered)): ?>
  <div class="card reveal" style="text-align:center;padding:80px">
    <i class="fas fa-search" style="font-size:52px;color:var(--text-muted);opacity:0.3;display:block;margin-bottom:16px"></i>
    <h3 style="color:var(--text-muted);font-weight:400">No matches found.</h3>
    <p style="color:var(--text-muted);font-size:13px;margin-top:8px">Try a different search or ask others to list their skills.</p>
  </div>
  <?php else: ?>
  <div class="grid-3 reveal">
    <?php foreach($filtered as $o): ?>
    <div style="background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:18px;padding:24px;backdrop-filter:blur(20px);transition:all 0.3s" onmouseover="this.style.borderColor='rgba(59,130,246,0.35)';this.style.transform='translateY(-4px)'" onmouseout="this.style.borderColor='var(--glass-border)';this.style.transform='translateY(0)'">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <div class="avatar"><?= strtoupper(substr($o['name'],0,1)) ?></div>
        <div>
          <div style="font-weight:700;font-size:14px"><?= htmlspecialchars($o['name']) ?></div>
          <div style="font-size:11px;color:var(--secondary);font-weight:600"><?= number_format($o['ranking_points']) ?> pts</div>
        </div>
      </div>
      <div style="background:rgba(26,86,219,0.1);border:1px solid rgba(59,130,246,0.18);border-radius:10px;padding:12px;margin-bottom:14px">
        <div style="font-size:15px;font-weight:700;color:var(--primary-light);margin-bottom:4px"><?= htmlspecialchars($o['skill_name']) ?></div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="badge badge-muted" style="font-size:10px"><?= htmlspecialchars($o['category']) ?></span>
          <div style="display:flex;gap:2px">
            <?php for($i=1;$i<=5;$i++): ?>
            <i class="fas fa-star" style="font-size:10px;color:<?= $i<=$o['proficiency_level']?'var(--warning)':'rgba(255,255,255,0.1)' ?>"></i>
            <?php endfor; ?>
          </div>
        </div>
      </div>
      <a href="profile.php" class="btn btn-primary" style="width:100%;font-size:13px"><i class="fas fa-paper-plane"></i> Request Swap</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Empty CTA if no offerings at all -->
  <?php if(empty($all_offerings)): ?>
  <div class="card reveal" style="text-align:center;padding:80px;margin-top:24px">
    <i class="fas fa-exchange-alt" style="font-size:52px;color:var(--text-muted);opacity:0.3;display:block;margin-bottom:16px"></i>
    <h3 style="color:var(--text-muted);font-weight:400">No one is offering skills yet.</h3>
    <p style="color:var(--text-muted);font-size:13px;margin-top:8px">Be the first! Add your skills in your profile.</p>
    <a href="profile.php" class="btn btn-primary" style="margin-top:20px">Add My Skills</a>
  </div>
  <?php endif; ?>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
