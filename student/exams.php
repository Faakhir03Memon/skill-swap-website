<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all exams
$exams = $pdo->query("
    SELECT e.*, s.name as skill_name, 
    (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) as q_count
    FROM exams e 
    JOIN skills s ON e.skill_id = s.id 
    ORDER BY e.created_at DESC
")->fetchAll();

// Fetch my passed exams to show completion
$passed_exams = $pdo->prepare("SELECT exam_id FROM results WHERE user_id = ? AND status = 'pass'");
$passed_exams->execute([$user_id]);
$passed_ids = $passed_exams->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams | SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar glass">
            <div class="logo" style="margin-bottom: 40px; text-align: center;">SKILLSWAP</div>
            <nav style="background: transparent; border: none; padding: 0;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="dashboard.php"><i class="fas fa-th-large"></i> Overview</a></li>
                    <li><a href="marketplace.php"><i class="fas fa-exchange-alt"></i> Skill Exchange</a></li>
                    <li><a href="exams.php" style="color: var(--primary);"><i class="fas fa-vial"></i> Take Exams</a></li>
                    <li><a href="certificates.php"><i class="fas fa-certificate"></i> My Certificates</a></li>
                    <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Available Exams</h1>
            <p style="color: var(--text-muted); margin-bottom: 40px;">Prove your skills and earn certificates.</p>

            <div class="grid-3">
                <?php foreach($exams as $exam): ?>
                <div class="card glass">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <h3 style="color: var(--primary);"><?php echo $exam['title']; ?></h3>
                        <?php if(in_array($exam['id'], $passed_ids)): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Passed</span>
                        <?php endif; ?>
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;"><?php echo $exam['description']; ?></p>
                    
                    <div style="background: rgba(15, 23, 42, 0.4); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted);">
                            <span>Skill Category:</span>
                            <span style="color: white;"><?php echo $exam['skill_name']; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                            <span>Questions:</span>
                            <span style="color: white;"><?php echo $exam['q_count']; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                            <span>Passing Score:</span>
                            <span style="color: white;"><?php echo $exam['passing_score']; ?>%</span>
                        </div>
                    </div>

                    <?php if($exam['q_count'] > 0): ?>
                        <a href="take_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-primary" style="width: 100%;">
                            <?php echo in_array($exam['id'], $passed_ids) ? 'Retake Exam' : 'Start Exam'; ?>
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline" style="width: 100%; cursor: not-allowed;" disabled>No Questions</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
