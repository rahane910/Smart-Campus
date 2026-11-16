<?php
session_start();

$message = "";

if(isset($_POST['verify'])){
    $entered_otp = trim($_POST['otp']);

    if(isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']){
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "❌ Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <h2>Verify OTP</h2>

    <?php if($message != "") echo $message; ?>

    <form method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit" name="verify">Verify OTP</button>
    </form>
</body>
</html>