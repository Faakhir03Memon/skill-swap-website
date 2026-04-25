<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

// Get user info from URL
$uid = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
$plan = isset($_GET['plan']) ? intval($_GET['plan']) : 0;

if ($uid == 0 || $plan == 0) {
    header('Location: register.php');
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND payment_status = 'pending'");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: register.php');
    exit;
}

// Plan details
$plans = [
    199 => ['name' => 'Basic Plan', 'lectures' => 3, 'color' => '#10b981', 'icon' => '🌱'],
    299 => ['name' => 'Standard Plan', 'lectures' => 5, 'color' => '#7c3aed', 'icon' => '⚡'],
    499 => ['name' => 'Premium Plan', 'lectures' => 7, 'color' => '#f59e0b', 'icon' => '👑'],
];

$current_plan = $plans[$plan] ?? $plans[199];

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    $sender_name = $_POST['sender_name'] ?? '';
    $transaction_id = $_POST['transaction_id'] ?? '';

    if (empty($payment_method) || empty($sender_name) || empty($transaction_id)) {
        $error = "Please fill in all payment details.";
    } else {
        // Save payment record and update user status
        // Create payments table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount INT NOT NULL,
            payment_method VARCHAR(50),
            sender_name VARCHAR(100),
            transaction_id VARCHAR(100),
            status ENUM('pending','verified','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, payment_method, sender_name, transaction_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $plan, $payment_method, $sender_name, $transaction_id]);

        // Update user payment status
        $pdo->prepare("UPDATE users SET payment_status = 'paid' WHERE id = ?")->execute([$uid]);

        $success = true;
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
        .payment-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 900px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        @media (max-width: 768px) {
            .payment-container { grid-template-columns: 1fr; }
        }

        .order-summary {
            padding: 40px;
            text-align: center;
        }
        .plan-icon {
            font-size: 56px;
            margin-bottom: 20px;
            display: block;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .plan-price {
            font-size: 48px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-bright), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 10px 0;
        }
        .plan-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
            color: var(--text-muted);
        }
        .plan-feature i {
            color: var(--success);
            width: 20px;
        }

        .payment-form { padding: 40px; }
        
        .method-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        .method-tab {
            flex: 1;
            padding: 12px 8px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .method-tab.active {
            border-color: var(--primary-bright);
            background: rgba(124, 58, 237, 0.1);
            color: var(--primary-bright);
        }
        .method-tab:hover {
            border-color: var(--primary-bright);
        }
        .method-tab i {
            display: block;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .payment-info-box {
            background: rgba(124, 58, 237, 0.08);
            border: 1px dashed var(--primary-bright);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: center;
        }
        .payment-info-box .account-number {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary-bright);
            letter-spacing: 2px;
            margin: 8px 0;
            cursor: pointer;
        }
        .payment-info-box .account-name {
            font-size: 13px;
            color: var(--text-muted);
        }
        .copy-btn {
            font-size: 11px;
            background: rgba(124, 58, 237, 0.2);
            border: none;
            color: var(--primary-bright);
            padding: 4px 12px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 5px;
        }

        .success-screen {
            text-align: center;
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: white;
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <?php if ($success === true): ?>
    <!-- SUCCESS SCREEN -->
    <div class="glass reveal active success-screen">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2 style="font-size: 28px; margin-bottom: 12px;">Payment Submitted!</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px; line-height: 1.7;">
            Your payment of <strong style="color: var(--primary-bright);">₨<?php echo $plan; ?></strong> has been submitted successfully.<br>
            Your account will be activated once the admin verifies your payment.
        </p>
        <div class="glass" style="padding: 20px; margin-bottom: 30px; text-align: left; background: rgba(255,255,255,0.02);">
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Name</span>
                <span style="font-weight: 600;"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <span style="color: var(--text-muted);">Plan</span>
                <span style="font-weight: 600; color: var(--primary-bright);"><?php echo $current_plan['name']; ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                <span style="color: var(--text-muted);">Status</span>
                <span class="badge badge-warning" style="font-size: 11px;">Pending Approval</span>
            </div>
        </div>
        <a href="login.php" class="btn btn-primary" style="width: 100%; padding: 14px;">
            <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Go to Login
        </a>
    </div>

    <?php else: ?>
    <!-- PAYMENT FORM -->
    <div class="payment-container">
        <!-- Left: Order Summary -->
        <div class="glass reveal active order-summary">
            <span class="plan-icon"><?php echo $current_plan['icon']; ?></span>
            <h2 style="font-size: 22px; margin-bottom: 5px;"><?php echo $current_plan['name']; ?></h2>
            <div class="plan-price">₨<?php echo $plan; ?></div>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">per month</p>

            <div style="text-align: left;">
                <div class="plan-feature"><i class="fas fa-check-circle"></i> <?php echo $current_plan['lectures']; ?> Lectures per day</div>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> AI Skill Matching</div>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> Take Verified Exams</div>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> Earn Certificates</div>
                <?php if ($plan >= 299): ?>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> Priority Support</div>
                <?php endif; ?>
                <?php if ($plan >= 499): ?>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> Exclusive Workshops</div>
                <div class="plan-feature"><i class="fas fa-check-circle"></i> 1-on-1 Mentoring</div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px;">
                <p style="font-size: 12px; color: var(--text-muted);">Registering as</p>
                <p style="font-weight: 700; font-size: 16px;"><?php echo htmlspecialchars($user['name']); ?></p>
                <p style="font-size: 13px; color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <!-- Right: Payment Form -->
        <div class="glass reveal active payment-form">
            <h3 style="margin-bottom: 8px; font-size: 22px;">
                <i class="fas fa-lock" style="color: var(--primary-bright); margin-right: 8px;"></i>
                Complete Payment
            </h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 24px;">Send payment and fill in your details below.</p>

            <?php if($error): ?>
                <div style="background: rgba(244, 63, 94, 0.1); color: var(--accent); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 13px; border: 1px solid rgba(244, 63, 94, 0.2);">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Payment Method Tabs -->
            <div class="method-tabs">
                <button class="method-tab active" onclick="switchMethod('easypaisa')" id="tab-easypaisa">
                    <i class="fas fa-mobile-alt"></i> EasyPaisa
                </button>
                <button class="method-tab" onclick="switchMethod('jazzcash')" id="tab-jazzcash">
                    <i class="fas fa-money-bill-wave"></i> JazzCash
                </button>
                <button class="method-tab" onclick="switchMethod('bank')" id="tab-bank">
                    <i class="fas fa-university"></i> Bank
                </button>
            </div>

            <!-- Payment Account Info -->
            <div class="payment-info-box">
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Send <strong style="color: var(--primary-bright);">₨<?php echo $plan; ?></strong> to:</p>
                <div class="account-number" id="account-number" onclick="copyAccount()">0300-1234567</div>
                <div class="account-name" id="account-name">SkillSwap - Admin Account</div>
                <button class="copy-btn" onclick="copyAccount()"><i class="fas fa-copy"></i> Copy Number</button>
            </div>

            <form method="POST">
                <input type="hidden" name="payment_method" id="payment_method" value="easypaisa">
                
                <div class="form-group">
                    <label>Your Name (Sender)</label>
                    <input type="text" name="sender_name" class="form-control" placeholder="Name on your account" required>
                </div>
                <div class="form-group">
                    <label>Transaction ID / Reference</label>
                    <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN123456789" required>
                </div>
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; padding: 12px; margin-bottom: 20px;">
                    <p style="font-size: 12px; color: #f59e0b;">
                        <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
                        Payment bhejne ke baad Transaction ID yahan paste karein. Admin verify karne ke baad aapka account activate ho jayega.
                    </p>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>Submit Payment Proof
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
    const accounts = {
        easypaisa: { number: '0300-1234567', name: 'SkillSwap - EasyPaisa' },
        jazzcash:  { number: '0301-7654321', name: 'SkillSwap - JazzCash' },
        bank:      { number: 'PK36MEZN0001230012345678', name: 'SkillSwap - Meezan Bank' }
    };

    function switchMethod(method) {
        document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + method).classList.add('active');
        document.getElementById('account-number').textContent = accounts[method].number;
        document.getElementById('account-name').textContent = accounts[method].name;
        document.getElementById('payment_method').value = method;
    }

    function copyAccount() {
        const num = document.getElementById('account-number').textContent;
        navigator.clipboard.writeText(num).then(() => {
            const btn = document.querySelector('.copy-btn');
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy Number'; }, 2000);
        });
    }
    </script>
    <script src="assets/js/animations.js"></script>
</body>
</html>
