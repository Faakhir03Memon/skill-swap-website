<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

// Fetch top students
$stmt = $pdo->query("SELECT name, ranking_points, created_at FROM users WHERE role = 'student' ORDER BY ranking_points DESC LIMIT 50");
$rankers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard | SkillSwap</title>
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
                    <li><a href="certificates.php"><i class="fas fa-certificate"></i> My Certificates</a></li>
                    <li><a href="leaderboard.php" style="color: var(--primary);"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Global Leaderboard</h1>
            <p style="color: var(--text-muted); margin-bottom: 40px;">The best of the best. Where do you stand?</p>

            <div class="glass" style="padding: 30px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Member Since</th>
                            <th>Points</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rankers as $index => $ranker): ?>
                        <tr style="<?php echo $index < 3 ? 'background: rgba(99, 102, 241, 0.05);' : ''; ?>">
                            <td style="font-weight: bold; width: 80px;">
                                <?php if($index == 0): ?>
                                    <i class="fas fa-crown" style="color: gold;"></i> #1
                                <?php elseif($index == 1): ?>
                                    <i class="fas fa-crown" style="color: silver;"></i> #2
                                <?php elseif($index == 2): ?>
                                    <i class="fas fa-crown" style="color: #cd7f32;"></i> #3
                                <?php else: ?>
                                    #<?php echo ($index + 1); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 32px; height: 32px; background: #1e293b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                                        <?php echo substr($ranker['name'], 0, 1); ?>
                                    </div>
                                    <?php echo $ranker['name']; ?>
                                </div>
                            </td>
                            <td><?php echo date('M Y', strtotime($ranker['created_at'])); ?></td>
                            <td style="font-weight: 600; color: var(--secondary);"><?php echo number_format($ranker['ranking_points']); ?> pts</td>
                            <td>
                                <?php if($ranker['ranking_points'] > 500): ?>
                                    <span class="badge badge-primary">Elite Scholar</span>
                                <?php elseif($ranker['ranking_points'] > 200): ?>
                                    <span class="badge badge-success">Pro Swapper</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(148, 163, 184, 0.2); color: #94a3b8;">Student</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($rankers)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 40px;">No students ranked yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
