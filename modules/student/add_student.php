<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../../auth/login.php");
    exit();
}

/* =========================
   VARIABLES
========================= */
$errors = [];
$success = "";

/* VALUES */
$name = $email = $student_id = $roll_no = $prn_no = "";
$admission_year = $dob = $gender = $course = $semester = $department = "";
$mobile = $parent_mobile = $address = "";
$password_input = "";

/* =========================
   FORM SUBMIT
========================= */
if (isset($_POST['add_student'])) {

    /* INPUTS */
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $prn_no = trim($_POST['prn_no'] ?? '');

    $admission_year = trim($_POST['admission_year'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';

    $course = $_POST['course'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $department = $_POST['department'] ?? '';

    $mobile = trim($_POST['mobile'] ?? '');
    $parent_mobile = trim($_POST['parent_mobile'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $password_input = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    /* =========================
       VALIDATION
    ========================= */

    if (!preg_match("/^[a-zA-Z ]{3,50}$/", $name)) {
        $errors['name'] = "Enter valid name (3–50 letters)";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if ($student_id == "") {
        $errors['student_id'] = "Student ID required";
    }

    if ($roll_no == "") {
        $errors['roll_no'] = "Roll No required";
    }

    if ($prn_no == "") {
        $errors['prn_no'] = "PRN required";
    }

    if (!preg_match("/^[0-9]{4}$/", $admission_year)) {
        $errors['admission_year'] = "Invalid year (e.g. 2026)";
    }

    if ($dob == "") {
        $errors['dob'] = "DOB required";
    }

    if (!in_array($gender, ["Male","Female","Other"])) {
        $errors['gender'] = "Select gender";
    }

    if ($course == "") {
        $errors['course'] = "Select course";
    }

    if (!in_array($semester, [
        "Semester 1","Semester 2","Semester 3",
        "Semester 4","Semester 5","Semester 6"
    ])) {
        $errors['semester'] = "Select valid semester";
    }

    if ($department == "") {
        $errors['department'] = "Select department";
    }

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors['mobile'] = "Invalid mobile number";
    }

    if (!preg_match("/^[0-9]{10}$/", $parent_mobile)) {
        $errors['parent_mobile'] = "Invalid parent mobile number";
    }

    if (strlen($address) < 5) {
        $errors['address'] = "Address too short";
    }

    if (strlen($password_input) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }

    if ($password_input !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    /* =========================
       INSERT
    ========================= */
    if (empty($errors)) {

        $check = $conn->prepare("SELECT id FROM students WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors['email'] = "Email already exists";
        } else {

            $password = password_hash($password_input, PASSWORD_DEFAULT);
            $photo = "default.png";

            /* PHOTO UPLOAD */
            if (!empty($_FILES['photo']['name'])) {
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];

                if (in_array($ext, $allowed)) {
                    $photo = "STU_" . time() . "." . $ext;
                    move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/" . $photo);
                }
            }

            $stmt = $conn->prepare("
                INSERT INTO students
                (name,email,password,student_id,roll_no,prn_no,
                 admission_year,dob,gender,course,semester,department,
                 mobile,parent_mobile,address,photo)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "ssssssssssssssss",
                $name,$email,$password,$student_id,$roll_no,$prn_no,
                $admission_year,$dob,$gender,$course,$semester,$department,
                $mobile,$parent_mobile,$address,$photo
            );

            if ($stmt->execute()) {
                $success = "Student registered successfully!";

                /* CLEAR FORM */
                $name = $email = $student_id = $roll_no = $prn_no = "";
                $admission_year = $dob = $gender = $course = $semester = $department = "";
                $mobile = $parent_mobile = $address = "";
            } else {
                $errors['db'] = "Database error occurred";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Student</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    min-height:100vh;
    padding:30px;
    background:linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
}

/* MAIN CARD */
.container{
    max-width:1100px;
    margin:auto;
    padding:35px;
    border-radius:24px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(18px);
    box-shadow:0 20px 60px rgba(0,0,0,0.25);
    color:white;
    border:1px solid rgba(255,255,255,0.15);
}

/* TITLE */
h2{
    text-align:center;
    font-size:30px;
    margin-bottom:10px;
    font-weight:700;
}

.subtitle{
    text-align:center;
    margin-bottom:30px;
    opacity:0.85;
    font-size:14px;
}

/* SUCCESS */
.success-box{
    background:rgba(34,197,94,0.15);
    border:1px solid #22c55e;
    color:#dcfce7;
    padding:14px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:600;
    text-align:center;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

/* FIELD CARD */
.field{
    display:flex;
    flex-direction:column;
}

/* LABEL */
label{
    font-size:13px;
    margin-bottom:6px;
    font-weight:600;
    opacity:0.9;
}

/* INPUTS */
input,select,textarea{
    width:100%;
    padding:14px 16px;
    border:none;
    outline:none;
    border-radius:14px;
    font-size:14px;
    background:rgba(255,255,255,0.92);
    box-shadow:0 4px 14px rgba(0,0,0,0.08);
}

textarea{
    min-height:90px;
    resize:none;
}

/* FULL WIDTH */
.full{
    grid-column:span 2;
}

/* ERROR */
.error{
    color:#fecaca;
    font-size:12px;
    margin-top:5px;
}

/* SECTION TITLE */
.section-title{
    grid-column:span 2;
    padding:12px 16px;
    border-left:4px solid #93c5fd;
    background:rgba(255,255,255,0.08);
    border-radius:10px;
    font-weight:600;
    margin-top:8px;
}

/* BUTTON */
button{
    width:100%;
    margin-top:25px;
    padding:15px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:white;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition:0.3s ease;
    box-shadow:0 8px 20px rgba(37,99,235,0.35);
}

button:hover{
    transform:translateY(-2px);
}

/* BACK BUTTON */
.back-btn{
    display:block;
    text-align:center;
    padding:14px;
    margin-top:15px;
    border-radius:14px;
    background:rgba(255,255,255,0.15);
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}

.back-btn:hover{
    background:rgba(255,255,255,0.25);
}

/* RESPONSIVE */
@media(max-width:768px){
    .grid{
        grid-template-columns:1fr;
    }

    .full,
    .section-title{
        grid-column:span 1;
    }

    .container{
        padding:25px;
    }
}
</style>
</head>

<body>

<div class="container">

<h2>➕ Add Student</h2>

<?php if($success != ""): ?>
<div class="success-box">✅ <?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div class="grid">

<!-- NAME -->
<div>
<input type="text" name="name" placeholder="Full Name" value="<?= $name ?>">
<span class="error"><?= $errors['name'] ?? '' ?></span>
</div>

<!-- EMAIL -->
<div>
<input type="email" name="email" placeholder="Email" value="<?= $email ?>">
<span class="error"><?= $errors['email'] ?? '' ?></span>
</div>

<!-- STUDENT ID -->
<div>
<input type="text" name="student_id" placeholder="Student ID" value="<?= $student_id ?>">
<span class="error"><?= $errors['student_id'] ?? '' ?></span>
</div>

<!-- ROLL -->
<div>
<input type="text" name="roll_no" placeholder="Roll No" value="<?= $roll_no ?>">
<span class="error"><?= $errors['roll_no'] ?? '' ?></span>
</div>

<!-- PRN -->
<div>
<input type="text" name="prn_no" placeholder="PRN No" value="<?= $prn_no ?>">
<span class="error"><?= $errors['prn_no'] ?? '' ?></span>
</div>

<!-- YEAR -->
<div>
<input type="number" name="admission_year" placeholder="Admission Year" value="<?= $admission_year ?>">
<span class="error"><?= $errors['admission_year'] ?? '' ?></span>
</div>

<!-- DOB -->
<div>
<input type="date" name="dob" value="<?= $dob ?>">
<span class="error"><?= $errors['dob'] ?? '' ?></span>
</div>

<!-- GENDER -->
<div>
<select name="gender">
    <option value="">Gender</option>
    <option>Male</option>
    <option>Female</option>
    <option>Other</option>
</select>
<span class="error"><?= $errors['gender'] ?? '' ?></span>
</div>

<!-- COURSE -->
<div>
<select name="course">
    <option value="">Course</option>
    <option>BCS</option>
    <option>BCA</option>
    <option>MCA</option>
</select>
<span class="error"><?= $errors['course'] ?? '' ?></span>
</div>

<!-- SEMESTER -->
<div>
<select name="semester">
    <option value="">Semester</option>
    <option>Semester 1</option>
    <option>Semester 2</option>
    <option>Semester 3</option>
    <option>Semester 4</option>
    <option>Semester 5</option>
    <option>Semester 6</option>
</select>
<span class="error"><?= $errors['semester'] ?? '' ?></span>
</div>

<!-- DEPARTMENT -->
<div>
<select name="department">
    <option value="">Department</option>
    <option>Computer Science</option>
    <option>Commerce</option>
    <option>Arts</option>
</select>
<span class="error"><?= $errors['department'] ?? '' ?></span>
</div>

<!-- MOBILE -->
<div>
<input type="text" name="mobile" placeholder="Student Mobile" value="<?= $mobile ?>">
<span class="error"><?= $errors['mobile'] ?? '' ?></span>
</div>

<!-- PARENT -->
<div>
<input type="text" name="parent_mobile" placeholder="Parent Mobile" value="<?= $parent_mobile ?>">
<span class="error"><?= $errors['parent_mobile'] ?? '' ?></span>
</div>

<!-- PASSWORD -->
<div>
<input type="password" name="password" placeholder="Password">
<span class="error"><?= $errors['password'] ?? '' ?></span>
</div>

<!-- CONFIRM -->
<div>
<input type="password" name="confirm_password" placeholder="Confirm Password">
<span class="error"><?= $errors['confirm_password'] ?? '' ?></span>
</div>

<!-- ADDRESS -->
<div class="full">
<textarea name="address" placeholder="Address"><?= $address ?></textarea>
<span class="error"><?= $errors['address'] ?? '' ?></span>
</div>

<!-- PHOTO -->
<div class="full">
<input type="file" name="photo">
</div>

</div>

<button type="submit" name="add_student">Add Student</button>

<div style="margin-top:15px;">
    <a href="../../admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>
</form>

</div>

</body>
</html>