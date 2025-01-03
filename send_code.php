<?php
$api_key = 'c0eea515d0e0d38ebd54f6e0f5ace75d';
$api_secret = 'b4149df6e31434a41fe7e03f37009f0a';

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email']);
$username = trim($data['username']);
$password = trim($data['password']);

$passwordRegex = "/^(?=(.*[a-zA-Z]){3})(?=(.*\d){3})(?=(.*[!@#$_&+\-*]){1}).{7,}$/";
if (!preg_match($passwordRegex, $password)) {
    echo json_encode(['success' => false, 'message' => 'Invalid password format.']);
    exit;
}

$users = json_decode(file_get_contents('users.json'), true) ?: [];
if (isset($users[$email])) {
    echo json_encode(['success' => false, 'message' => 'This Gmail is already registered.']);
    exit;
}

$auth_code = random_int(100000, 999999);
$timestamp = time();

$users[$email] = [
    'username' => $username,
    'password' => $password,
    'auth_code' => $auth_code,
    'timestamp' => $timestamp
];
file_put_contents('users.json', json_encode($users));

$data = [
    'Messages' => [
        [
            'From' => ['Email' => "adeimran.yaqub@gmail.com", 'Name' => "Adiy Tech"],
            'To' => [['Email' => $email, 'Name' => $username]],
            'Subject' => "Your Authentication Code",
            'TextPart' => "Your authentication code is $auth_code",
            'HTMLPart' => "<p>Your authentication code is: <strong>$auth_code</strong></p>"
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.mailjet.com/v3.1/send');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$api_secret");

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo json_encode(['success' => true, 'message' => 'Code sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send code.']);
}
?>
