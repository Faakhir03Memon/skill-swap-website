<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo">⚡ SKILLSWAP</div>
        <small>Student Portal</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Overview</div>
        <ul>
            <li><a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="profile.php" class="<?= $current=='profile.php'?'active':'' ?>"><i class="fas fa-user-circle"></i> My Profile</a></li>
        </ul>
        <div class="nav-section-title">Skills & Matching</div>
        <ul>
            <li><a href="marketplace.php" class="<?= $current=='marketplace.php'?'active':'' ?>"><i class="fas fa-exchange-alt"></i> Skill Exchange</a></li>
        </ul>
        <div class="nav-section-title">Exams & Certs</div>
        <ul>
            <li><a href="exams.php" class="<?= $current=='exams.php'?'active':'' ?>"><i class="fas fa-vial"></i> Take Exams</a></li>
            <li><a href="certificates.php" class="<?= $current=='certificates.php'?'active':'' ?>"><i class="fas fa-certificate"></i> My Certificates</a></li>
        </ul>
        <div class="nav-section-title">Community</div>
        <ul>
            <li><a href="leaderboard.php" class="<?= $current=='leaderboard.php'?'active':'' ?>"><i class="fas fa-trophy"></i> Leaderboard</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
