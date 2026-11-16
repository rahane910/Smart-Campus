<?php
session_start();
include("../config/db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";
$selected_role = "admin";
$email_value = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $email_value = $email;

    $password = trim($_POST['password']);
    $login_role = strtolower(trim($_POST['login_role']));
    $selected_role = $login_role;

    $security_code = trim($_POST['security_code'] ?? '');

    $user = null;
    $table = "";

    $admin_key = "ADMIN@2026#SMART";
    $faculty_key = "FACULTY@2026";

    /* ROLE TABLE SELECT */
    if ($login_role == "admin") {

        if ($security_code != $admin_key) {
            $error = "❌ Invalid Admin Security Key!";
        }

        $table = "admins";

    } elseif ($login_role == "faculty") {

        if ($security_code != $faculty_key) {
            $error = "❌ Invalid Faculty Security Key!";
        }

        $table = "faculty";

    } else {
        $table = "students";
    }

    if (empty($error)) {

        $stmt = $conn->prepare("SELECT * FROM $table WHERE email=?");

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }

        /* PASSWORD CHECK */
        if (
            $user &&
            (
                $password === $user['password'] ||
                password_verify($password, $user['password'])
            )
        ) {

            /* =========================
               FIXED SESSION (IMPORTANT)
            ========================= */

            $_SESSION['role'] = $login_role;
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['photo'] = $user['photo'] ?? '';

            if ($login_role == "student") {
                $_SESSION['user_id'] = $user['student_id'] ?? $user['id'];
            } elseif ($login_role == "faculty") {
                $_SESSION['user_id'] = $user['faculty_id'] ?? $user['id'];
            } else {
                $_SESSION['user_id'] = $user['id'];
            }

            /* REDIRECT */
            if ($login_role == "admin") {
                header("Location: ../admin_dashboard.php");
            } elseif ($login_role == "faculty") {
                header("Location: ../faculty_dashboard.php");
            } else {
                header("Location: ../student_dashboard.php");
            }

            exit();

        } else {
            $error = "❌ Invalid Email or Password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Smart Campus Login</title>

    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
        body{
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#667eea,#764ba2);
        }

        .login-box{
            width:430px;
            padding:35px;
            border-radius:20px;
            background:rgba(255,255,255,0.15);
            backdrop-filter:blur(15px);
            color:white;
        }

        h2{text-align:center;margin-bottom:20px;}

        .tabs{display:flex;margin-bottom:20px;overflow:hidden;border-radius:10px;}
        .tab-btn{
            flex:1;padding:12px;border:none;cursor:pointer;
            background:rgba(255,255,255,0.2);color:white;font-weight:bold;
        }
        .active{background:white;color:black;}

        .input-group{margin-bottom:15px;}
        label{display:block;margin-bottom:5px;}
        input{
            width:100%;padding:10px;border:none;border-radius:8px;
        }

        .login-btn{
            width:100%;padding:12px;
            background:white;border:none;border-radius:10px;
            font-weight:bold;cursor:pointer;
        }

        .error{
            background:red;
            padding:10px;
            border-radius:8px;
            margin-bottom:10px;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>🎓 Smart Campus Login</h2>

    <?php if(!empty($error)){ ?>
        <div class="error"><?= $error ?></div>
    <?php } ?>

    <div class="tabs">
        <button type="button" class="tab-btn <?= ($selected_role=='admin')?'active':'' ?>" onclick="switchRole('admin', this)">Admin</button>
        <button type="button" class="tab-btn <?= ($selected_role=='faculty')?'active':'' ?>" onclick="switchRole('faculty', this)">Faculty</button>
        <button type="button" class="tab-btn <?= ($selected_role=='student')?'active':'' ?>" onclick="switchRole('student', this)">Student</button>
    </div>

    <form method="POST">

        <input type="hidden" name="login_role" id="login_role" value="<?= $selected_role ?>">

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email_value) ?>" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group" id="securityBox">
            <label id="keyLabel">Security Key</label>
            <input type="password" name="security_code">
        </div>

        <button class="login-btn" name="login">Login</button>

    </form>
</div>

<script>
function switchRole(role, btn){
    document.getElementById("login_role").value = role;

    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    let box = document.getElementById("securityBox");
    let label = document.getElementById("keyLabel");

    if(role === "admin"){
        box.style.display = "block";
        label.innerText = "Admin Security Key";
    }
    else if(role === "faculty"){
        box.style.display = "block";
        label.innerText = "Faculty Security Key";
    }
    else{
        box.style.display = "none";
    }
}
</script>

</body>
</html>