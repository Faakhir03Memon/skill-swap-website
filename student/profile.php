<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') { header('Location: ../login.php'); exit; }
$user_id = $_SESSION['user_id'];
$message = $msg_type = '';

if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['add_skill'])) {
        $skill_id = $_POST['skill_id'];
        
        // Handle new custom skill
        if ($skill_id == 'new') {
            $custom_name = trim($_POST['custom_skill_name']);
            if (!empty($custom_name)) {
                // Check if it already exists globally
                $check_exist = $pdo->prepare("SELECT id FROM skills WHERE name = ?");
                $check_exist->execute([$custom_name]);
                $existing = $check_exist->fetch();
                if ($existing) {
                    $skill_id = $existing['id'];
                } else {
                    // Add to global skills table so everyone can see it
                    $pdo->prepare("INSERT INTO skills (name, category, description) VALUES (?, 'Community Added', 'Skill added by a community member')")->execute([$custom_name]);
                    $skill_id = $pdo->lastInsertId();
                }
            }
        }

        if ($skill_id && $skill_id != 'new') {
            $check = $pdo->prepare("SELECT id FROM user_skills WHERE user_id=? AND skill_id=? AND type=?");
            $check->execute([$user_id, $skill_id, $_POST['type']]);
            if (!$check->fetch()) {
                $pdo->prepare("INSERT INTO user_skills (user_id,skill_id,type,proficiency_level) VALUES (?,?,?,?)")->execute([$user_id,$skill_id,$_POST['type'],$_POST['level']]);
                $message = "Skill added successfully!"; $msg_type = 'success';
            } else { $message = "Skill already listed."; $msg_type = 'warning'; }
        } else {
            $message = "Please provide a valid skill name."; $msg_type = 'danger';
        }
    }
    if (isset($_POST['remove_skill_id'])) {
        $pdo->prepare("DELETE FROM user_skills WHERE id=? AND user_id=?")->execute([$_POST['remove_skill_id'], $user_id]);
        $message = "Skill removed."; $msg_type = 'success';
    }
}

