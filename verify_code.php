<?php
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$auth_code = trim($data['authCode']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Fetch users data
$userData = file_get_contents('users.json');
if ($userData === false) {
    echo json_encode(['success' => false, 'message' => 'Unable to read users data.']);
    exit;
}

$users = json_decode($userData, true);

if (isset($users[$email])) {
    $storedCode = $users[$email]['auth_code'];
    $storedTimestamp = $users[$email]['timestamp'];
    $currentTime = time();

    if ($storedCode == $auth_code && ($currentTime - $storedTimestamp) <= 30) {
        // Remove auth_code after successful verification
        unset($users[$email]['auth_code']);
        unset($users[$email]['timestamp']);
        
        // Save updated data
        file_put_contents('users.json', json_encode($users));
        echo json_encode(['success' => true, 'redirect' => 'index.html']);
    } elseif (($currentTime - $storedTimestamp) > 30) {
        echo json_encode(['success' => false, 'message' => 'Authentication code has expired.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid authentication code.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
}
?>