<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/db.php");
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if(isset($_POST['send_otp'])){

    $email = trim($_POST['email']);

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($result && $result->num_rows > 0){

        $otp = rand(100000, 999999);

        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_expiry'] = time() + 60;

        try{
            $mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

$mail->Username = 'yourgmail@gmail.com';
$mail->Password = 'abcdefghijklmnop';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('yourgmail@gmail.com', 'Smart Campus');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = 'OTP for Password Reset';
$mail->Body = "Your OTP is: <b>$otp</b>";

            $mail->send();

            header("Location: verify_otp.php");
            exit();

        } catch (Exception $e){
            $message = "❌ Mail Error: " . $mail->ErrorInfo;
        }

    } else {
        $message = "❌ Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>

<h2>Forgot Password</h2>

<?php
if($message != ""){
    echo "<p>$message</p>";
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Enter email" required>
    <button type="submit" name="send_otp">Send OTP</button>
</form>

</body>
</html>