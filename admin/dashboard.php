<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../admin_login.php');
    exit;
}

// Handle Approval action
if (isset($_GET['approve_id'])) {
    $approve_id = (int)$_GET['approve_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$approve_id]);
    header("Location: dashboard.php?msg=approved");
    exit;
}

// Handle Reject action
if (isset($_GET['reject_id'])) {
    $reject_id = (int)$_GET['reject_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$reject_id]);
    header("Location: dashboard.php?msg=rejected");
    exit;
}

// Fetch Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_skills = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
$total_exams = $pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$total_certificates = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();

// Fetch Pending Payment Approvals
$pending_users = $pdo->query("SELECT * FROM users WHERE is_approved = 0 AND transaction_id IS NOT NULL AND transaction_id != '' ORDER BY created_at DESC")->fetchAll();

// Fetch Recent Users
$recent_users = $pdo->query("SELECT * FROM users WHERE role = 'student' AND is_approved = 1 ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar glass">
            <div class="logo" style="margin-bottom: 40px; text-align: center;">SKILLSWAP</div>
            <nav style="background: transparent; border: none; padding: 0;">
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                    <li><a href="dashboard.php" style="color: var(--primary);"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="skills.php"><i class="fas fa-lightbulb"></i> Manage Skills</a></li>
                    <li><a href="exams.php"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
                    <li><a href="results.php"><i class="fas fa-poll"></i> View Results</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
                <h1>Admin Dashboard</h1>
                <div class="user-info" style="display: flex; align-items: center; gap: 15px;">
                    <span>Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong></span>
                    <div class="glass" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </header>

            <div class="grid-3" style="grid-template-columns: repeat(4, 1fr);">
                <div class="card glass">
                    <p style="color: var(--text-muted);">Total Students</p>
                    <h2 style="font-size: 32px;"><?php echo $total_users; ?></h2>
                </div>
                <div class="card glass">
                    <p style="color: var(--text-muted);">Total Skills</p>
                    <h2 style="font-size: 32px;"><?php echo $total_skills; ?></h2>
                </div>
                <div class="card glass">
                    <p style="color: var(--text-muted);">Total Exams</p>
                    <h2 style="font-size: 32px;"><?php echo $total_exams; ?></h2>
                </div>
                <div class="card glass">
                    <p style="color: var(--text-muted);">Certificates Issued</p>
                    <h2 style="font-size: 32px;"><?php echo $total_certificates; ?></h2>
                </div>
            </div>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'approved'): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 15px 20px; border-radius: 12px; margin-top: 30px; font-weight: 600;">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i> User payment approved successfully!
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'rejected'): ?>
                <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; padding: 15px 20px; border-radius: 12px; margin-top: 30px; font-weight: 600;">
                    <i class="fas fa-times-circle" style="margin-right: 8px;"></i> User rejected and removed.
                </div>
            <?php endif; ?>

            <!-- PENDING APPROVALS SECTION -->
            <div class="glass" style="margin-top: 40px; padding: 30px; border-left: 4px solid var(--warning);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: var(--warning);"><i class="fas fa-clock" style="margin-right: 10px;"></i> Pending Payment Approvals</h3>
                    <span style="background: rgba(245, 158, 11, 0.15); color: var(--warning); padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;"><?php echo count($pending_users); ?> Pending</span>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <th>Plan</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending_users as $p_user): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($p_user['name']); ?></strong><br>
                                <span style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($p_user['email']); ?></span>
                            </td>
                            <td><span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 6px; font-size: 13px;"><?php echo $p_user['subscription_plan']; ?> Rs</span></td>
                            <td><strong style="color: var(--primary-bright);"><?php echo htmlspecialchars($p_user['payment_method']); ?></strong></td>
                            <td style="font-family: monospace; font-size: 15px; letter-spacing: 1px; color: #10b981; font-weight: bold;"><?php echo htmlspecialchars($p_user['transaction_id']); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="dashboard.php?approve_id=<?php echo $p_user['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; background: #10b981; border-color: #10b981;">
                                        <i class="fas fa-check"></i> Accept
                                    </a>
                                    <a href="dashboard.php?reject_id=<?php echo $p_user['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; background: #f43f5e; border-color: #f43f5e;" onclick="return confirm('Are you sure you want to reject and delete this user?');">
                                        <i class="fas fa-times"></i> Reject
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($pending_users)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">No pending payments to review.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="glass" style="margin-top: 40px; padding: 30px;">
                <h3 style="margin-bottom: 20px;">Recently Joined Students</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined Date</th>
                            <th>Ranking</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_users as $user): ?>
                        <tr>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $user['ranking_points']; ?> pts</td>
                            <td>
                                <a href="user_view.php?id=<?php echo $user['id']; ?>" style="color: var(--primary);"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($recent_users)): ?>
                            <tr><td colspan="5" style="text-align: center;">No users yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
