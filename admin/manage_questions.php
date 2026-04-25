<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$exam_id = $_GET['exam_id'] ?? 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    $q_text = $_POST['question_text'];
    $oa = $_POST['option_a'];
    $ob = $_POST['option_b'];
    $oc = $_POST['option_c'];
    $od = $_POST['option_d'];
    $correct = $_POST['correct_option'];

    $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$exam_id, $q_text, $oa, $ob, $oc, $od, $correct])) {
        $message = "Question added successfully!";
    }
}

// Fetch exam details
$exam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$exam->execute([$exam_id]);
$exam_data = $exam->fetch();

// Fetch existing questions
$questions = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$questions->execute([$exam_id]);
$questions_list = $questions->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions | Admin</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1>Questions for: <?php echo $exam_data['title']; ?></h1>
                <a href="exams.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Exams</a>
            </div>
            
            <div class="grid-3" style="grid-template-columns: 1fr 2fr;">
                <div class="card glass">
                    <h3>Add New Question</h3>
                    <?php if($message): ?>
                        <p style="color: var(--success); margin: 10px 0;"><?php echo $message; ?></p>
                    <?php endif; ?>
                    <form method="POST" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Question Text</label>
                            <textarea name="question_text" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Option A</label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Option B</label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Option C</label>
                            <input type="text" name="option_c" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Option D</label>
                            <input type="text" name="option_d" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Correct Option</label>
                            <select name="correct_option" class="form-control" required>
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                        </div>
                        <button type="submit" name="add_question" class="btn btn-primary" style="width: 100%;">Save Question</button>
                    </form>
                </div>

                <div class="card glass">
                    <h3>Existing Questions</h3>
                    <?php foreach($questions_list as $index => $q): ?>
                        <div class="glass" style="padding: 20px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between;">
                                <p style="font-weight: 600;">Q<?php echo ($index+1); ?>: <?php echo $q['question_text']; ?></p>
                                <a href="#" style="color: var(--accent);"><i class="fas fa-trash"></i></a>
                            </div>
                            <ul style="list-style: none; margin-top: 10px; font-size: 14px; color: var(--text-muted);">
                                <li>A) <?php echo $q['option_a']; ?> <?php echo $q['correct_option'] == 'A' ? '✅' : ''; ?></li>
                                <li>B) <?php echo $q['option_b']; ?> <?php echo $q['correct_option'] == 'B' ? '✅' : ''; ?></li>
                                <li>C) <?php echo $q['option_c']; ?> <?php echo $q['correct_option'] == 'C' ? '✅' : ''; ?></li>
                                <li>D) <?php echo $q['option_d']; ?> <?php echo $q['correct_option'] == 'D' ? '✅' : ''; ?></li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($questions_list)): ?>
                        <p style="color: var(--text-muted); text-align: center;">No questions added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
