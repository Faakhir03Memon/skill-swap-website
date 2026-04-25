<?php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillSwap | Exchange Knowledge, Grow Together</title>
    <meta name="description" content="The ultimate student skill exchange platform with AI matching, exams, and certificates.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">SKILLSWAP</a>
            <ul class="nav-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#leaderboard">Leaderboard</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="btn btn-primary">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary">Join Now</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <h1>Master New Skills Through <br><span style="color: var(--primary);">Collaborative Exchange</span></h1>
            <p>Connect with fellow students, swap expertise, take certified exams, and climb the global leaderboard. Powered by AI matching.</p>
            <div class="hero-btns">
                <a href="register.php" class="btn btn-primary">Get Started Free</a>
                <a href="#how-it-works" class="btn btn-outline" style="margin-left: 15px;">How it Works</a>
            </div>
        </div>
    </header>

    <section id="features" class="container" style="padding: 100px 0;">
        <h2 style="text-align: center; font-size: 36px;">Why Choose SkillSwap?</h2>
        <div class="grid-3">
            <div class="card glass">
                <i class="fas fa-robot" style="font-size: 40px; color: var(--primary); margin-bottom: 20px;"></i>
                <h3>AI Skill Matching</h3>
                <p>Our intelligent algorithm connects you with the perfect learning partner based on your skills and ranking.</p>
            </div>
            <div class="card glass">
                <i class="fas fa-file-signature" style="font-size: 40px; color: var(--secondary); margin-bottom: 20px;"></i>
                <h3>Verified Exams</h3>
                <p>Prove your expertise by taking skill-specific exams designed by top students and moderators.</p>
            </div>
            <div class="card glass">
                <i class="fas fa-certificate" style="font-size: 40px; color: var(--accent); margin-bottom: 20px;"></i>
                <h3>Global Certificates</h3>
                <p>Earn professional certificates upon passing exams to showcase your skills to the world.</p>
            </div>
        </div>
    </section>

    <section id="leaderboard" class="container" style="padding-bottom: 100px;">
        <div class="glass" style="padding: 40px;">
            <h2 style="margin-bottom: 30px;">Top Contributors</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Student</th>
                        <th>Skills Swapped</th>
                        <th>Points</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT name, ranking_points FROM users WHERE role = 'student' ORDER BY ranking_points DESC LIMIT 5");
                        $rank = 1;
                        while($row = $stmt->fetch()) {
                            echo "<tr>
                                <td>#$rank</td>
                                <td>{$row['name']}</td>
                                <td>" . rand(5, 50) . "</td>
                                <td>{$row['ranking_points']}</td>
                                <td><span class='badge badge-success'>Elite</span></td>
                            </tr>";
                            $rank++;
                        }
                        if($rank == 1) {
                            echo "<tr><td colspan='5' style='text-align:center;'>No students yet. Be the first!</td></tr>";
                        }
                    } catch(Exception $e) {
                        echo "<tr><td colspan='5'>Error loading leaderboard.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer style="padding: 50px 0; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-muted);">
        <p>&copy; 2026 SkillSwap Platform. All rights reserved.</p>
    </footer>
</body>
</html>
