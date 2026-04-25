<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's certificates
$certs = $pdo->prepare("
    SELECT c.*, e.title as exam_title, s.name as skill_name 
    FROM certificates c 
    JOIN exams e ON c.exam_id = e.id 
    JOIN skills s ON e.skill_id = s.id
    WHERE c.user_id = ?
    ORDER BY c.issued_at DESC
");
$certs->execute([$user_id]);
$certs_list = $certs->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates | SkillSwap</title>
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
                    <li><a href="exams.php"><i class="fas fa-vial"></i> Take Exams</a></li>
                    <li><a href="certificates.php" style="color: var(--primary);"><i class="fas fa-certificate"></i> My Certificates</a></li>
                    <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>My Certificates</h1>
            <p style="color: var(--text-muted); margin-bottom: 40px;">Manage and download your verified achievements.</p>

            <div class="grid-3">
                <?php foreach($certs_list as $cert): ?>
                <div class="card glass">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <i class="fas fa-award" style="font-size: 50px; color: var(--warning);"></i>
                    </div>
                    <h3 style="text-align: center; margin-bottom: 10px;"><?php echo $cert['exam_title']; ?></h3>
                    <p style="text-align: center; color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Skill: <?php echo $cert['skill_name']; ?></p>
                    <div style="border-top: 1px solid var(--glass-border); padding-top: 20px; margin-top: 10px;">
                        <p style="font-size: 12px; color: var(--text-muted);">Issued on: <?php echo date('M d, Y', strtotime($cert['issued_at'])); ?></p>
                        <p style="font-size: 12px; color: var(--text-muted);">Code: <?php echo $cert['certificate_code']; ?></p>
                    </div>
                    <a href="view_certificate.php?code=<?php echo $cert['certificate_code']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 20px;">View & Download</a>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($certs_list)): ?>
                    <div style="grid-column: span 3; text-align: center; padding: 100px;">
                        <i class="fas fa-certificate" style="font-size: 60px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
                        <p style="color: var(--text-muted);">You haven't earned any certificates yet. Take an exam to start!</p>
                        <a href="exams.php" class="btn btn-primary" style="margin-top: 20px;">Browse Exams</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
