<?php
session_start();
require_once 'includes/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $plan             = $_POST['plan'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($plan)) {
        $error = "Please fill in all fields and select a plan.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!in_array($plan, ['199', '299', '499'])) {
        $error = "Invalid plan selected.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            // Insert basic user — no subscription columns yet (added later via setup.php)
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
            if ($stmt->execute([$name, $email, $hashed_pass])) {
                // Store info in session to continue to payment
                $_SESSION['pending_user_id'] = $pdo->lastInsertId();
                $_SESSION['pending_plan']    = $plan;
                header('Location: payment.php');
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .plan-card { cursor: pointer; transition: all 0.3s; }
        .plan-card:has(input:checked) {
            border-color: var(--primary-bright) !important;
            background: rgba(99, 102, 241, 0.12) !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }
        .plan-card:hover { border-color: rgba(255,255,255,0.25) !important; }
        .plan-badge {
            font-size: 11px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; padding: 3px 10px; border-radius: 100px;
        }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding: 40px 16px;">
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="glass reveal active" style="width:100%; max-width:520px; padding:48px; position:relative; z-index:1;">
        <div style="text-align:center; margin-bottom:36px;">
            <a href="login.php" class="logo" style="text-decoration:none; font-size:30px;">SKILLSWAP</a>
            <p style="color:var(--text-muted); margin-top:10px; font-weight:300;">Create your account &amp; choose a plan</p>
        </div>

        <?php if ($error): ?>
            <div style="background:rgba(244,63,94,0.1); color:var(--accent); padding:14px; border-radius:12px; margin-bottom:24px; text-align:center; font-size:14px; border:1px solid rgba(244,63,94,0.2);">
                <i class="fas fa-exclamation-circle" style="margin-right:8px;"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Name & Email -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Ali Hassan" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="ali@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>

            <!-- Password -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:20px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <!-- Plan Selection -->
            <div style="margin-top:28px;">
                <label style="display:block; color:var(--text-muted); font-size:14px; font-weight:500; margin-bottom:12px;">
                    <i class="fas fa-crown" style="color:var(--primary-bright); margin-right:6px;"></i>Choose Your Plan
                </label>
                <div style="display:flex; flex-direction:column; gap:10px;">

                    <!-- Basic -->
                    <label class="plan-card glass" style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-radius:14px; border:1px solid rgba(255,255,255,0.08);">
                        <div style="display:flex; align-items:center; gap:14px;">
                            <input type="radio" name="plan" value="199" required style="accent-color:var(--primary-bright); width:16px; height:16px;">
                            <div>
                                <div style="font-weight:700; font-size:15px;">Basic Plan</div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                    <i class="fas fa-exchange-alt" style="margin-right:4px; color:var(--secondary);"></i>10 Skill Exchanges / month
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; font-size:20px; color:var(--primary-bright);">199<span style="font-size:12px; font-weight:400; color:var(--text-muted);">rs</span></div>
                            <div style="font-size:11px; color:var(--text-muted);">per month</div>
                        </div>
                    </label>

                    <!-- Standard -->
                    <label class="plan-card glass" style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-radius:14px; border:1px solid rgba(255,255,255,0.08); position:relative;">
                        <div style="position:absolute; top:-1px; right:14px; background:linear-gradient(135deg,var(--primary),#4f46e5); padding:3px 10px; border-radius:0 0 8px 8px; font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase;">Popular</div>
                        <div style="display:flex; align-items:center; gap:14px;">
                            <input type="radio" name="plan" value="299" style="accent-color:var(--primary-bright); width:16px; height:16px;">
                            <div>
                                <div style="font-weight:700; font-size:15px;">Standard Plan</div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                    <i class="fas fa-exchange-alt" style="margin-right:4px; color:var(--secondary);"></i>25 Skill Exchanges / month
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; font-size:20px; color:var(--primary-bright);">299<span style="font-size:12px; font-weight:400; color:var(--text-muted);">rs</span></div>
                            <div style="font-size:11px; color:var(--text-muted);">per month</div>
                        </div>
                    </label>

                    <!-- Premium -->
                    <label class="plan-card glass" style="display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-radius:14px; border:1px solid rgba(255,255,255,0.08);">
                        <div style="display:flex; align-items:center; gap:14px;">
                            <input type="radio" name="plan" value="499" style="accent-color:var(--primary-bright); width:16px; height:16px;">
                            <div>
                                <div style="font-weight:700; font-size:15px;">Premium Plan</div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                    <i class="fas fa-exchange-alt" style="margin-right:4px; color:var(--secondary);"></i>Unlimited Skill Exchanges
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800; font-size:20px; color:var(--primary-bright);">499<span style="font-size:12px; font-weight:400; color:var(--text-muted);">rs</span></div>
                            <div style="font-size:11px; color:var(--text-muted);">per month</div>
                        </div>
                    </label>

                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:28px; padding:15px; font-size:16px;">
                Continue to Payment <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
            </button>
        </form>

        <p style="text-align:center; margin-top:28px; color:var(--text-muted); font-size:14px;">
            Already have an account? <a href="login.php" style="color:var(--primary-bright); text-decoration:none; font-weight:600;">Sign In</a>
        </p>
    </div>

    <script src="assets/js/animations.js"></script>
</body>
</html>
