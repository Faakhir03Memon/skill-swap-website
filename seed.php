<?php
require_once 'includes/db.php';

$admin_email = 'skill@admin.com';
$admin_pass = 'skill@access.com';
$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin', $admin_email, $hashed_pass, 'admin']);
        echo "Admin user created successfully.\n";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_pass, $admin_email]);
        echo "Admin password updated successfully.\n";
    }

    // Seed some skills
    $skills = [
        ['PHP Development', 'Programming'],
        ['UI/UX Design', 'Design'],
        ['Graphic Design', 'Design'],
        ['Digital Marketing', 'Marketing'],
        ['Python Programming', 'Programming'],
        ['Data Science', 'Data'],
        ['English Speaking', 'Languages'],
        ['Spanish Speaking', 'Languages'],
        ['Public Speaking', 'Soft Skills'],
        ['Project Management', 'Business']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO skills (name, category) VALUES (?, ?)");
    foreach ($skills as $skill) {
        $stmt->execute($skill);
    }
    echo "Initial skills seeded.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
