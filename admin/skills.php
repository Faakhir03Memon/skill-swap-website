<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_skill'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO skills (name, category) VALUES (?, ?)");
    if ($stmt->execute([$name, $category])) {
        $message = "Skill added successfully!";
    }
}

$skills = $pdo->query("SELECT * FROM skills ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills | Admin</title>
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
                    <li><a href="skills.php" style="color: var(--primary);"><i class="fas fa-lightbulb"></i> Manage Skills</a></li>
                    <li><a href="exams.php"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
                    <li><a href="results.php"><i class="fas fa-poll"></i> View Results</a></li>
                    <li style="margin-top: auto;"><a href="../logout.php" style="color: var(--accent);"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Manage Skills</h1>
            
            <div class="grid-3" style="grid-template-columns: 1fr 2fr;">
                <div class="card glass">
                    <h3>Add New Skill</h3>
                    <?php if($message): ?>
                        <p style="color: var(--success); margin: 10px 0;"><?php echo $message; ?></p>
                    <?php endif; ?>
                    <form method="POST" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Skill Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" name="category" class="form-control" required>
                        </div>
                        <button type="submit" name="add_skill" class="btn btn-primary" style="width: 100%;">Add Skill</button>
                    </form>
                </div>

                <div class="card glass">
                    <h3>Existing Skills</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($skills as $skill): ?>
                            <tr>
                                <td><?php echo $skill['name']; ?></td>
                                <td><?php echo $skill['category']; ?></td>
                                <td>
                                    <a href="#" style="color: var(--accent);"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
