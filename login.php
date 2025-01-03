<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email']);
$password = trim($data['password']);

// Load users from the JSON file
$users = json_decode(file_get_contents('users.json'), true) ?: [];

// Check if the email exists in the system
if (!isset($users[$email])) {
    echo json_encode(['success' => false, 'message' => 'Gmail not registered.']);
    exit;
}

// Check if the account is verified
if (!$users[$email]['verified']) {
    echo json_encode(['success' => false, 'message' => 'Please verify your Gmail before logging in.']);
    exit;
}

// Check if the password is correct
if ($users[$email]['password'] !== $password) {
    echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    exit;
}

// If all checks pass, start a session and redirect
$_SESSION['logged_in'] = true;
$_SESSION['email'] = $email;

echo json_encode(['success' => true, 'message' => 'Login successful.']);
?>
