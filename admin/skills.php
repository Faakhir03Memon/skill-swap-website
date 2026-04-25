<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}

$message = $msg_type = '';

// Add skill
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_skill'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $desc = trim($_POST['description'] ?? '');
    if ($name && $category) {
        $stmt = $pdo->prepare("INSERT INTO skills (name, category, description) VALUES (?,?,?) ON DUPLICATE KEY UPDATE category=VALUES(category)");
        try {
            $stmt->execute([$name, $category, $desc]);
            $message = "Skill '<strong>$name</strong>' added successfully!";
            $msg_type = 'success';
        } catch(Exception $e) {
            $message = "Skill already exists or error occurred.";
            $msg_type = 'danger';
        }
    }
}

// Delete skill
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM skills WHERE id=?")->execute([(int)$_GET['delete_id']]);
    header("Location: skills.php?msg=deleted"); exit;
}

$skills = $pdo->query("SELECT * FROM skills ORDER BY category, name ASC")->fetchAll();

// Group by category
$grouped = [];
foreach ($skills as $sk) {
    $grouped[$sk['category']][] = $sk;
}

$categories = ['Programming', 'Design', 'Data Science', 'Business', 'Language', 'Music', 'Engineering', 'Marketing', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Skills | SkillSwap Admin</title>
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
      <h1><i class="fas fa-lightbulb" style="color:var(--primary-light);margin-right:12px"></i>Manage Skills</h1>
      <p>Add, edit, and organize skills available on the platform.</p>
    </div>
    <div class="badge badge-primary" style="font-size:14px;padding:10px 18px"><?= count($skills) ?> Skills</div>
  </div>

  <?php if($message): ?>
  <div class="alert alert-<?= $msg_type ?> reveal"><i class="fas fa-<?= $msg_type=='success'?'check-circle':'exclamation-circle' ?>"></i> <?= $message ?></div>
  <?php endif; ?>
  <?php if(isset($_GET['msg'])&&$_GET['msg']=='deleted'): ?>
  <div class="alert alert-danger reveal"><i class="fas fa-trash"></i> Skill deleted.</div>
  <?php endif; ?>

  <div class="grid-2 reveal" style="align-items:start">
    <!-- Add Skill Form -->
    <div class="card" style="position:sticky;top:20px">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:20px"><i class="fas fa-plus-circle" style="color:var(--primary-light);margin-right:8px"></i>Add New Skill</h3>
      <form method="POST">
        <div class="form-group">
          <label>Skill Name *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Python Programming" required>
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" class="form-control" required>
            <option value="">-- Select Category --</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Description (optional)</label>
          <textarea name="description" class="form-control" placeholder="Brief description of this skill..."></textarea>
        </div>
        <button type="submit" name="add_skill" class="btn btn-primary" style="width:100%"><i class="fas fa-plus"></i> Add Skill</button>
      </form>
    </div>

    <!-- Skills List -->
    <div>
      <?php if(empty($grouped)): ?>
      <div class="card" style="text-align:center;padding:60px">
        <i class="fas fa-lightbulb" style="font-size:48px;color:var(--text-muted);opacity:0.3;display:block;margin-bottom:16px"></i>
        <p style="color:var(--text-muted)">No skills added yet. Add your first skill!</p>
      </div>
      <?php else: ?>
        <?php foreach($grouped as $cat => $cat_skills): ?>
        <div class="card" style="margin-bottom:20px">
          <h4 style="font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--primary-light);margin-bottom:16px">
            <i class="fas fa-tag" style="margin-right:6px"></i><?= $cat ?>
            <span class="badge badge-primary" style="margin-left:8px"><?= count($cat_skills) ?></span>
          </h4>
          <table class="data-table">
            <thead><tr><th>Skill Name</th><th>Description</th><th style="width:80px">Action</th></tr></thead>
            <tbody>
              <?php foreach($cat_skills as $sk): ?>
              <tr>
                <td><strong><?= htmlspecialchars($sk['name']) ?></strong></td>
                <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($sk['description'] ?? '—') ?></td>
                <td>
                  <a href="skills.php?delete_id=<?= $sk['id'] ?>" class="btn btn-danger" style="padding:5px 10px;font-size:12px" onclick="return confirm('Delete this skill?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
