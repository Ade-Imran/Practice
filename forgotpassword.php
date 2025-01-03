<?php
// Start a session
session_start();

// Include PHPMailer (ensure PHPMailer is installed via Composer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Simulated users database
$users = [
    "user1@example.com" => "John Doe",
    "user2@example.com" => "Jane Doe"
];

// Handle actions
if (isset($_GET['action']) && $_GET['action'] === 'requestReset') {
    // Handle reset link request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];

        if (array_key_exists($email, $users)) {
            // Generate reset token and link
            $token = bin2hex(random_bytes(16));
            $_SESSION["resetToken_$email"] = $token;
            $resetLink = "http://localhost/resetPassword.php?action=resetPassword&email=" . urlencode($email) . "&token=" . urlencode($token);

            // Send email using PHPMailer
            $mail = new PHPMailer(true);

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'your-email@gmail.com'; // Replace with your email
                $mail->Password = 'your-email-password'; // Replace with your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email details
                $mail->setFrom('adeimran.yaqub@gmail.com', 'index2.html');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request";
                $mail->Body = "Hello " . $users[$email] . ",<br><br>Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a>";

                $mail->send();
                echo "A reset link has been sent to your email.";
            } catch (Exception $e) {
                echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: Email address not found.";
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'resetPassword') {
    // Display reset password form
    if (isset($_GET['email'], $_GET['token'])) {
        $email = $_GET['email'];
        $token = $_GET['token'];

        if (isset($_SESSION["resetToken_$email"]) && hash_equals($_SESSION["resetToken_$email"], $token)) {
            // Show form for password reset
            echo "
                <h2>Reset your password for $email</h2>
                <form method='POST' action='resetPassword.php?action=savePassword'>
                    <input type='hidden' name='email' value='$email'>
                    <input type='password' name='password' placeholder='Enter new password' required>
                    <button type='submit'>Reset Password</button>
                </form>
            ";
        } else {
            echo "Invalid or expired token.";
        }
    } else {
        echo "Invalid request.";
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'savePassword') {
    // Handle password saving
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Simulated saving of password (in real-world, save to the database)
        echo "Password for $email has been updated successfully!";
    }
} else {
    // Default page (reset request form)
    echo '
        <h2>Forgot Password</h2>
        <form method="POST" action="resetPassword.php?action=requestReset">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    ';
}
?>