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
    <div class="dashboard-container">
        <aside class="sidebar glass">
            <div class="logo" style="margin-bottom: 40px; text-align: center;">SKILLSWAP</div>
            <nav style="background: transparent; border: none; padding: 0;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="dashboard.php" style="color: var(--primary);"><i class="fas fa-th-large"></i> Overview</a></li>
                    <li><a href="marketplace.php"><i class="fas fa-exchange-alt"></i> Skill Exchange</a></li>
                    <li><a href="exams.php"><i class="fas fa-vial"></i> Take Exams</a></li>
                    <li><a href="certificates.php"><i class="fas fa-certificate"></i> My Certificates</a></li>
                    <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
                <div>
                    <h1>Welcome, <?php echo $user['name']; ?>!</h1>
                    <p style="color: var(--text-muted);">Ready to swap some skills today?</p>
                </div>
                <div class="user-info" style="display: flex; align-items: center; gap: 15px;">
                    <div style="text-align: right;">
                        <span style="display: block; font-weight: 600;">Rank: #<?php echo rand(1, 100); ?></span>
                        <span style="color: var(--secondary); font-size: 14px;"><?php echo $user['ranking_points']; ?> Points</span>
                    </div>
                    <div class="glass" style="width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--primary);">
                        <i class="fas fa-user" style="color: white; font-size: 20px;"></i>
                    </div>
                </div>
            </header>

            <div class="grid-3">
                <!-- My Skills Card -->
                <div class="card glass" style="grid-column: span 1;">
                    <h3 style="margin-bottom: 20px;"><i class="fas fa-star" style="color: var(--warning);"></i> My Skills</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach($skills_list as $sk): ?>
                            <span class="badge <?php echo $sk['type'] == 'offering' ? 'badge-primary' : 'badge-success'; ?>">
                                <?php echo $sk['name']; ?> (<?php echo $sk['type'] == 'offering' ? 'Have' : 'Want'; ?>)
                            </span>
                        <?php endforeach; ?>
                        <?php if(empty($skills_list)): ?>
                            <p style="color: var(--text-muted); font-size: 14px;">No skills added yet.</p>
                        <?php endif; ?>
                    </div>
                    <a href="profile.php" class="btn btn-outline" style="margin-top: 20px; width: 100%; font-size: 14px;">Manage Skills</a>
                </div>

                <!-- AI Recommendations -->
                <div class="card glass" style="grid-column: span 2;">
                    <h3 style="margin-bottom: 20px;"><i class="fas fa-magic" style="color: var(--primary);"></i> AI-Matched Partners</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach($matches as $match): ?>
                        <div class="glass" style="padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div style="width: 40px; height: 40px; background: #334155; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--secondary);">
                                    <?php echo substr($match['name'], 0, 1); ?>
                                </div>
                                <div>
                                    <p style="font-weight: 600;"><?php echo $match['name']; ?></p>
                                    <p style="font-size: 12px; color: var(--text-muted);">Can teach: <span style="color: var(--primary);"><?php echo $match['skill_name']; ?></span></p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-size: 12px; font-weight: bold; color: var(--secondary);"><?php echo $match['ranking_points']; ?> pts</p>
                                <a href="request_swap.php?user_id=<?php echo $match['id']; ?>" class="btn btn-primary" style="padding: 5px 15px; font-size: 12px; margin-top: 5px;">Request Swap</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($matches)): ?>
                            <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                                <i class="fas fa-search" style="font-size: 30px; margin-bottom: 10px;"></i>
                                <p>Add skills you want to learn to see AI matches!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity / Exams -->
            <div class="grid-3" style="margin-top: 30px;">
                <div class="card glass" style="grid-column: span 3;">
                    <h3 style="margin-bottom: 20px;">Available Exams</h3>
                    <div class="grid-3" style="margin-top: 0;">
                        <?php
                        $exams = $pdo->query("SELECT e.*, s.name as skill_name FROM exams e JOIN skills s ON e.skill_id = s.id LIMIT 3")->fetchAll();
                        foreach($exams as $exam):
                        ?>
                        <div class="glass" style="padding: 20px;">
                            <h4 style="color: var(--primary);"><?php echo $exam['title']; ?></h4>
                            <p style="font-size: 14px; color: var(--text-muted); margin: 10px 0;"><?php echo $exam['skill_name']; ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <span style="font-size: 12px;">Passing: <?php echo $exam['passing_score']; ?>%</span>
                                <a href="take_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px;">Start Exam</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($exams)): ?>
                            <p style="color: var(--text-muted);">No exams available at the moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
