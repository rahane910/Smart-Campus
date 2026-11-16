<?php
include("../config/db.php");

$message = "";
$message_type = "";

if(isset($_POST['register_faculty'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $faculty_code = trim($_POST['faculty_code']);

    $valid_code = "FACULTY2026";

    // Check empty fields
    if(empty($name) || empty($email) || empty($password_input)){
        $message = "❌ Please fill all required fields.";
        $message_type = "error";
    }

    elseif($password_input != $confirm_password){
        $message = "❌ Passwords do not match!";
        $message_type = "error";
    }

    elseif($faculty_code !== $valid_code){
        $message = "❌ Invalid Faculty Verification Key!";
        $message_type = "error";
    }

    else{

        // Check email already exists
        $check_email = $conn->query("SELECT * FROM faculty WHERE email='$email'");

        if($check_email->num_rows > 0){
            $message = "❌ Email already registered!";
            $message_type = "error";
        }

        else{

            $password = password_hash($password_input, PASSWORD_DEFAULT);

            $faculty_id = trim($_POST['faculty_id']);
            $department = trim($_POST['department']);
            $qualification = trim($_POST['qualification']);
            $experience = trim($_POST['experience']);
            $joining_date = trim($_POST['joining_date']);
            $mobile = trim($_POST['mobile']);
            $designation = trim($_POST['designation']);
            $specialization = trim($_POST['specialization']);
            $gender = trim($_POST['gender']);
            $dob = trim($_POST['dob']);

            $photo_name = "default.png";

            if(isset($_FILES['photo']) && $_FILES['photo']['name'] != ""){

                $file_name = $_FILES['photo']['name'];
                $tmp_name = $_FILES['photo']['tmp_name'];

                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];

                if(in_array($ext, $allowed)){
                    $photo_name = time() . "_" . $file_name;
                    move_uploaded_file($tmp_name, "../uploads/" . $photo_name);
                }
            }

            $sql = "INSERT INTO faculty
            (name,email,password,faculty_id,department,qualification,experience,joining_date,mobile,designation,specialization,gender,dob,photo)
            VALUES
            ('$name','$email','$password','$faculty_id','$department','$qualification','$experience','$joining_date','$mobile','$designation','$specialization','$gender','$dob','$photo_name')";

            if($conn->query($sql)){
                $message = "✅ Faculty Registered Successfully!";
                $message_type = "success";

                // optional redirect
                header("refresh:2;url=login.php");
            } 
            else{
                $message = "❌ Database Error: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Registration</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:30px;
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #2563eb, #0ea5e9);
            background-size:400% 400%;
            animation: gradientMove 12s ease infinite;
        }

        @keyframes gradientMove{
            0%{ background-position:0% 50%; }
            50%{ background-position:100% 50%; }
            100%{ background-position:0% 50%; }
        }

        .form-box{
            width:100%;
            max-width:950px;
            background:rgba(255,255,255,0.12);
            backdrop-filter:blur(18px);
            border-radius:24px;
            padding:40px;
            box-shadow:0 15px 40px rgba(0,0,0,0.25);
            color:white;
        }

        h2{
            text-align:center;
            font-size:34px;
            margin-bottom:8px;
        }

        .subtitle{
            text-align:center;
            margin-bottom:28px;
        }

        .message{
    text-align:center;
    margin-bottom:20px;
    padding:14px;
    border-radius:10px;
    font-weight:600;
}

.message.success{
    background:rgba(34,197,94,0.2);
    border:1px solid #22c55e;
}

.message.error{
    background:rgba(239,68,68,0.2);
    border:1px solid #ef4444;
}

        .profile-upload{
            text-align:center;
            margin-bottom:28px;
        }

        .profile-image{
            width:110px;
            height:110px;
            border-radius:50%;
            object-fit:cover;
            cursor:pointer;
            border:4px solid white;
        }

        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        }

        .input-group{
            display:flex;
            flex-direction:column;
        }

        .input-group label{
            margin-bottom:7px;
            font-size:14px;
            font-weight:600;
        }

        .input-group input,
        .input-group select{
            width:100%;
            padding:14px;
            border:none;
            border-radius:14px;
            outline:none;
        }

        .btn{
            width:100%;
            margin-top:28px;
            padding:15px;
            border:none;
            border-radius:14px;
            background:white;
            color:#1e3a8a;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
        }

        .login-link{
            text-align:center;
            margin-top:18px;
        }

        .login-link a{
            color:white;
            text-decoration:none;
        }

        @media(max-width:768px){
            .grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>🎓 Faculty Registration</h2>
    <p class="subtitle">Smart Campus Management System</p>

   <?php if($message != ""){ ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
    </div>
<?php } ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="profile-upload">
            <label for="photoInput">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png"
                     id="previewImage"
                     class="profile-image">
            </label>

            <input type="file"
                   id="photoInput"
                   name="photo"
                   accept="image/*"
                   hidden
                   required
                   onchange="previewPhoto(event)">
        </div>

        <div class="grid">

            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <div class="input-group">
                <label>Faculty ID</label>
                <input type="text" name="faculty_id" required>
            </div>

            <div class="input-group">
                <label>Faculty Verification Key</label>
                <input type="password" name="faculty_code" required placeholder="Enter Secret Key">
            </div>

            <div class="input-group">
                <label>Department</label>
                <select name="department" required>
                    <option>Computer Science</option>
                    <option>Commerce</option>
                    <option>Arts</option>
                </select>
            </div>

            <div class="input-group">
                <label>Qualification</label>
                <input type="text" name="qualification">
            </div>

            <div class="input-group">
                <label>Experience</label>
                <input type="text" name="experience">
            </div>

            <div class="input-group">
                <label>Designation</label>
                <input type="text" name="designation">
            </div>

            <div class="input-group">
                <label>Specialization</label>
                <input type="text" name="specialization">
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
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="input-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" pattern="[0-9]{10}" maxlength="10">
            </div>

        </div>

        <button type="submit" name="register_faculty" class="btn">
            Register Faculty
        </button>

        <div class="login-link">
            <a href="login.php">Already registered? Login here</a>
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