<?php
require_once 'includes/db.php';

// Add description column if not exists
try { $pdo->exec("ALTER TABLE skills ADD COLUMN description TEXT NULL"); } catch(Exception $e) {}

$skills = [
    // Programming
    ['Python Programming',    'Programming',   'Build apps, scripts, and data pipelines with Python'],
    ['JavaScript (ES6+)',     'Programming',   'Modern JS including async, classes, and modules'],
    ['PHP Web Development',   'Programming',   'Server-side web development with PHP and MySQL'],
    ['Java Programming',      'Programming',   'Object-oriented development with Java'],
    ['C++ Programming',       'Programming',   'Systems and performance-critical programming'],
    ['React.js',              'Programming',   'Build interactive UIs with React and hooks'],
    ['Node.js',               'Programming',   'Backend JavaScript with Express and APIs'],
    // Data Science
    ['Data Analysis',         'Data Science',  'Analyse data using pandas, numpy, and visualization'],
    ['Machine Learning',      'Data Science',  'Build predictive models with scikit-learn and TensorFlow'],
    ['SQL & Databases',       'Data Science',  'Write complex queries and design relational schemas'],
    ['Power BI / Tableau',    'Data Science',  'Create interactive business intelligence dashboards'],
    // Design
    ['UI/UX Design',          'Design',        'Design intuitive user interfaces and experiences'],
    ['Graphic Design',        'Design',        'Create visual content using Adobe tools or Canva'],
    ['Figma Prototyping',     'Design',        'Prototype and wireframe apps and websites in Figma'],
    ['Video Editing',         'Design',        'Edit professional videos using Premiere or DaVinci'],
    // Business
    ['Digital Marketing',     'Marketing',     'SEO, social media, email campaigns, and paid ads'],
    ['Content Writing',       'Marketing',     'Write blogs, copy, and engaging content for the web'],
    ['Project Management',    'Business',      'Plan, execute, and deliver projects on time'],
    ['Accounting & Finance',  'Business',      'Basic accounting, budgeting, and financial statements'],
    // Language
    ['English Communication', 'Language',      'Improve spoken and written English fluency'],
    ['Arabic Language',       'Language',      'Learn Arabic reading, writing, and conversation'],
    // Music
    ['Guitar Playing',        'Music',         'Learn acoustic or electric guitar from basics to advanced'],
    ['Music Production',      'Music',         'Produce tracks using DAWs like FL Studio or Ableton'],
    // Engineering
    ['Arduino & IoT',         'Engineering',   'Build IoT projects with Arduino, sensors, and actuators'],
    ['AutoCAD / Drafting',    'Engineering',   'Create technical drawings and 2D/3D CAD designs'],
];

$inserted = 0; $skipped = 0;
$check = $pdo->prepare("SELECT id FROM skills WHERE name=?");
$insert = $pdo->prepare("INSERT INTO skills (name, category, description) VALUES (?,?,?)");

foreach ($skills as [$name, $cat, $desc]) {
    $check->execute([$name]);
    if ($check->fetch()) { $skipped++; continue; }
    $insert->execute([$name, $cat, $desc]);
    $inserted++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Seed Skills | SkillSwap</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:40px}</style>
</head>
<body>
<div class="bg-blobs"><div class="blob blob-1"></div><div class="blob blob-2"></div></div>
<div class="card" style="max-width:500px;width:100%;text-align:center;padding:48px">
  <div style="width:72px;height:72px;background:rgba(16,185,129,0.15);border:2px solid rgba(16,185,129,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px">
    <i class="fas fa-check" style="font-size:28px;color:#10b981"></i>
  </div>
  <h2 style="font-size:22px;font-weight:800;margin-bottom:8px">Skills Seeded!</h2>
  <p style="color:var(--text-muted);margin-bottom:24px">Database has been populated with skills.</p>
  <div style="background:rgba(0,8,30,0.5);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left">
    <div style="display:flex;justify-content:space-between;margin-bottom:10px">
      <span style="color:var(--text-muted)">Newly Inserted</span>
      <span style="color:#10b981;font-weight:700"><?= $inserted ?> skills</span>
    </div>
    <div style="display:flex;justify-content:space-between">
      <span style="color:var(--text-muted)">Already Existed</span>
      <span style="color:var(--primary-light);font-weight:700"><?= $skipped ?> skills</span>
    </div>
  </div>
  <div style="display:flex;gap:10px">
    <a href="admin/skills.php" class="btn btn-primary" style="flex:1"><i class="fas fa-lightbulb"></i> View Skills</a>
    <a href="admin/dashboard.php" class="btn btn-outline" style="flex:1"><i class="fas fa-home"></i> Dashboard</a>
  </div>
</div>
</body>
</html>
