<?php
session_start();
require_once '../includes/db.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch user's skills
$my_skills = $pdo->prepare("SELECT s.name, us.type, us.proficiency_level FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ?");
$my_skills->execute([$user_id]);
$skills_list = $my_skills->fetchAll();

// AI Skill Matching Logic (Simple version)
// 1. Get skills I want
$wanted_skills_stmt = $pdo->prepare("SELECT skill_id FROM user_skills WHERE user_id = ? AND type = 'seeking'");
$wanted_skills_stmt->execute([$user_id]);
$wanted_ids = $wanted_skills_stmt->fetchAll(PDO::FETCH_COLUMN);

$matches = [];
if (!empty($wanted_ids)) {
    // 2. Find users who have those skills (offering)
    $in  = str_repeat('?,', count($wanted_ids) - 1) . '?';
    $match_stmt = $pdo->prepare("
        SELECT u.id, u.name, u.ranking_points, s.name as skill_name 
        FROM users u 
        JOIN user_skills us ON u.id = us.user_id 
        JOIN skills s ON us.skill_id = s.id
        WHERE us.skill_id IN ($in) AND us.type = 'offering' AND u.id != ?
        ORDER BY u.ranking_points DESC
        LIMIT 5
    ");
    $params = array_merge($wanted_ids, [$user_id]);
    $match_stmt->execute($params);
    $matches = $match_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="dashboard-container">
        <aside class="sidebar glass reveal active">
            <div class="logo" style="margin-bottom: 40px; text-align: center; font-size: 24px;">SKILLSWAP</div>
            <nav style="background: transparent; border: none; padding: 0;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="dashboard.php" style="color: var(--primary-bright); font-weight: 600;"><i class="fas fa-th-large" style="margin-right: 10px;"></i> Overview</a></li>
                    <li><a href="marketplace.php" style="color: var(--text-muted);"><i class="fas fa-exchange-alt" style="margin-right: 10px;"></i> Skill Exchange</a></li>
                    <li><a href="exams.php" style="color: var(--text-muted);"><i class="fas fa-vial" style="margin-right: 10px;"></i> Take Exams</a></li>
                    <li><a href="certificates.php" style="color: var(--text-muted);"><i class="fas fa-certificate" style="margin-right: 10px;"></i> My Certificates</a></li>
                    <li><a href="leaderboard.php" style="color: var(--text-muted);"><i class="fas fa-trophy" style="margin-right: 10px;"></i> Leaderboard</a></li>
                    <li style="margin-top: 40px;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="reveal" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 48px;">
                <div>
                    <h1 style="font-size: 32px;">Welcome, <?php echo $user['name']; ?>!</h1>
                    <p style="color: var(--text-muted); font-size: 16px;">Ready to swap some skills today?</p>
                </div>
                <div class="user-info" style="display: flex; align-items: center; gap: 20px;">
                    <div style="text-align: right;">
                        <span style="display: block; font-weight: 700; color: var(--primary-bright);">Rank: #<?php echo rand(1, 100); ?></span>
                        <span style="color: var(--secondary); font-size: 14px; font-weight: 500;"><?php echo $user['ranking_points']; ?> Points</span>
                    </div>
                    <div class="glass" style="width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary), #4f46e5); box-shadow: 0 8px 16px -4px var(--primary-glow);">
                        <i class="fas fa-user" style="color: white; font-size: 24px;"></i>
                    </div>
                </div>
            </header>

            <div class="grid-3">
                <!-- My Skills Card -->
                <div class="card glass reveal" style="grid-column: span 1; transition-delay: 0.1s;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;"><i class="fas fa-star" style="color: var(--warning); margin-right: 10px;"></i> My Skills</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <?php foreach($skills_list as $sk): ?>
                            <span class="badge <?php echo $sk['type'] == 'offering' ? 'badge-primary' : 'badge-success'; ?>">
                                <?php echo $sk['name']; ?>
                            </span>
                        <?php endforeach; ?>
                        <?php if(empty($skills_list)): ?>
                            <p style="color: var(--text-muted); font-size: 14px;">No skills added yet.</p>
                        <?php endif; ?>
                    </div>
                    <a href="profile.php" class="btn btn-outline" style="margin-top: 32px; width: 100%; font-size: 14px; border-radius: 12px;">Manage Skills</a>
                </div>

                <!-- Subscription Plan Card -->
                <div class="card glass reveal" style="grid-column: span 1; transition-delay: 0.15s; background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(0,0,0,0));">
                    <h3 style="margin-bottom: 24px; font-size: 20px;"><i class="fas fa-crown" style="color: var(--primary-bright); margin-right: 10px;"></i> Subscription</h3>
                    <div style="text-align: center; padding: 10px 0;">
                        <div style="font-size: 32px; font-weight: 800; color: var(--primary-bright); margin-bottom: 5px;">
                            <?php echo $user['subscription_plan']; ?>rs <span style="font-size: 14px; color: var(--text-muted); font-weight: 400;">/ mo</span>
                        </div>
                        <p style="font-weight: 600; font-size: 16px; color: var(--text-main); margin-bottom: 12px;">
                            <?php 
                                if($user['subscription_plan'] == 199) echo "Basic Plan";
                                elseif($user['subscription_plan'] == 299) echo "Standard Plan";
                                elseif($user['subscription_plan'] == 499) echo "Premium Plan";
                                else echo "No Active Plan";
                            ?>
                        </p>
                        <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; border: 1px solid var(--glass-border);">
                            <span style="color: var(--text-muted); font-size: 13px;">Daily Limit:</span>
                            <span style="color: var(--secondary); font-weight: 700; margin-left: 5px;"><?php echo $user['lecture_limit']; ?> Lectures</span>
                        </div>
                    </div>
                </div>

                <!-- AI Recommendations -->
                <div class="card glass reveal" style="grid-column: span 2; transition-delay: 0.2s;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;"><i class="fas fa-magic" style="color: var(--primary-bright); margin-right: 10px;"></i> AI-Matched Partners</h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach($matches as $match): ?>
                        <div class="glass" style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02);">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 44px; height: 44px; background: var(--glass-border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--secondary); font-size: 18px;">
                                    <?php echo substr($match['name'], 0, 1); ?>
                                </div>
                                <div>
                                    <p style="font-weight: 700; font-size: 16px;"><?php echo $match['name']; ?></p>
                                    <p style="font-size: 13px; color: var(--text-muted);">Can teach: <span style="color: var(--primary-bright); font-weight: 500;"><?php echo $match['skill_name']; ?></span></p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 13px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;"><?php echo $match['ranking_points']; ?> pts</p>
                                <a href="request_swap.php?user_id=<?php echo $match['id']; ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; border-radius: 10px;">Request Swap</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($matches)): ?>
                            <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                                <i class="fas fa-search" style="font-size: 40px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p style="font-weight: 300;">Add skills you want to learn to see AI matches!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Exams -->
            <div class="grid-3 reveal" style="margin-top: 32px; transition-delay: 0.3s;">
                <div class="card glass" style="grid-column: span 3;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;">Available Exams</h3>
                    <div class="grid-3" style="margin-top: 0;">
                        <?php
                        $exams = $pdo->query("SELECT e.*, s.name as skill_name FROM exams e JOIN skills s ON e.skill_id = s.id LIMIT 3")->fetchAll();
                        foreach($exams as $exam):
                        ?>
                        <div class="glass" style="padding: 24px; border-radius: 20px;">
                            <h4 style="color: var(--primary-bright); margin-bottom: 8px; font-size: 18px;"><?php echo $exam['title']; ?></h4>
                            <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;"><?php echo $exam['skill_name']; ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Passing: <span style="color: var(--text-main);"><?php echo $exam['passing_score']; ?>%</span></span>
                                <a href="take_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; border-radius: 10px;">Start Exam</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($exams)): ?>
                            <p style="color: var(--text-muted); padding: 20px;">No exams available at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/animations.js"></script>
</body>
</html>
