<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all exam results
$results = $pdo->query("
    SELECT r.*, u.name as user_name, e.title as exam_title 
    FROM results r 
    JOIN users u ON r.user_id = u.id 
    JOIN exams e ON r.exam_id = e.id 
    ORDER BY r.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results | Admin</title>
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
                    <li><a href="exams.php"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
                    <li><a href="results.php" style="color: var(--primary);"><i class="fas fa-poll"></i> View Results</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Exam Results</h1>
            
            <div class="glass" style="margin-top: 30px; padding: 30px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $res): ?>
                        <tr>
                            <td><?php echo $res['user_name']; ?></td>
                            <td><?php echo $res['exam_title']; ?></td>
                            <td><?php echo $res['score']; ?>%</td>
                            <td>
                                <span class="badge <?php echo $res['status'] == 'pass' ? 'badge-success' : 'badge-accent'; ?>" style="<?php echo $res['status'] == 'fail' ? 'background: rgba(244, 63, 94, 0.2); color: #f43f5e;' : ''; ?>">
                                    <?php echo ucfirst($res['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($res['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($results)): ?>
                            <tr><td colspan="5" style="text-align: center;">No results recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
