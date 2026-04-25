<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

$exam_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch exam details
$stmt = $pdo->prepare("SELECT e.*, s.name as skill_name FROM exams e JOIN skills s ON e.skill_id = s.id WHERE e.id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    die("Exam not found.");
}

// Fetch questions
$q_stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$q_stmt->execute([$exam_id]);
$questions = $q_stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score = 0;
    $total = count($questions);
    
    foreach ($questions as $q) {
        $answer = $_POST['q_' . $q['id']] ?? '';
        if ($answer == $q['correct_option']) {
            $score++;
        }
    }
    
    $percentage = ($total > 0) ? ($score / $total) * 100 : 0;
    $status = ($percentage >= $exam['passing_score']) ? 'pass' : 'fail';
    
    // Save Result
    $res_stmt = $pdo->prepare("INSERT INTO results (user_id, exam_id, score, status) VALUES (?, ?, ?, ?)");
    $res_stmt->execute([$user_id, $exam_id, $percentage, $status]);
    
    if ($status == 'pass') {
        // Issue Certificate
        $cert_code = strtoupper(substr(md5(uniqid()), 0, 10));
        $cert_stmt = $pdo->prepare("INSERT INTO certificates (user_id, exam_id, certificate_code) VALUES (?, ?, ?)");
        $cert_stmt->execute([$user_id, $exam_id, $cert_code]);
        
        // Award points
        $points = 50; // Points for passing an exam
        $update_pts = $pdo->prepare("UPDATE users SET ranking_points = ranking_points + ? WHERE id = ?");
        $update_pts->execute([$points, $user_id]);
        
        header("Location: result_view.php?status=pass&score=$percentage&cert=$cert_code");
    } else {
        header("Location: result_view.php?status=fail&score=$percentage");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam: <?php echo $exam['title']; ?> | SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="padding: 50px 0;">
        <div class="glass" style="max-width: 800px; margin: 0 auto; padding: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
                <div>
                    <h2 style="color: var(--primary);"><?php echo $exam['title']; ?></h2>
                    <p style="color: var(--text-muted);">Skill: <?php echo $exam['skill_name']; ?></p>
                </div>
                <div style="text-align: right;">
                    <p style="font-weight: bold;">Passing Score: <?php echo $exam['passing_score']; ?>%</p>
                    <p style="font-size: 14px; color: var(--accent);">Time: 30:00</p>
                </div>
            </div>

            <?php if(empty($questions)): ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-muted);">This exam has no questions yet. Please contact admin.</p>
                    <a href="dashboard.php" class="btn btn-outline" style="margin-top: 20px;">Back to Dashboard</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <?php foreach($questions as $index => $q): ?>
                    <div style="margin-bottom: 30px;">
                        <p style="font-weight: 600; margin-bottom: 15px;"><?php echo ($index + 1) . ". " . $q['question_text']; ?></p>
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-left: 20px;">
                            <label class="glass" style="padding: 10px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="A" required> A) <?php echo $q['option_a']; ?>
                            </label>
                            <label class="glass" style="padding: 10px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="B"> B) <?php echo $q['option_b']; ?>
                            </label>
                            <label class="glass" style="padding: 10px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="C"> C) <?php echo $q['option_c']; ?>
                            </label>
                            <label class="glass" style="padding: 10px 20px; cursor: pointer; display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="D"> D) <?php echo $q['option_d']; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="text-align: center; margin-top: 40px;">
                        <button type="submit" class="btn btn-primary" style="padding: 15px 40px;">Submit Exam</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
