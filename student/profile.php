<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_skill'])) {
        $skill_id = $_POST['skill_id'];
        $type = $_POST['type'];
        $level = $_POST['level'];

        // Check if already exists
        $check = $pdo->prepare("SELECT id FROM user_skills WHERE user_id = ? AND skill_id = ? AND type = ?");
        $check->execute([$user_id, $skill_id, $type]);
        
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO user_skills (user_id, skill_id, type, proficiency_level) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $skill_id, $type, $level]);
            $message = "Skill added successfully!";
        } else {
            $message = "You already have this skill listed.";
        }
    }
}

// Fetch all available skills
$all_skills = $pdo->query("SELECT * FROM skills ORDER BY name ASC")->fetchAll();

// Fetch my skills
$my_skills = $pdo->prepare("
    SELECT us.id, s.name, us.type, us.proficiency_level 
    FROM user_skills us 
    JOIN skills s ON us.skill_id = s.id 
    WHERE us.user_id = ?
");
$my_skills->execute([$user_id]);
$my_skills_list = $my_skills->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | SkillSwap</title>
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
                    <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Manage My Skills</h1>
            <p style="color: var(--text-muted);">Add skills you can teach or skills you want to learn.</p>

            <div class="grid-3" style="grid-template-columns: 1fr 2fr; margin-top: 30px;">
                <div class="card glass">
                    <h3>Add Skill</h3>
                    <?php if($message): ?>
                        <p style="color: var(--primary); margin: 10px 0; font-size: 14px;"><?php echo $message; ?></p>
                    <?php endif; ?>
                    <form method="POST" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Skill</label>
                            <select name="skill_id" class="form-control" required>
                                <?php foreach($all_skills as $sk): ?>
                                    <option value="<?php echo $sk['id']; ?>"><?php echo $sk['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control" required>
                                <option value="offering">I can teach this (Offering)</option>
                                <option value="seeking">I want to learn this (Seeking)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Proficiency (1-5)</label>
                            <input type="number" name="level" class="form-control" min="1" max="5" value="1" required>
                        </div>
                        <button type="submit" name="add_skill" class="btn btn-primary" style="width: 100%;">Add to Profile</button>
                    </form>
                </div>

                <div class="card glass">
                    <h3>My Listed Skills</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Skill Name</th>
                                <th>Type</th>
                                <th>Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($my_skills_list as $sk): ?>
                            <tr>
                                <td><?php echo $sk['name']; ?></td>
                                <td><span class="badge <?php echo $sk['type'] == 'offering' ? 'badge-primary' : 'badge-success'; ?>"><?php echo ucfirst($sk['type']); ?></span></td>
                                <td><?php echo $sk['proficiency_level']; ?>/5</td>
                                <td>
                                    <a href="#" style="color: var(--accent);"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($my_skills_list)): ?>
                                <tr><td colspan="4" style="text-align: center;">No skills added yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
