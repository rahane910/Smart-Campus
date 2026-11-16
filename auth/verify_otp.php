<?php
session_start();

$error = "";

if(isset($_POST['verify'])){

    $entered_otp = trim($_POST['otp']);

    if(!isset($_SESSION['admin_otp'])){
        $error = "❌ Session expired!";
    }
    elseif(time() - $_SESSION['otp_time'] > 120){
        $error = "❌ OTP expired!";
    }
    elseif($entered_otp == $_SESSION['admin_otp']){

        $user = $_SESSION['temp_user'];

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = "admin";
        $_SESSION['email'] = $user['email'];

        unset($_SESSION['admin_otp']);
        unset($_SESSION['temp_user']);
        unset($_SESSION['otp_time']);

        header("Location: ../admin_dashboard.php");
        exit();
    }
    else{
        $error = "❌ Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <style>
        body{
            font-family:Arial;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            background:linear-gradient(135deg,#667eea,#764ba2);
        }

        .box{
            width:350px;
            padding:30px;
            border-radius:15px;
            background:white;
            text-align:center;
        }

        input{
            width:100%;
            padding:12px;
            margin:15px 0;
            border:1px solid #ccc;
            border-radius:8px;
        }

        button{
            width:100%;
            padding:12px;
            border:none;
            background:#667eea;
            color:white;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
        }

        .error{
            color:red;
            margin-bottom:10px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Enter OTP</h2>

    <?php if($error){ ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="otp" maxlength="6" placeholder="Enter 6-digit OTP" required>
        <button type="submit" name="verify">Verify OTP</button>
    </form>
</div>

</body>
</html>