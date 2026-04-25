<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT email, reset_token, reset_token_expiry FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Users:\n";
print_r($users);
