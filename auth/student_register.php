<?php
include("../config/db.php");

$message = "";

if(isset($_POST['register_student'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password_input != $confirm_password){
        $message = "❌ Passwords do not match!";
    }
    else{

        // Check duplicate email
        $check_email = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $email_result = $check_email->get_result();

        if($email_result->num_rows > 0){
            $message = "❌ Email already exists! Please use another email.";
        }
        else{

            $password = password_hash($password_input, PASSWORD_DEFAULT);

            $student_id = trim($_POST['student_id']);
            $roll_no = trim($_POST['roll_no']);
            $prn_no = trim($_POST['prn_no']);
            $course = $_POST['course'];
            $semester = $_POST['semester'];
            $department = $_POST['department'];
           $admission_year = $_POST['admission_year'] ?? '';
$dob = $_POST['dob'] ?? '';
$gender = $_POST['gender'] ?? '';
            $mobile = trim($_POST['mobile']);
            $parent_mobile = trim($_POST['parent_mobile']);
            $address = trim($_POST['address']);

            // Profile upload
            $photo_name = "default.png";

            if(isset($_FILES['photo']) && $_FILES['photo']['name'] != ""){
                $file_name = $_FILES['photo']['name'];
                $tmp_name = $_FILES['photo']['tmp_name'];

                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];

                if(in_array($ext,$allowed)){
                    $photo_name = time() . "_" . $file_name;
                    move_uploaded_file($tmp_name, "../uploads/" . $photo_name);
                }
            }

            $stmt = $conn->prepare("INSERT INTO students
            (name,email,password,student_id,roll_no,prn_no,course,semester,department,admission_year,dob,gender,mobile,parent_mobile,address,photo)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

            $stmt->bind_param(
                "ssssssssssssssss",
                $name,
                $email,
                $password,
                $student_id,
                $roll_no,
                $prn_no,
                $course,
                $semester,
                $department,
                $admission_year,
                $dob,
                $gender,
                $mobile,
                $parent_mobile,
                $address,
                $photo_name
            );

            if($stmt->execute()){
                $message = "✅ Student Registered Successfully!";
            }
            else{
                $message = "❌ Registration Failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
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
            background:linear-gradient(135deg,#667eea,#764ba2,#6a11cb);
        }

        .form-box{
            width:100%;
            max-width:1000px;
            padding:40px;
            border-radius:24px;
            background:rgba(255,255,255,0.15);
            backdrop-filter:blur(18px);
            box-shadow:0 12px 35px rgba(0,0,0,0.25);
            color:white;
        }

        h2{
            text-align:center;
            margin-bottom:8px;
        }

        .subtitle{
            text-align:center;
            margin-bottom:25px;
        }

        .message{
            text-align:center;
            margin-bottom:20px;
            padding:12px;
            border-radius:8px;
            font-weight:bold;
            background:rgba(255,0,0,0.2);
        }

        .profile-upload{
            text-align:center;
            margin-bottom:25px;
        }

        .profile-image{
            width:100px;
            height:100px;
            border-radius:50%;
            cursor:pointer;
            object-fit:cover;
            border:3px solid white;
        }

        .section-title{
            margin:25px 0 15px;
            padding:10px;
            border-left:4px solid white;
            background:rgba(255,255,255,0.1);
            border-radius:8px;
            font-weight:bold;
        }

        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .input-group label{
            display:block;
            margin-bottom:6px;
        }

        .input-group input,
        .input-group select,
        .input-group textarea{
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            outline:none;
        }

        textarea{
            resize:none;
            height:80px;
        }

        .full-width{
            grid-column:span 2;
        }

        .btn{
            width:100%;
            margin-top:25px;
            padding:14px;
            border:none;
            border-radius:10px;
            background:white;
            color:#333;
            font-weight:bold;
            cursor:pointer;
        }

        .login-link{
            text-align:center;
            margin-top:15px;
        }

        .login-link a{
            color:white;
            text-decoration:none;
        }

        @media(max-width:768px){
            .grid{
                grid-template-columns:1fr;
            }

            .full-width{
                grid-column:span 1;
            }
        }
    </style>
</head>
<body>

<div class="form-box">

    <h2>🎓 Student Registration Portal</h2>
    <p class="subtitle">Create your Smart Campus account</p>

    <?php if($message != ""){ ?>
        <div class="message"><?php echo $message; ?></div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="profile-upload">
            <label for="photoInput">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png"
                     id="previewImage"
                     class="profile-image">
            </label>

            <input type="file" id="photoInput" name="photo" hidden required onchange="previewPhoto(event)">
            <p>Upload Profile Photo</p>
        </div>

        <div class="section-title">👤 Personal Details</div>
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
        </div>

        <div class="section-title">🎓 Academic Details</div>
        <div class="grid">
            <div class="input-group">
                <label>Student ID</label>
                <input type="text" name="student_id" required>
            </div>

            <div class="input-group">
                <label>Roll Number</label>
                <input type="text" name="roll_no" required>
            </div>

            <div class="input-group">
                <label>PRN Number</label>
                <input type="text" name="prn_no" required>
            </div>

            <div class="input-group">
                <label>Course</label>
                <select name="course" required>
                    <option>BCS</option>
                    <option>BCA</option>
                    <option>MCA</option>
                </select>
            </div>

            <div class="input-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option>Semester 1</option>
                    <option>Semester 2</option>
                    <option>Semester 3</option>
                    <option>Semester 4</option>
                    <option>Semester 5</option>
                    <option>Semester 6</option>
                </select>
            </div>

            <div class="input-group">
                <label>Department</label>
                <select name="department" required>
                    <option>Computer Science</option>
                    <option>Commerce</option>
                    <option>Arts</option>
                </select>
            </div>
        </div>

        <div class="section-title">📞 Contact Details</div>
        <div class="grid">
            <div class="input-group">
                <label>Student Mobile</label>
                <input type="tel" name="mobile">
            </div>

            <div class="input-group">
                <label>Parent Mobile</label>
                <input type="tel" name="parent_mobile">
            </div>

            <div class="input-group full-width">
                <label>Address</label>
                <textarea name="address"></textarea>
            </div>
        </div>

        <button type="submit" name="register_student" class="btn">
            Create Student Account
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