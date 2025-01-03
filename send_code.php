<?php
$api_key = 'c0eea515d0e0d38ebd54f6e0f5ace75d'; // Your Mailjet API Key
$api_secret = 'b4149df6e31434a41fe7e03f37009f0a'; // Your Mailjet Secret Key

// Get data from the frontend
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email']);
$username = trim($data['username']);
$password = trim($data['password']);

// Password validation regex
$passwordRegex = "/^(?=(.*[a-zA-Z]){3})(?=(.*\d){3})(?=(.*[!@#$_&+\-*]){1}).{7,}$/";

if (!preg_match($passwordRegex, $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 7 characters long, contain 3 words, 3 numbers, and 1 special character.']);
    exit;
}

// Fetch existing user data
$users = json_decode(file_get_contents('users.json'), true) ?: [];

// Check if the email is already registered
if (isset($users[$email])) {
    echo json_encode(['success' => false, 'message' => 'This Gmail is already registered. Please use a different one.']);
    exit;
}

// Generate a random 6-digit authentication code
$auth_code = random_int(100000, 999999);

// Save the timestamp for the authentication code
$timestamp = time(); // Current timestamp

// Save user data and auth code to a temporary file (e.g., users.json)
$users[$email] = [
    'username' => $username,
    'password' => $password,
    'auth_code' => $auth_code,
    'timestamp' => $timestamp // Add timestamp for 30-second validity
];
file_put_contents('users.json', json_encode($users));

// Send the authentication code to the user's email using Mailjet
$data = [
    'Messages' => [
        [
            'From' => [
                'Email' => "adeimran.yaqub@gmail.com", // Your email
                'Name' => "Adiy Tech"
            ],
            'To' => [
                [
                    'Email' => $email,
                    'Name' => $username
                ]
            ],
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
    echo json_encode(['success' => true, 'message' => 'Authentication code sent to your Gmail.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send authentication code.']);
}
?>