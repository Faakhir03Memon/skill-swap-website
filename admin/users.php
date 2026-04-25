<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all students
$users = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY ranking_points DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
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
                    <li><a href="users.php" style="color: var(--primary);"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="skills.php"><i class="fas fa-lightbulb"></i> Manage Skills</a></li>
                    <li><a href="exams.php"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
                    <li><a href="results.php"><i class="fas fa-poll"></i> View Results</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Manage Users</h1>
            
            <div class="glass" style="margin-top: 30px; padding: 30px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Ranking Points</th>
                            <th>Joined On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['ranking_points']; ?> pts</td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="#" style="color: var(--accent);"><i class="fas fa-user-times"></i> Ban</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($users)): ?>
                            <tr><td colspan="5" style="text-align: center;">No students registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
