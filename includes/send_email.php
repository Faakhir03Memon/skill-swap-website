<?php
function sendResetEmail($to_email, $reset_link) {
    $dir = __DIR__ . '/PHPMailer';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $files = [
        'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
        'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
        'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
    ];
    
    foreach ($files as $name => $url) {
        if (!file_exists("$dir/$name")) {
            $content = @file_get_contents($url);
            if ($content) file_put_contents("$dir/$name", $content);
        }
    }
    
    if (!file_exists("$dir/PHPMailer.php")) {
        return false; // Failed to download
    }

    require_once "$dir/Exception.php";
    require_once "$dir/PHPMailer.php";
    require_once "$dir/SMTP.php";

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // ==========================================
        // PUT YOUR GMAIL AND APP PASSWORD HERE
        // ==========================================
        $mail->Username   = 'info.skillswapp@gmail.com'; // Your Gmail
        $mail->Password   = 'lstgqyrxfkwvvvfn';    // Your Gmail App Password
        // ==========================================
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('info.skillswapp@gmail.com', 'SkillSwap');
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - SkillSwap';
        $mail->Body    = "
            <h3>Password Reset Request</h3>
            <p>You have requested to reset your password for SkillSwap.</p>
            <p>Please click the button below to reset it:</p>
            <a href='$reset_link' style='display:inline-block;padding:10px 20px;background:#3b82f6;color:#fff;text-decoration:none;border-radius:5px;'>Reset Password</a>
            <br><br>
            <p>If you didn't request this, you can safely ignore this email.</p>
        ";
        $mail->AltBody = "Please click the link to reset your password: $reset_link";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
