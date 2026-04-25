<?php
session_start();
require_once 'includes/db.php';

// Must come from registration flow
if (!isset($_SESSION['pending_user_id']) || !isset($_SESSION['pending_plan'])) {
    header('Location: register.php');
    exit;
}

$user_id = (int)$_SESSION['pending_user_id'];
$plan    = $_SESSION['pending_plan'];

// Fetch user name for personalization
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_name = $user ? $user['name'] : 'User';

// Plan details
$plans = [
    '199' => [
        'name'      => 'Basic Plan',
        'exchanges' => '10 Skill Exchanges / month',
        'icon'      => 'fa-seedling',
        'color'     => '#818cf8',
        'gradient'  => 'linear-gradient(135deg,#6366f1,#4f46e5)',
    ],
    '299' => [
        'name'      => 'Standard Plan',
        'exchanges' => '25 Skill Exchanges / month',
        'icon'      => 'fa-bolt',
        'color'     => '#14b8a6',
        'gradient'  => 'linear-gradient(135deg,#14b8a6,#0891b2)',
    ],
    '499' => [
        'name'      => 'Premium Plan',
        'exchanges' => 'Unlimited Skill Exchanges',
        'icon'      => 'fa-crown',
        'color'     => '#f59e0b',
        'gradient'  => 'linear-gradient(135deg,#f59e0b,#d97706)',
    ],
];
$p = $plans[$plan];

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tx_id  = trim($_POST['transaction_id'] ?? '');
    $method = trim($_POST['payment_method'] ?? '');

    if (empty($tx_id) || empty($method)) {
        $error = "Please select a payment method and enter your Transaction ID.";
    } elseif (strlen($tx_id) < 6) {
        $error = "Transaction ID must be at least 6 characters.";
    } else {
        try {
            // Auto-create columns if missing (runs only once)
            $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('subscription_plan',  $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN subscription_plan INT DEFAULT 0");
            if (!in_array('exchange_limit',     $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN exchange_limit VARCHAR(20) DEFAULT '0'");
            if (!in_array('is_approved',        $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
            if (!in_array('payment_method',     $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL");
            if (!in_array('transaction_id',     $cols)) $pdo->exec("ALTER TABLE users ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL");

            $exchange_limit = ($plan == '199') ? '10' : (($plan == '299') ? '25' : 'unlimited');

            $stmt = $pdo->prepare("UPDATE users SET subscription_plan=?, exchange_limit=?, is_approved=0, payment_method=?, transaction_id=? WHERE id=?");
            $stmt->execute([$plan, $exchange_limit, $method, $tx_id, $user_id]);

            // Clear session
            unset($_SESSION['pending_user_id'], $_SESSION['pending_plan']);
            $success = true;

        } catch (PDOException $e) {
            $error = "Error saving payment. Please contact support.";
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
        .method-card { cursor: pointer; transition: all 0.25s; user-select: none; }
        .method-card:has(input:checked) {
            border-color: var(--primary-bright) !important;
            background: rgba(99,102,241,0.15) !important;
            box-shadow: 0 0 0 2px rgba(99,102,241,0.35);
            transform: translateY(-2px);
        }
        .method-card:hover { border-color: rgba(255,255,255,0.2) !important; transform: translateY(-1px); }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%; font-size: 13px;
            font-weight: 800; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; background: <?php echo $p['gradient']; ?>;
        }
        @keyframes bounceIn {
            0%   { transform: scale(0.5); opacity: 0; }
            70%  { transform: scale(1.1); }
            100% { transform: scale(1);   opacity: 1; }
        }
        .success-icon { animation: bounceIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275) forwards; }
        .account-num { font-size: 18px; font-weight: 800; letter-spacing: 2px; color: <?php echo $p['color']; ?>; }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; padding:40px 16px;">
<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<?php if ($success): ?>
<!-- ── SUCCESS ─────────────────────────────────── -->
<div class="glass reveal active" style="width:100%;max-width:460px;padding:56px 40px;text-align:center;position:relative;z-index:1;">
    <div class="success-icon" style="width:88px;height:88px;border-radius:50%;background:rgba(16,185,129,0.12);border:2px solid rgba(16,185,129,0.35);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;">
        <i class="fas fa-check" style="font-size:40px;color:#10b981;"></i>
    </div>
    <h2 style="font-size:26px;font-weight:800;margin-bottom:10px;">Payment Submitted!</h2>
    <p style="color:var(--text-muted);line-height:1.8;margin-bottom:28px;">
        Hi <strong style="color:var(--text-main);"><?php echo htmlspecialchars($user_name); ?></strong>, your payment is under review.<br>
        Account will be <strong style="color:<?php echo $p['color']; ?>;">activated within 24 hours</strong>.
    </p>
    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--glass-border);border-radius:14px;padding:16px 20px;margin-bottom:28px;text-align:left;">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span style="color:var(--text-muted);font-size:13px;">Plan Selected</span>
            <span style="font-weight:700;"><?php echo $p['name']; ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span style="color:var(--text-muted);font-size:13px;">Amount Paid</span>
            <span style="font-weight:800;color:<?php echo $p['color']; ?>;"><?php echo $plan; ?>rs</span>
        </div>
        <div style="display:flex;justify-content:space-between;">
            <span style="color:var(--text-muted);font-size:13px;">Skill Exchanges</span>
            <span style="font-weight:600;color:var(--secondary);"><?php echo $p['exchanges']; ?></span>
        </div>
    </div>
    <a href="login.php" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
        <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Go to Login
    </a>
</div>

<?php else: ?>
<!-- ── PAYMENT FORM ────────────────────────────── -->
<div class="glass reveal active" style="width:100%;max-width:520px;padding:44px 40px;position:relative;z-index:1;">

    <!-- Logo + progress -->
    <div style="text-align:center;margin-bottom:8px;">
        <a href="login.php" class="logo" style="text-decoration:none;font-size:26px;">SKILLSWAP</a>
    </div>

    <!-- Step indicators -->
    <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:32px;margin-top:16px;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--glass-border);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-check" style="font-size:12px;color:var(--success);"></i>
            </div>
            <span style="font-size:10px;color:var(--success);font-weight:600;">Account</span>
        </div>
        <div style="flex:1;height:2px;background:var(--glass-border);margin:0 8px;margin-bottom:18px;"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <div style="width:32px;height:32px;border-radius:50%;background:<?php echo $p['gradient']; ?>;display:flex;align-items:center;justify-content:center;box-shadow:0 0 12px rgba(99,102,241,0.4);">
                <i class="fas fa-credit-card" style="font-size:12px;color:white;"></i>
            </div>
            <span style="font-size:10px;color:var(--primary-bright);font-weight:700;">Payment</span>
        </div>
        <div style="flex:1;height:2px;background:var(--glass-border);margin:0 8px;margin-bottom:18px;"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--glass-border);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-user-check" style="font-size:12px;color:var(--text-muted);"></i>
            </div>
            <span style="font-size:10px;color:var(--text-muted);">Activation</span>
        </div>
    </div>

    <!-- Plan Banner (locked — from registration) -->
    <div style="background:<?php echo $p['gradient']; ?>;border-radius:16px;padding:18px 22px;margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 8px 24px -8px rgba(0,0,0,0.4);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i class="fas <?php echo $p['icon']; ?>" style="font-size:20px;color:white;"></i>
            </div>
            <div>
                <div style="font-weight:800;font-size:16px;color:white;"><?php echo $p['name']; ?></div>
                <div style="font-size:12px;color:rgba(255,255,255,0.75);margin-top:2px;">
                    <i class="fas fa-exchange-alt" style="margin-right:4px;"></i><?php echo $p['exchanges']; ?>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:30px;font-weight:800;color:white;line-height:1;"><?php echo $plan; ?><span style="font-size:14px;font-weight:400;opacity:0.8;">rs</span></div>
            <div style="font-size:11px;color:rgba(255,255,255,0.7);">/ month</div>
        </div>
    </div>

    <?php if ($error): ?>
    <div style="background:rgba(244,63,94,0.1);color:var(--accent);padding:12px 16px;border-radius:12px;margin-bottom:20px;font-size:14px;border:1px solid rgba(244,63,94,0.2);">
        <i class="fas fa-exclamation-circle" style="margin-right:8px;"></i><?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST">

        <!-- STEP 1: Payment Method -->
        <div style="margin-bottom:22px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div class="step-num">1</div>
                <span style="font-weight:600;font-size:14px;">Choose Payment Method</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">

                <label class="method-card glass" style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);">
                    <input type="radio" name="payment_method" value="JazzCash" required style="accent-color:#e6474d;width:15px;height:15px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#e6474d;"><i class="fas fa-mobile-alt" style="margin-right:5px;"></i>JazzCash</div>
                        <div style="font-size:11px;color:var(--text-muted);">Mobile Account</div>
                    </div>
                </label>

                <label class="method-card glass" style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);">
                    <input type="radio" name="payment_method" value="EasyPaisa" style="accent-color:#44b549;width:15px;height:15px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#44b549;"><i class="fas fa-mobile-alt" style="margin-right:5px;"></i>EasyPaisa</div>
                        <div style="font-size:11px;color:var(--text-muted);">Mobile Account</div>
                    </div>
                </label>

                <label class="method-card glass" style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);">
                    <input type="radio" name="payment_method" value="Bank Transfer" style="accent-color:var(--primary-bright);width:15px;height:15px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;"><i class="fas fa-university" style="margin-right:5px;"></i>Bank Transfer</div>
                        <div style="font-size:11px;color:var(--text-muted);">Direct Transfer</div>
                    </div>
                </label>

                <label class="method-card glass" style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);">
                    <input type="radio" name="payment_method" value="SadaPay" style="accent-color:#7c3aed;width:15px;height:15px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#7c3aed;"><i class="fas fa-wallet" style="margin-right:5px;"></i>SadaPay</div>
                        <div style="font-size:11px;color:var(--text-muted);">Digital Wallet</div>
                    </div>
                </label>

            </div>
        </div>

        <!-- STEP 2: Send Payment To -->
        <div style="margin-bottom:22px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div class="step-num">2</div>
                <span style="font-weight:600;font-size:14px;">Send <?php echo $plan; ?>rs To This Account</span>
            </div>
            <div style="background:rgba(15,23,42,0.7);border:1px dashed rgba(255,255,255,0.15);border-radius:14px;padding:16px 18px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:0.5px;">Account Number</div>
                        <div class="account-num" id="acc-num">0300-1234567</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">Account Name: <strong style="color:var(--text-main);">SkillSwap Admin</strong></div>
                    </div>
                    <button type="button" onclick="copyAcc()" id="copy-btn"
                        style="background:rgba(255,255,255,0.06);border:1px solid var(--glass-border);color:var(--text-muted);border-radius:8px;padding:8px 12px;cursor:pointer;font-size:12px;transition:0.2s;">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:8px;padding:9px 14px;background:rgba(245,158,11,0.07);border-radius:8px;border:1px solid rgba(245,158,11,0.15);">
                <i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-right:5px;"></i>
                Send exactly <strong style="color:var(--warning);"><?php echo $plan; ?>rs</strong> — use your full name as payment reference.
            </div>
        </div>

        <!-- STEP 3: Transaction ID -->
        <div style="margin-bottom:26px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <div class="step-num">3</div>
                <span style="font-weight:600;font-size:14px;">Enter Transaction ID</span>
            </div>
            <input type="text" name="transaction_id" class="form-control" required
                   placeholder="e.g.  TXN-20250425-12345"
                   style="font-family:monospace;letter-spacing:1px;"
                   value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>">
            <div style="font-size:12px;color:var(--text-muted);margin-top:7px;">
                <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                Find the Transaction ID in your JazzCash / EasyPaisa SMS or app receipt.
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:15px;font-size:15px;letter-spacing:0.3px;">
            <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Payment for Verification
        </button>
    </form>

    <div style="text-align:center;margin-top:18px;font-size:12px;color:var(--text-muted);">
        <i class="fas fa-lock" style="color:var(--success);margin-right:4px;"></i>
        Your account will be activated after admin verifies your payment (within 24h).
    </div>
</div>
<?php endif; ?>

<script src="assets/js/animations.js"></script>
<script>
function copyAcc() {
    navigator.clipboard.writeText(document.getElementById('acc-num').innerText).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.style.color = '#10b981';
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; btn.style.color = ''; }, 2000);
    });
}
</script>
</body>
</html>
