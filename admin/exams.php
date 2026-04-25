<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php'); exit;
}

$message = $msg_type = '';
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_exam'])) {
    $stmt = $pdo->prepare("INSERT INTO exams (skill_id,title,description,passing_score) VALUES (?,?,?,?)");
    if ($stmt->execute([$_POST['skill_id'],$_POST['title'],$_POST['description'],$_POST['passing_score']])) {
        $message = "Exam created successfully!"; $msg_type = 'success';
    }
}
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM exams WHERE id=?")->execute([(int)$_GET['delete_id']]);
    header("Location: exams.php?msg=deleted"); exit;
}

$exams  = $pdo->query("SELECT e.*, s.name as skill_name, (SELECT COUNT(*) FROM questions WHERE exam_id=e.id) as q_count FROM exams e JOIN skills s ON e.skill_id=s.id ORDER BY e.created_at DESC")->fetchAll();
$skills = $pdo->query("SELECT * FROM skills ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Exams | SkillSwap Admin</title>
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
      <h1><i class="fas fa-file-alt" style="color:var(--primary-light);margin-right:12px"></i>Manage Exams</h1>
      <p>Create and manage skill certification exams.</p>
    </div>
    <div class="badge badge-primary" style="font-size:14px;padding:10px 18px"><?= count($exams) ?> Exams</div>
  </div>

  <?php if($message): ?><div class="alert alert-<?= $msg_type ?> reveal"><i class="fas fa-check-circle"></i> <?= $message ?></div><?php endif; ?>
  <?php if(isset($_GET['msg'])&&$_GET['msg']=='deleted'): ?><div class="alert alert-danger reveal"><i class="fas fa-trash"></i> Exam deleted.</div><?php endif; ?>

  <div class="grid-2 reveal" style="align-items:start">
    <div class="card" style="position:sticky;top:20px">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:20px"><i class="fas fa-plus-circle" style="color:var(--primary-light);margin-right:8px"></i>Create New Exam</h3>
      <form method="POST">
        <div class="form-group">
          <label>Select Skill *</label>
          <select name="skill_id" class="form-control" required>
            <option value="">-- Choose Skill --</option>
            <?php foreach($skills as $sk): ?><option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Exam Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Python Basics Certification" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="What will this exam test?"></textarea>
        </div>
        <div class="form-group">
          <label>Passing Score (%)</label>
          <input type="number" name="passing_score" class="form-control" value="70" min="1" max="100" required>
        </div>
        <button type="submit" name="add_exam" class="btn btn-primary" style="width:100%"><i class="fas fa-plus"></i> Create Exam</button>
      </form>
    </div>

    <div class="card">
      <h3 style="font-size:16px;font-weight:700;margin-bottom:20px"><i class="fas fa-list" style="color:var(--primary-light);margin-right:8px"></i>All Exams</h3>
      <table class="data-table">
        <thead><tr><th>Title</th><th>Skill</th><th>Pass %</th><th>Questions</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($exams as $exam): ?>
          <tr>
            <td><strong><?= htmlspecialchars($exam['title']) ?></strong></td>
            <td><span class="badge badge-primary"><?= htmlspecialchars($exam['skill_name']) ?></span></td>
            <td><span style="color:var(--success);font-weight:600"><?= $exam['passing_score'] ?>%</span></td>
            <td>
              <span class="badge <?= $exam['q_count']>0 ? 'badge-success' : 'badge-warning' ?>">
                <?= $exam['q_count'] ?> Q's
              </span>
            </td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="manage_questions.php?exam_id=<?= $exam['id'] ?>" class="btn btn-outline" style="padding:5px 10px;font-size:12px" title="Add Questions"><i class="fas fa-plus-circle"></i></a>
                <a href="exams.php?delete_id=<?= $exam['id'] ?>" class="btn btn-danger" style="padding:5px 10px;font-size:12px" onclick="return confirm('Delete this exam?')"><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($exams)): ?><tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">No exams created yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script>document.querySelectorAll('.reveal').forEach((el,i)=>setTimeout(()=>el.classList.add('active'),i*80));</script>
</body>
</html>
