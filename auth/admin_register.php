<?php
include("../config/db.php");

$message = "";

if(isset($_POST['register_admin'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // confirm password check
    if($password_input != $confirm_password){
        $message = "❌ Passwords do not match!";
    } else {

        $password = password_hash($password_input, PASSWORD_DEFAULT);

        $admin_id = trim($_POST['admin_id']);
        $access_level = $_POST['access_level'];
        $office_location = trim($_POST['office_location']);
        $mobile = trim($_POST['mobile']);
        $designation = trim($_POST['designation']);
        $department = trim($_POST['department']);
        $joining_date = $_POST['joining_date'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];

        // upload photo FIRST
        $photo_name = "";

        if(isset($_FILES['photo']) && $_FILES['photo']['name'] != ""){
            $file_name = $_FILES['photo']['name'];
            $tmp_name = $_FILES['photo']['tmp_name'];

            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];

            if(in_array($ext,$allowed)){
                $photo_name = time() . "_" . $file_name;
                move_uploaded_file($tmp_name, "../uploads/" . $photo_name);
            } else {
                $message = "❌ Only JPG, PNG, WEBP allowed!";
            }
        }

        // duplicate email check
        $check = $conn->query("SELECT * FROM admins WHERE email='$email'");

        if($check->num_rows > 0){
            $message = "❌ Email already exists!";
        } else {

            $sql = "INSERT INTO admins
            (name,email,password,admin_id,access_level,office_location,mobile,designation,department,joining_date,gender,dob,photo)
            VALUES
            ('$name','$email','$password','$admin_id','$access_level','$office_location','$mobile','$designation','$department','$joining_date','$gender','$dob','$photo_name')";

           if($conn->query($sql)){
    $message = "✅ Admin Registered Successfully! Redirecting to login page...";
    header("refresh:2;url=login.php");
    exit();
} else {
    $message = "❌ Error: " . $conn->error;
}
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
            background: linear-gradient(-45deg, #667eea, #764ba2, #6dd5ed, #2193b0);
            background-size:400% 400%;
            animation: gradientBG 10s ease infinite;
        }

        @keyframes gradientBG{
            0%{background-position:0% 50%;}
            50%{background-position:100% 50%;}
            100%{background-position:0% 50%;}
        }
        .profile-upload{
    text-align:center;
    margin-bottom:20px;
}

.profile-image{
    width:100px;
    height:100px;
    border-radius:50%;
    border:3px solid white;
    object-fit:cover;
    cursor:pointer;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.profile-upload p{
    margin-top:8px;
    font-size:14px;
    font-weight:bold;
}

        .form-box{
            width:100%;
            max-width:850px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(15px);
            border-radius:20px;
            padding:35px;
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
            color:white;
        }

        h2{
            text-align:center;
            margin-bottom:10px;
            font-size:30px;
        }

        .subtitle{
            text-align:center;
            margin-bottom:25px;
            font-size:14px;
            opacity:0.9;
        }

        .message{
            text-align:center;
            margin-bottom:20px;
            padding:12px;
            border-radius:8px;
            font-weight:bold;
            background: rgba(0,255,0,0.15);
        }

        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .input-group label{
            display:block;
            margin-bottom:6px;
            font-weight:bold;
            font-size:14px;
        }

        .input-group input,
        .input-group select{
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            outline:none;
            font-size:14px;
        }

        .btn{
            width:100%;
            margin-top:25px;
            padding:14px;
            border:none;
            border-radius:10px;
            background:white;
            color:#333;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
            transition:0.3s;
        }

        .btn:hover{
            transform:translateY(-2px);
        }

        @media(max-width:768px){
            .grid{
                grid-template-columns:1fr;
            }

            .form-box{
                padding:25px;
            }
        }
        .login-link{
    text-align:center;
    margin-top:15px;
    font-size:14px;
}

.login-link a{
    color:white;
    font-weight:bold;
    text-decoration:none;
    margin-left:5px;
}

.login-link a:hover{
    text-decoration:underline;
}
    </style>
</head>
<body>

<div class="form-box">
    <h2>Admin Registration</h2>
    <p class="subtitle">Create admin account for Smart Campus</p>

    <?php if($message != ""){ ?>
        <div class="message"><?php echo $message; ?></div>
    <?php } ?>

    <div class="profile-upload">
    <label for="photoInput">
        <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png"
             id="previewImage"
             class="profile-image"
             alt="Profile">
    </label>

   
    <p>Upload Profile Photo</p>
</div>
    <form method="POST" enctype="multipart/form-data">

        <div class="grid">

         <input type="file"
           id="photoInput"
           name="photo"
           accept="image/*"
           hidden
           required
           onchange="previewPhoto(event)">

            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter full name" required>
            </div>

            <div class="input-group">
                <label>Official Email</label>
                <input type="email" name="email" placeholder="Enter official email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm password" required> 
            </div>

            <div class="input-group">
                <label>Admin ID</label>
                <input type="text" name="admin_id" placeholder="Enter admin ID" required>
            </div>

            <div class="input-group">
                <label>Access Level</label>
                <select name="access_level" required>
                    <option value="">Select Access Level</option>
                    <option>Super Admin</option>
                    <option>Department Admin</option>
                    <option>Accounts Admin</option>
                    <option>Exam Admin</option>
                </select>
            </div>

            <div class="input-group">
                <label>Designation</label>
                <input type="text" name="designation" placeholder="Enter designation">
            </div>

            <div class="input-group">
                <label>Department</label>
                <input type="text" name="department" placeholder="Enter department">
            </div>

            <div class="input-group">
                <label>Office Location</label>
                <input type="text" name="office_location" placeholder="Enter office location">
            </div>

            <div class="input-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" placeholder="Enter mobile number">
            </div>

            <div class="input-group">
                <label>Joining Date</label>
                <input type="date" name="joining_date">
            </div>

            <div class="input-group">
                <label>Date of Birth</label>
                <input type="date" name="dob">
            </div>

            <div class="input-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>

        </div>

        <button type="submit" name="register_admin" class="btn">
            Register Admin
        </button>
        <div class="login-link">
    Already have an account?
    <a href="login.php">Go to Login</a>
</div>

    </form>
</div>

<script>
function previewPhoto(event){
    let image = document.getElementById("previewImage");
    image.src = URL.createObjectURL(event.target.files[0]);
}
</script>
</body>
</html>