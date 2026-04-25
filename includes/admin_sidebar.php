<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo">⚡ SKILLSWAP</div>
        <small>Admin Panel</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        <ul>
            <li><a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="users.php" class="<?= $current=='users.php'?'active':'' ?>"><i class="fas fa-users"></i> Manage Users</a></li>
        </ul>
        <div class="nav-section-title">Content</div>
        <ul>
            <li><a href="skills.php" class="<?= $current=='skills.php'?'active':'' ?>"><i class="fas fa-lightbulb"></i> Manage Skills</a></li>
            <li><a href="exams.php" class="<?= $current=='exams.php'?'active':'' ?>"><i class="fas fa-file-alt"></i> Manage Exams</a></li>
            <li><a href="manage_questions.php" class="<?= $current=='manage_questions.php'?'active':'' ?>"><i class="fas fa-question-circle"></i> Questions</a></li>
        </ul>
        <div class="nav-section-title">Reports</div>
        <ul>
            <li><a href="results.php" class="<?= $current=='results.php'?'active':'' ?>"><i class="fas fa-poll"></i> Exam Results</a></li>
            <li><a href="certificates.php" class="<?= $current=='certificates.php'?'active':'' ?>"><i class="fas fa-award"></i> Certificates</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>
