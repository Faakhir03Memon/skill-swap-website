<?php
session_start();
require_once '../includes/db.php';

$code = $_GET['code'] ?? '';

$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name, e.title as exam_title, s.name as skill_name 
    FROM certificates c 
    JOIN users u ON c.user_id = u.id 
    JOIN exams e ON c.exam_id = e.id 
    JOIN skills s ON e.skill_id = s.id
    WHERE c.certificate_code = ?
");
$stmt->execute([$code]);
$cert = $stmt->fetch();

if (!$cert) {
    die("Certificate not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo $cert['certificate_code']; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f1f5f9; color: #1e293b; padding: 40px; display: flex; flex-direction: column; align-items: center; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .certificate-card { border: 20px solid #1e293b !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 30px;">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print / Save PDF</button>
        <a href="dashboard.php" class="btn btn-outline" style="margin-left: 10px;">Back to Dashboard</a>
    </div>

    <div class="certificate-card">
        <div style="border: 2px solid #1e293b; height: 100%; padding: 40px; display: flex; flex-direction: column; align-items: center;">
            <div class="logo" style="margin-bottom: 40px; font-size: 32px;">SKILLSWAP</div>
            
            <p style="font-size: 20px; font-style: italic; margin-bottom: 20px;">This is to certify that</p>
            <h2 style="font-size: 54px; margin-bottom: 20px; color: #6366f1;"><?php echo $cert['user_name']; ?></h2>
            <p style="font-size: 20px; margin-bottom: 40px;">has successfully demonstrated proficiency in</p>
            
            <h3 style="font-size: 36px; margin-bottom: 10px;"><?php echo $cert['exam_title']; ?></h3>
            <p style="font-size: 18px; color: #64748b; margin-bottom: 60px;">Specialization in <?php echo $cert['skill_name']; ?></p>
            
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;">
                <div style="text-align: left;">
                    <p style="font-weight: bold; border-top: 1px solid #1e293b; padding-top: 10px;">Platform Director</p>
                    <p style="font-size: 14px; color: #64748b;">SkillSwap Verification</p>
                </div>
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; border: 5px double #1e293b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px; transform: rotate(-15deg);">
                        OFFICIAL<br>SEAL
                    </div>
                </div>
                <div style="text-align: right;">
                    <p style="font-weight: bold;"><?php echo date('M d, Y', strtotime($cert['issued_at'])); ?></p>
                    <p style="font-size: 14px; color: #64748b;">Issue Date</p>
                </div>
            </div>
            
            <div style="position: absolute; bottom: 20px; right: 20px; font-size: 12px; color: #94a3b8;">
                Verify at: skillswap.com/verify/<?php echo $cert['certificate_code']; ?>
            </div>
        </div>
    </div>
</body>
</html>
