<?php
session_start();
require_once 'includes/db.php';

// Must come from registration flow
if (!isset($_SESSION['pending_user_id']) || !isset($_SESSION['pending_plan'])) {
    header('Location: register.php');
    exit;
}

$user_id = $_SESSION['pending_user_id'];
$plan    = $_SESSION['pending_plan'];

// Plan details
$plans = [
    '199' => ['name' => 'Basic Plan',    'exchanges' => '10 Skill Exchanges / month',        'color' => '#818cf8'],
    '299' => ['name' => 'Standard Plan', 'exchanges' => '25 Skill Exchanges / month',        'color' => '#14b8a6'],
    '499' => ['name' => 'Premium Plan',  'exchanges' => 'Unlimited Skill Exchanges / month', 'color' => '#f59e0b'],
];
$selected_plan = $plans[$plan];

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tx_id = trim($_POST['transaction_id'] ?? '');
    $method = $_POST['payment_method'] ?? '';

    if (empty($tx_id) || empty($method)) {
        $error = "Please fill in all payment details.";
    } elseif (strlen($tx_id) < 6) {
        $error = "Transaction ID must be at least 6 characters.";
    } else {
        // Check if columns exist, add if not
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('subscription_plan', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN subscription_plan INT DEFAULT 0");
            }
            if (!in_array('exchange_limit', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN exchange_limit VARCHAR(20) DEFAULT '0'");
            }
            if (!in_array('is_approved', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
            }
            if (!in_array('payment_method', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL");
            }
            if (!in_array('transaction_id', $cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL");
            }

            // Determine exchange limit label
            $exchange_limit = ($plan == '199') ? '10' : (($plan == '299') ? '25' : 'unlimited');

            // Update the user with payment info
            $stmt = $pdo->prepare("UPDATE users SET subscription_plan = ?, exchange_limit = ?, is_approved = 0, payment_method = ?, transaction_id = ? WHERE id = ?");
            $stmt->execute([$plan, $exchange_limit, $method, $tx_id, $user_id]);

            // Clear session
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_plan']);

            $success = true;
        } catch (PDOException $e) {
            $error = "Payment processing failed. Please contact support. (" . $e->getMessage() . ")";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment | SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .method-card { cursor: pointer; transition: all 0.3s; }
        .method-card:has(input:checked) {
            border-color: var(--primary-bright) !important;
            background: rgba(99, 102, 241, 0.12) !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }
        .step-badge {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; flex-shrink: 0;
        }
        .account-box {
            background: rgba(15,23,42,0.6);
            border: 1px dashed rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 15px;
            letter-spacing: 1px;
        }
        .copy-btn {
            background: none; border: none; color: var(--primary-bright);
            cursor: pointer; font-size: 13px; padding: 4px 8px;
            border-radius: 6px; transition: 0.2s;
        }
        .copy-btn:hover { background: rgba(99,102,241,0.15); }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding:40px 16px;">
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <?php if ($success): ?>
    <!-- SUCCESS STATE -->
    <div class="glass reveal active" style="width:100%; max-width:480px; padding:56px 48px; text-align:center; position:relative; z-index:1;">
        <div style="width:80px; height:80px; background:rgba(16,185,129,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; border:2px solid rgba(16,185,129,0.3);">
            <i class="fas fa-check" style="font-size:36px; color:#10b981;"></i>
        </div>
        <h2 style="font-size:28px; font-weight:800; margin-bottom:12px;">Payment Submitted!</h2>
        <p style="color:var(--text-muted); margin-bottom:28px; line-height:1.7;">
            Your payment details have been sent to admin for verification.<br>
            Your account will be <strong style="color:var(--primary-bright);">activated within 24 hours</strong> after confirmation.
        </p>
        <div style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:14px; padding:16px 20px; margin-bottom:28px; text-align:left;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:var(--text-muted); font-size:14px;">Plan</span>
                <span style="font-weight:700;"><?php echo $selected_plan['name']; ?> — <?php echo $plan; ?>rs/mo</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color:var(--text-muted); font-size:14px;">Exchanges</span>
                <span style="font-weight:700; color:var(--secondary);"><?php echo $selected_plan['exchanges']; ?></span>
            </div>
        </div>
        <a href="login.php" class="btn btn-primary" style="width:100%; padding:14px;">Go to Login</a>
    </div>

    <?php else: ?>
    <!-- PAYMENT FORM -->
    <div class="glass reveal active" style="width:100%; max-width:520px; padding:48px; position:relative; z-index:1;">

        <!-- Header -->
        <div style="text-align:center; margin-bottom:32px;">
            <a href="login.php" class="logo" style="text-decoration:none; font-size:28px;">SKILLSWAP</a>
            <p style="color:var(--text-muted); margin-top:8px; font-weight:300;">Complete your payment to activate account</p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
            <div style="background:rgba(244,63,94,0.1); color:var(--accent); padding:14px; border-radius:12px; margin-bottom:20px; text-align:center; font-size:14px; border:1px solid rgba(244,63,94,0.2);">
                <i class="fas fa-exclamation-circle" style="margin-right:8px;"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Plan Summary -->
        <div style="background:linear-gradient(135deg, rgba(99,102,241,0.12), rgba(20,184,166,0.08)); border:1px solid rgba(99,102,241,0.25); border-radius:16px; padding:18px 20px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; font-size:16px;"><?php echo $selected_plan['name']; ?></div>
                <div style="font-size:13px; color:var(--text-muted); margin-top:3px;">
                    <i class="fas fa-exchange-alt" style="margin-right:4px; color:var(--secondary);"></i><?php echo $selected_plan['exchanges']; ?>
                </div>
            </div>
            <div style="font-size:28px; font-weight:800; color:var(--primary-bright);">
                <?php echo $plan; ?><span style="font-size:14px; color:var(--text-muted); font-weight:400;">rs</span>
            </div>
        </div>

        <form method="POST">
            <!-- Step 1: Choose Method -->
            <div style="margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <div class="step-badge">1</div>
                    <span style="font-weight:600; font-size:15px;">Choose Payment Method</span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <label class="method-card glass" style="display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                        <input type="radio" name="payment_method" value="JazzCash" required style="accent-color:var(--primary-bright);">
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#e6474d;">JazzCash</div>
                            <div style="font-size:11px; color:var(--text-muted);">Mobile Account</div>
                        </div>
                    </label>
                    <label class="method-card glass" style="display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                        <input type="radio" name="payment_method" value="EasyPaisa" style="accent-color:var(--primary-bright);">
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#44b549;">EasyPaisa</div>
                            <div style="font-size:11px; color:var(--text-muted);">Mobile Account</div>
                        </div>
                    </label>
                    <label class="method-card glass" style="display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                        <input type="radio" name="payment_method" value="Bank Transfer" style="accent-color:var(--primary-bright);">
                        <div>
                            <div style="font-weight:700; font-size:14px;">Bank Transfer</div>
                            <div style="font-size:11px; color:var(--text-muted);">Direct Transfer</div>
                        </div>
                    </label>
                    <label class="method-card glass" style="display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.08);">
                        <input type="radio" name="payment_method" value="SadaPay" style="accent-color:var(--primary-bright);">
                        <div>
                            <div style="font-weight:700; font-size:14px; color:#7c3aed;">SadaPay</div>
                            <div style="font-size:11px; color:var(--text-muted);">Digital Wallet</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Account Details -->
            <div style="margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <div class="step-badge">2</div>
                    <span style="font-weight:600; font-size:15px;">Send Payment To</span>
                </div>
                <div class="account-box" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px; font-family:sans-serif;">Account Number</div>
                        <div id="acc-number" style="color:var(--primary-bright); font-size:17px; font-weight:700;">03XX-XXXXXXX</div>
                        <div style="font-size:11px; color:var(--text-muted); font-family:sans-serif; margin-top:4px;">Account Name: SkillSwap Admin</div>
                    </div>
                    <button type="button" class="copy-btn" onclick="copyAccount()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:8px; padding:10px; background:rgba(245,158,11,0.08); border-radius:8px; border:1px solid rgba(245,158,11,0.15);">
                    <i class="fas fa-info-circle" style="color:var(--warning); margin-right:5px;"></i>
                    Send exactly <strong style="color:var(--warning);"><?php echo $plan; ?>rs</strong> with your registered name as reference.
                </div>
            </div>

            <!-- Step 3: Transaction ID -->
            <div style="margin-bottom:28px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <div class="step-badge">3</div>
                    <span style="font-weight:600; font-size:15px;">Enter Transaction ID</span>
                </div>
                <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN12345678" required
                       style="font-family:monospace; letter-spacing:1px;"
                       value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>">
                <div style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                    Find the Transaction ID in your JazzCash / EasyPaisa SMS confirmation.
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:15px; font-size:16px;">
                <i class="fas fa-shield-alt" style="margin-right:8px;"></i>Submit Payment for Verification
            </button>
        </form>

        <div style="text-align:center; margin-top:20px; font-size:13px; color:var(--text-muted);">
            <i class="fas fa-lock" style="margin-right:5px; color:var(--success);"></i>
            Your account will be activated after admin verifies your payment.
        </div>

    </div>
    <?php endif; ?>

    <script src="assets/js/animations.js"></script>
    <script>
        function copyAccount() {
            const text = document.getElementById('acc-number').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; }, 2000);
            });
        }
    </script>
</body>
</html>