$user = $pdo->prepare("SELECT * FROM users WHERE id=?"); $user->execute([$user_id]); $user = $user->fetch();
$all_skills = $pdo->query("SELECT * FROM skills ORDER BY category, name ASC")->fetchAll();
$my_skills_stmt = $pdo->prepare("SELECT us.id, s.name, s.category, us.type, us.proficiency_level FROM user_skills us JOIN skills s ON us.skill_id=s.id WHERE us.user_id=?");
$my_skills_stmt->execute([$user_id]);
$my_skills_list = $my_skills_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Profile | SkillSwap</title>
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
      <h1><i class="fas fa-user-circle" style="color:var(--primary-light);margin-right:12px"></i>My Profile & Skills</h1>
      <p>Manage skills you can teach and skills you want to learn. Added custom skills will be visible to everyone.</p>
    </div>
  </div>

  <?php if($message): ?><div class="alert alert-<?= $msg_type ?> reveal"><i class="fas fa-<?= $msg_type=='success'?'check-circle':'exclamation-triangle' ?>"></i> <?= $message ?></div><?php endif; ?>

  <!-- Profile Info -->
  <div class="card reveal" style="margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:20px">
      <div class="avatar lg" style="width:64px;height:64px;font-size:26px;border-radius:18px"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <div style="flex:1">
        <h2 style="font-size:20px;font-weight:800"><?= htmlspecialchars($user['name']) ?></h2>
        <p style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($user['email']) ?></p>
        <div style="display:flex;gap:12px;margin-top:10px">
          <span class="badge badge-primary"><i class="fas fa-crown"></i> Rs <?= $user['subscription_plan'] ?>/mo</span>
          <span class="badge badge-success"><i class="fas fa-star"></i> <?= number_format($user['ranking_points']) ?> pts</span>
          <span class="badge badge-muted"><i class="fas fa-list"></i> <?= count($my_skills_list) ?> Skills</span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid-2 reveal" style="align-items:start">
    <!-- Add Skill Form -->
    <div class="card" style="position:sticky;top:20px">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:20px"><i class="fas fa-plus-circle" style="color:var(--primary-light);margin-right:8px"></i>Add a Skill</h3>
      <form method="POST">
        <div class="form-group">
          <label>Select Skill</label>
          <select name="skill_id" id="skillSelect" class="form-control" required onchange="toggleCustomInput()">
            <option value="">-- Choose Skill --</option>
            <option value="new" style="font-weight:bold;color:var(--warning)">➕ Add Custom Skill (Not in list)</option>
            <?php
            $cur_cat = '';
            foreach($all_skills as $sk):
              if($sk['category'] !== $cur_cat) {
                if($cur_cat) echo '</optgroup>';
                echo '<optgroup label="'.$sk['category'].'">';
                $cur_cat = $sk['category'];
              }
            ?>
            <option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['name']) ?></option>
            <?php endforeach; if($cur_cat) echo '</optgroup>'; ?>
          </select>
        </div>
        
        <div class="form-group" id="customSkillGroup" style="display:none;background:rgba(245,158,11,0.05);padding:14px;border:1px dashed rgba(245,158,11,0.4);border-radius:10px">
          <label style="color:var(--warning)">Your Custom Skill Name</label>
          <input type="text" name="custom_skill_name" class="form-control" placeholder="e.g. Quantum Computing" style="border-color:rgba(245,158,11,0.3)">
          <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:6px">This will be added globally for everyone to see.</small>
        </div>

        <div class="form-group">
          <label>Type</label>
          <select name="type" class="form-control" required>
            <option value="offering">📢 I can teach this (Offering)</option>
            <option value="seeking">📚 I want to learn this (Seeking)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Proficiency Level (1–5)</label>
          <div style="display:flex;gap:8px">
            <?php for($i=1;$i<=5;$i++): ?>
            <label style="flex:1;text-align:center;cursor:pointer">
              <input type="radio" name="level" value="<?= $i ?>" <?= $i==3?'checked':'' ?> style="display:none">
              <div class="level-btn" data-level="<?= $i ?>" style="padding:10px;border-radius:8px;border:1px solid rgba(59,130,246,0.2);font-weight:700;font-size:14px;transition:all 0.2s;background:<?= $i==3?'rgba(26,86,219,0.2)':'transparent' ?>;color:<?= $i==3?'white':'var(--text-muted)' ?>"><?= $i ?></div>
            </label>
            <?php endfor; ?>
          </div>
        </div>
        <button type="submit" name="add_skill" class="btn btn-primary" style="width:100%"><i class="fas fa-plus"></i> Add to Profile</button>
      </form>
    </div>

    <!-- My Skills List -->
    <div class="card">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:20px"><i class="fas fa-list" style="color:var(--primary-light);margin-right:8px"></i>My Listed Skills <span class="badge badge-primary" style="margin-left:8px"><?= count($my_skills_list) ?></span></h3>
      <?php if(empty($my_skills_list)): ?>
      <div style="text-align:center;padding:50px;color:var(--text-muted)">
        <i class="fas fa-star" style="font-size:36px;opacity:0.3;display:block;margin-bottom:12px"></i>
        <p>No skills yet. Add your first skill!</p>
      </div>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach($my_skills_list as $sk): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(0,20,60,0.35);border:1px solid rgba(59,130,246,0.1);border-radius:12px">
          <div>
            <div style="font-weight:600;margin-bottom:4px"><?= htmlspecialchars($sk['name']) ?></div>
            <div style="display:flex;gap:8px;align-items:center">
              <span class="badge <?= $sk['type']=='offering'?'badge-primary':'badge-success' ?>">
                <i class="fas fa-<?= $sk['type']=='offering'?'chalkboard-teacher':'book-open' ?>"></i> <?= ucfirst($sk['type']) ?>
              </span>
              <div style="display:flex;gap:3px">
                <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star" style="font-size:10px;color:<?= $i<=$sk['proficiency_level']?'var(--warning)':'rgba(255,255,255,0.1)' ?>"></i>
                <?php endfor; ?>
              </div>
            </div>
          </div>
          <form method="POST">
            <input type="hidden" name="remove_skill_id" value="<?= $sk['id'] ?>">
            <button type="submit" class="btn btn-danger" style="padding:6px 10px;font-size:12px" onclick="return confirm('Remove this skill?')"><i class="fas fa-trash"></i></button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<script>
function toggleCustomInput() {
  const select = document.getElementById('skillSelect');
  const customGroup = document.getElementById('customSkillGroup');
  if (select.value === 'new') {
    customGroup.style.display = 'block';
    customGroup.querySelector('input').required = true;
  } else {
    customGroup.style.display = 'none';
    customGroup.querySelector('input').required = false;
  }
}

document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));
document.querySelectorAll('input[name="level"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.level-btn').forEach(btn => {
      btn.style.background = 'transparent'; btn.style.color = 'var(--text-muted)'; btn.style.borderColor = 'rgba(59,130,246,0.2)';
    });
    const btn = radio.nextElementSibling;
    btn.style.background = 'rgba(26,86,219,0.25)'; btn.style.color = 'white'; btn.style.borderColor = 'var(--primary-bright)';
  });
});
</script>
</body>
</html>
