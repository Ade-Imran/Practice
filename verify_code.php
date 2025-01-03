<?php
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email']);
$auth_code = trim($data['authCode']);

$users = json_decode(file_get_contents('users.json'), true) ?: [];

if (!isset($users[$email])) {
    echo json_encode(['success' => false, 'message' => 'Gmail not registered.']);
    exit;
}

if ($users[$email]['auth_code'] !== (int)$auth_code) {
    echo json_encode(['success' => false, 'message' => 'Invalid authentication code.']);
    exit;
}

if (time() - $users[$email]['timestamp'] > 600) { // 10-minute expiry
    echo json_encode(['success' => false, 'message' => 'Authentication code expired.']);
    exit;
}

// Mark user as verified
$users[$email]['verified'] = true;
unset($users[$email]['auth_code']);
unset($users[$email]['timestamp']);
file_put_contents('users.json', json_encode($users));

echo json_encode(['success' => true, 'message' => 'Verification successful.']);
?>
