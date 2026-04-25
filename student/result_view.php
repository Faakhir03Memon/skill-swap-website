<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$status = $_GET['status'] ?? 'fail';
$score = $_GET['score'] ?? 0;
$cert = $_GET['cert'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result | SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="glass" style="max-width: 500px; padding: 50px; text-align: center;">
        <?php if($status == 'pass'): ?>
            <i class="fas fa-check-circle" style="font-size: 80px; color: var(--success); margin-bottom: 20px;"></i>
            <h1 style="color: var(--success);">Congratulations!</h1>
            <p style="margin: 20px 0; font-size: 18px;">You passed the exam with a score of <strong><?php echo $score; ?>%</strong>.</p>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Your certificate has been issued and 50 points have been added to your ranking.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="view_certificate.php?code=<?php echo $cert; ?>" class="btn btn-primary">View Certificate</a>
                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <i class="fas fa-times-circle" style="font-size: 80px; color: var(--accent); margin-bottom: 20px;"></i>
            <h1 style="color: var(--accent);">Better Luck Next Time</h1>
            <p style="margin: 20px 0; font-size: 18px;">You scored <strong><?php echo $score; ?>%</strong>. You need at least 70% to pass.</p>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Keep practicing and try again later!</p>
            <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
