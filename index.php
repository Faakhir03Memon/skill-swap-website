<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
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
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

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
        <div class="container reveal">
            <h1>Master New Skills Through <br><span style="background: linear-gradient(135deg, var(--primary-bright), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Collaborative Exchange</span></h1>
            <p>Connect with fellow students, swap expertise, take certified exams, and climb the global leaderboard. Powered by AI matching.</p>
            <div class="hero-btns">
                <a href="register.php" class="btn btn-primary">Get Started Free</a>
                <a href="#features" class="btn btn-outline" style="margin-left: 15px;">How it Works</a>
            </div>
        </div>
    </header>

    <section id="features" class="container" style="padding: 100px 0;">
        <h2 class="reveal" style="text-align: center; font-size: 42px; margin-bottom: 20px;">Why Choose SkillSwap?</h2>
        <div class="grid-3">
            <div class="card glass reveal" style="transition-delay: 0.1s;">
                <i class="fas fa-robot" style="font-size: 40px; color: var(--primary-bright); margin-bottom: 24px;"></i>
                <h3 style="margin-bottom: 15px;">AI Skill Matching</h3>
                <p style="color: var(--text-muted);">Our intelligent algorithm connects you with the perfect learning partner based on your skills and ranking.</p>
            </div>
            <div class="card glass reveal" style="transition-delay: 0.2s;">
                <i class="fas fa-file-signature" style="font-size: 40px; color: var(--secondary); margin-bottom: 24px;"></i>
                <h3 style="margin-bottom: 15px;">Verified Exams</h3>
                <p style="color: var(--text-muted);">Prove your expertise by taking skill-specific exams designed by top students and moderators.</p>
            </div>
            <div class="card glass reveal" style="transition-delay: 0.3s;">
                <i class="fas fa-certificate" style="font-size: 40px; color: var(--accent); margin-bottom: 24px;"></i>
                <h3 style="margin-bottom: 15px;">Global Certificates</h3>
                <p style="color: var(--text-muted);">Earn professional certificates upon passing exams to showcase your skills to the world.</p>
            </div>
        </div>
    </section>

    <section id="leaderboard" class="container" style="padding-bottom: 120px;">
        <div class="glass reveal" style="padding: 48px; position: relative; overflow: hidden;">
            <h2 style="margin-bottom: 40px; font-size: 32px;">Top Contributors</h2>
            <div style="overflow-x: auto;">
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
                                $badgeClass = $rank == 1 ? 'badge-success' : 'badge-primary';
                                $status = $rank == 1 ? 'Elite' : 'Pro';
                                echo "<tr class='reveal' style='transition-delay: " . ($rank * 0.1) . "s'>
                                    <td><span style='font-weight: 800; color: var(--primary-bright);'>#$rank</span></td>
                                    <td><div style='display:flex; align-items:center; gap:12px;'>
                                        <div style='width:32px; height:32px; background:var(--glass-border); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px;'>" . substr($row['name'], 0, 1) . "</div>
                                        {$row['name']}
                                    </div></td>
                                    <td>" . rand(5, 50) . "</td>
                                    <td><span style='font-weight: 600;'>{$row['ranking_points']}</span></td>
                                    <td><span class='badge $badgeClass'>$status</span></td>
                                </tr>";
                                $rank++;
                            }
                            if($rank == 1) {
                                echo "<tr><td colspan='5' style='text-align:center; padding: 40px;'>No students yet. Be the first!</td></tr>";
                            }
                        } catch(Exception $e) {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color: var(--accent);'>Error loading leaderboard.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <footer style="padding: 60px 0; border-top: 1px solid var(--glass-border); text-align: center; background: rgba(0,0,0,0.2);">
        <div class="container">
            <a href="#" class="logo" style="font-size: 20px; margin-bottom: 20px; display: block;">SKILLSWAP</a>
            <p style="color: var(--text-muted);">&copy; 2026 SkillSwap Platform. Built for the next generation of learners.</p>
        </div>
    </footer>

    <script src="assets/js/animations.js"></script>
</body>
</html>
