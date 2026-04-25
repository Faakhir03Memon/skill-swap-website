<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exam'])) {
    $skill_id = $_POST['skill_id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $passing = $_POST['passing_score'];

    $stmt = $pdo->prepare("INSERT INTO exams (skill_id, title, description, passing_score) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$skill_id, $title, $desc, $passing])) {
        $message = "Exam created successfully!";
    }
}

$exams = $pdo->query("SELECT e.*, s.name as skill_name FROM exams e JOIN skills s ON e.skill_id = s.id ORDER BY e.created_at DESC")->fetchAll();
$skills = $pdo->query("SELECT * FROM skills ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar glass">
            <div class="logo" style="margin-bottom: 40px; text-align: center;">SKILLSWAP</div>
            <nav style="background: transparent; border: none; padding: 0;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="skills.php"><i class="fas fa-lightbulb"></i> Manage Skills</a></li>
                    <li><a href="exams.php" style="color: var(--primary);"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
                    <li><a href="results.php"><i class="fas fa-poll"></i> View Results</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Manage Exams</h1>
            
            <div class="grid-3" style="grid-template-columns: 1fr 2fr;">
                <div class="card glass">
                    <h3>Create New Exam</h3>
                    <?php if($message): ?>
                        <p style="color: var(--success); margin: 10px 0;"><?php echo $message; ?></p>
                    <?php endif; ?>
                    <form method="POST" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Select Skill</label>
                            <select name="skill_id" class="form-control" required>
                                <?php foreach($skills as $skill): ?>
                                    <option value="<?php echo $skill['id']; ?>"><?php echo $skill['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Exam Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Passing Score (%)</label>
                            <input type="number" name="passing_score" class="form-control" value="70" required>
                        </div>
                        <button type="submit" name="add_exam" class="btn btn-primary" style="width: 100%;">Create Exam</button>
                    </form>
                </div>

                <div class="card glass">
                    <h3>Current Exams</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Skill</th>
                                <th>Passing %</th>
                                <th>Questions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($exams as $exam): ?>
                            <tr>
                                <td><?php echo $exam['title']; ?></td>
                                <td><?php echo $exam['skill_name']; ?></td>
                                <td><?php echo $exam['passing_score']; ?>%</td>
                                <td>0</td>
                                <td>
                                    <a href="manage_questions.php?exam_id=<?php echo $exam['id']; ?>" style="color: var(--primary);" title="Manage Questions"><i class="fas fa-plus-circle"></i></a>
                                    <a href="#" style="color: var(--accent); margin-left: 10px;"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
