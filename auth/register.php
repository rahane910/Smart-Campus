<?php
include("../config/db.php");

$message = "";
$messageType = "";
$departments = $conn->query("SELECT * FROM departments");

if(isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? "";
    $admission_date = $_POST['admission_date'] ?? "";

    // Password validation
    if($password_input != $confirm_password){
        $message = "❌ Passwords do not match!";
        $messageType = "error";
    } else {

        $password = password_hash($password_input, PASSWORD_DEFAULT);

        $role = $_POST['role'];
        $department = $_POST['department'];
        $mobile = trim($_POST['mobile']);
        $roll_no = trim($_POST['roll_no']);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $address = trim($_POST['address']);
        $course = $_POST['course'];
        $semester = $_POST['semester'];


        $faculty_id = $_POST['faculty_id'] ?? "";
        $qualification = $_POST['qualification'] ?? "";
        $joining_date = $_POST['joining_date'] ?? "";

        $admin_id = $_POST['admin_id'] ?? "";
        $access_level = $_POST['access_level'] ?? "";
        // Check existing email
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");

        // Photo upload
        $photo_name = "default.png";
        if(isset($_FILES['photo']) && $_FILES['photo']['name'] != ""){
            $file_name = $_FILES['photo']['name'];
            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_size = $_FILES['photo']['size'];
            
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if(in_array($ext, $allowed)){
                $photo_name = time() . "_" . $file_name;
                $target = "../uploads/" . $photo_name;
                
                move_uploaded_file($tmp_name, $target);
                } else {
                    $message = "❌ Only JPG, JPEG, PNG, WEBP files allowed!";
                    $messageType = "error";
                    }
                }

        if($check->num_rows > 0){
            $message = "❌ Email already registered!";
            $messageType = "error";
        } else {

           $sql = "INSERT INTO users
(name,email,password,role,department_id,mobile,roll_no,dob,gender,address,course,semester,admission_date,photo,faculty_id,qualification,joining_date,admin_id,access_level)
VALUES
('$name','$email','$password','$role','$department','$mobile','$roll_no','$dob','$gender','$address','$course','$semester','$admission_date','$photo_name','$faculty_id','$qualification','$joining_date','$admin_id','$access_level')";

            if($conn->query($sql)){
                $message = "✅ Registration Successful! Redirecting to login page...";
                $messageType = "success";

                header("refresh:2;url=login.php");
            } else {
                $message = "❌ Error: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
            overflow:auto;
            padding:10px;
            background: linear-gradient(-45deg, #667eea, #764ba2, #6dd5ed, #2193b0);
            background-size:400% 400%;
            animation: gradientBG 10s ease infinite;
        }

        @keyframes gradientBG{
            0%{background-position:0% 50%;}
            50%{background-position:100% 50%;}
            100%{background-position:0% 50%;}
        }

        .register-box{
            width:95%;
            max-width:1000px;
            max-height:95vh;
            overflow:auto;
            padding:20px;
            border-radius:18px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(15px);
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
            color:white;
        }

        .profile-upload{
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            margin-bottom:15px;
        }

        .profile-image{
            width:90px;
            height:90px;
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

        h2{
            text-align:center;
            margin-bottom:5px;
            font-size:26px;
        }

        .subtitle{
            text-align:center;
            margin-bottom:15px;
            font-size:13px;
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:12px;
        }

        .input-group label{
            display:block;
            margin-bottom:4px;
            font-weight:bold;
            font-size:14px;
        }

        .input-group input,
        .input-group select,
        .input-group textarea{
            width:100%;
            padding:9px;
            border:none;
            border-radius:8px;
            outline:none;
            font-size:14px;
        }

        textarea{
            min-height:55px;
            resize:none;
        }

        .full-width{
            grid-column:span 2;
        }

        .register-btn{
            width:100%;
            margin-top:12px;
            padding:10px;
            border:none;
            border-radius:8px;
            background:white;
            color:#333;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
        }

        .link{
            text-align:center;
            margin-top:8px;
        }

        .link a{
            color:white;
            text-decoration:none;
            font-size:14px;
        }

        .message{
            margin-bottom:12px;
            padding:10px;
            border-radius:8px;
            text-align:center;
            font-weight:bold;
        }

        .success{
            background: rgba(0,255,0,0.2);
        }

        .error{
            background: rgba(255,0,0,0.2);
        }

        @media(max-width:900px){
            .form-grid{
                grid-template-columns:1fr;
            }

            .full-width{
                grid-column:span 1;
            }

            .profile-image{
                width:70px;
                height:70px;
            }
        }
    </style>
</head>
<body>

<div class="register-box">

    <div class="profile-upload">
        <label for="photoInput">
            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png"
                 id="previewImage"
                 alt="Profile"
                 class="profile-image">
        </label>
        <p>Upload Profile Photo</p>
    </div>

    <h2>Create Account</h2>
    <p class="subtitle">Register to access Smart Campus</p>

    <?php if($message != ""){ ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" id="photoInput" name="photo" accept="image/*" hidden onchange="previewPhoto(event)">
<div class="input-group">
    <label>Role</label>
    <select name="role" id="role" onchange="toggleFields()" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
        <option value="admin">Admin</option>
    </select>
</div>

<!-- STUDENT FIELDS -->
<div id="studentFields" style="display:none;">

    <div class="input-group">
        <label>Roll No</label>
        <input type="text" name="roll_no">
    </div>

    <div class="input-group">
        <label>Course</label>
        <select name="course">
            <option value="">Select Course</option>
            <option value="BCS">BCS</option>
            <option value="BCA">BCA</option>
            <option value="MCA">MCA</option>
        </select>
    </div>

    <div class="input-group">
        <label>Semester</label>
        <select name="semester">
            <option value="">Select Semester</option>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
            <option value="3">Semester 3</option>
            <option value="4">Semester 4</option>
            <option value="5">Semester 5</option>
            <option value="6">Semester 6</option>
        </select>
    </div>

    <div class="input-group">
        <label>Admission Date</label>
        <input type="date" name="admission_date">
    </div>
</div>

<!-- FACULTY FIELDS -->
<div id="facultyFields" style="display:none;">

    <div class="input-group">
        <label>Employee ID</label>
        <input type="text" name="faculty_id">
    </div>

    <div class="input-group">
        <label>Qualification</label>
        <input type="text" name="qualification">
    </div>

    <div class="input-group">
        <label>Joining Date</label>
        <input type="date" name="joining_date">
    </div>
</div>

<!-- ADMIN FIELDS -->
<div id="adminFields" style="display:none;">

    <div class="input-group">
        <label>Admin ID</label>
        <input type="text" name="admin_id">
    </div>

    <div class="input-group">
        <label>Access Level</label>
        <select name="access_level">
            <option value="full">Full Access</option>
            <option value="limited">Limited Access</option>
        </select>
    </div>
</div>
        <div class="form-grid">

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
                <label>Mobile Number</label>
                <input type="tel" name="mobile" pattern="[0-9]{10}" required>
            </div>

            <div class="input-group">
                <label>Roll No / Employee ID</label>
                <input type="text" name="roll_no" required>
            </div>

            <div class="input-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" required>
            </div>

            <div class="input-group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="input-group">
                <label>Course</label>
                <select name="course" required>
                    <option value="">Select Course</option>
                    <option value="BCS">BCS</option>
                    <option value="BCA">BCA</option>
                    <option value="MCA">MCA</option>
                </select>
            </div>

            <div class="input-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                    <option value="3">Semester 3</option>
                    <option value="4">Semester 4</option>
                    <option value="5">Semester 5</option>
                    <option value="6">Semester 6</option>
                </select>
            </div>

            <!-- <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
            </div> -->

            <div class="input-group">
                <label>Department</label>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <?php while($dept = $departments->fetch_assoc()) { ?>
                        <option value="<?php echo $dept['department_id']; ?>">
                            <?php echo $dept['department_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="input-group">
                <label>Admission Date</label>
                <input type="date" name="admission_date" required>
            </div>

            <div class="input-group full-width">
                <label>Address</label>
                <textarea name="address" required></textarea>
            </div>

        </div>

        <button type="submit" name="register" class="register-btn">
            Register
        </button>

        <div class="link">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </form>
</div>

<script>
function previewPhoto(event){
    let image = document.getElementById("previewImage");
    image.src = URL.createObjectURL(event.target.files[0]);
}

function toggleFields() {
    let role = document.getElementById("role").value;

    document.getElementById("studentFields").style.display = "none";
    document.getElementById("facultyFields").style.display = "none";
    document.getElementById("adminFields").style.display = "none";

    if(role === "student"){
        document.getElementById("studentFields").style.display = "contents";
    }
    else if(role === "faculty"){
        document.getElementById("facultyFields").style.display = "contents";
    }
    else if(role === "admin"){
        document.getElementById("adminFields").style.display = "contents";
    }
}
</script>

</body>
</html>