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
$name = $email = $faculty_id = $department = $qualification = "";
$experience = $joining_date = $mobile = $designation = "";
$specialization = $gender = $dob = "";
$faculty_code = "";
$password_input = "";

/* VALID KEY */
$valid_code = "FACULTY2026";

/* =========================
   FORM SUBMIT
========================= */
if (isset($_POST['add_faculty'])) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $faculty_id = trim($_POST['faculty_id'] ?? '');
    $department = $_POST['department'] ?? '';
    $qualification = trim($_POST['qualification'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $joining_date = $_POST['joining_date'] ?? '';
    $mobile = trim($_POST['mobile'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';

    $faculty_code = $_POST['faculty_code'] ?? '';
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

    if ($faculty_id == "") {
        $errors['faculty_id'] = "Faculty ID required";
    }

    if ($department == "") {
        $errors['department'] = "Select department";
    }

    if ($faculty_code !== $valid_code) {
        $errors['faculty_code'] = "Invalid verification key";
    }

    if (strlen($password_input) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }

    if ($password_input !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors['mobile'] = "Invalid mobile number";
    }

    /* =========================
       INSERT
    ========================= */
    if (empty($errors)) {

        $check = $conn->prepare("SELECT id FROM faculty WHERE email=?");
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
                    $photo = "FAC_" . time() . "." . $ext;
                    move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/" . $photo);
                }
            }

            $stmt = $conn->prepare("
                INSERT INTO faculty
                (name,email,password,faculty_id,department,qualification,experience,
                joining_date,mobile,designation,specialization,gender,dob,photo)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "ssssssssssssss",
                $name,$email,$password,$faculty_id,$department,$qualification,$experience,
                $joining_date,$mobile,$designation,$specialization,$gender,$dob,$photo
            );

            if ($stmt->execute()) {
                $success = "Faculty registered successfully!";

                /* CLEAR FORM */
                $name = $email = $faculty_id = $department = "";
                $qualification = $experience = $joining_date = "";
                $mobile = $designation = $specialization = "";
                $gender = $dob = "";
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
<title>Add Faculty</title>

<!DOCTYPE html>
<html>
<head>
<title>Add Faculty</title>

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
    padding:25px;
    background: radial-gradient(circle at top, #1e3a8a, #0f172a, #020617);
}

/* MAIN CARD */
.container{
    width:100%;
    max-width:1100px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(20px);
    border-radius:24px;
    padding:35px;
    box-shadow:0 20px 60px rgba(0,0,0,0.5);
    color:white;
    border:1px solid rgba(255,255,255,0.15);
}

/* TITLE */
h2{
    text-align:center;
    font-size:28px;
    margin-bottom:8px;
}

.subtitle{
    text-align:center;
    font-size:14px;
    opacity:0.8;
    margin-bottom:25px;
}

/* SUCCESS */
.success{
    background:rgba(34,197,94,0.15);
    border:1px solid #22c55e;
    padding:12px;
    border-radius:10px;
    text-align:center;
    margin-bottom:15px;
}

/* PHOTO */
.photo-box{
    text-align:center;
    margin-bottom:20px;
}

.photo-box img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid white;
    cursor:pointer;
    transition:0.3s;
}

.photo-box img:hover{
    transform:scale(1.05);
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

/* INPUTS */
input,select{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:none;
    outline:none;
    font-size:14px;
    background:rgba(255,255,255,0.9);
}

/* LABEL STYLE */
label{
    font-size:13px;
    margin-bottom:6px;
    display:block;
    font-weight:600;
    opacity:0.9;
}

/* FIELD WRAPPER */
.field{
    display:flex;
    flex-direction:column;
}

/* ERROR */
.error{
    font-size:12px;
    color:#ff6b6b;
    margin-top:5px;
}

/* SECTION */
.section{
    grid-column:span 2;
    margin-top:10px;
    padding:10px 15px;
    background:rgba(255,255,255,0.08);
    border-left:4px solid #60a5fa;
    border-radius:10px;
    font-weight:600;
}

/* BUTTON */
button{
    width:100%;
    margin-top:25px;
    padding:15px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    color:white;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
}

/* BACK */
.back{
    display:block;
    text-align:center;
    margin-top:18px;
    color:white;
    text-decoration:none;
    opacity:0.8;
}

.back:hover{
    opacity:1;
}

/* RESPONSIVE */
@media(max-width:768px){
    .grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<div class="container">

<h2>🎓 Add Faculty</h2>
<p class="subtitle">Smart Campus Admin Panel</p>

<?php if($success): ?>
<div class="success">✅ <?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<!-- PHOTO -->
<div class="photo-box">
    <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png"
         id="previewImage">

    <input type="file" name="photo" accept="image/*"
           onchange="previewPhoto(event)">
</div>

<div class="grid">

<div class="section">👤 Personal Details</div>

<div class="field">
<label>Name</label>
<input type="text" name="name" value="<?= $name ?>">
<span class="error"><?= $errors['name'] ?? '' ?></span>
</div>

<div class="field">
<label>Email</label>
<input type="email" name="email" value="<?= $email ?>">
<span class="error"><?= $errors['email'] ?? '' ?></span>
</div>

<div class="field">
<label>Password</label>
<input type="password" name="password">
<span class="error"><?= $errors['password'] ?? '' ?></span>
</div>

<div class="field">
<label>Confirm Password</label>
<input type="password" name="confirm_password">
<span class="error"><?= $errors['confirm_password'] ?? '' ?></span>
</div>

<div class="section">🏢 Academic Details</div>

<div class="field">
<label>Faculty ID</label>
<input type="text" name="faculty_id" value="<?= $faculty_id ?>">
<span class="error"><?= $errors['faculty_id'] ?? '' ?></span>
</div>

<div class="field">
<label>Verification Key</label>
<input type="password" name="faculty_code">
<span class="error"><?= $errors['faculty_code'] ?? '' ?></span>
</div>

<div class="field">
<label>Department</label>
<select name="department">
<option value="">Select</option>
<option>Computer Science</option>
<option>Commerce</option>
<option>Arts</option>
</select>
<span class="error"><?= $errors['department'] ?? '' ?></span>
</div>

<div class="field">
<label>Qualification</label>
<input type="text" name="qualification">
</div>

<div class="field">
<label>Experience</label>
<input type="text" name="experience">
</div>

<div class="field">
<label>Designation</label>
<input type="text" name="designation">
</div>

<div class="field">
<label>Specialization</label>
<input type="text" name="specialization">
</div>

<div class="field">
<label>Joining Date</label>
<input type="date" name="joining_date">
</div>

<div class="field">
<label>DOB</label>
<input type="date" name="dob">
</div>

<div class="field">
<label>Gender</label>
<select name="gender">
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>
</div>

<div class="field">
<label>Mobile</label>
<input type="text" name="mobile">
<span class="error"><?= $errors['mobile'] ?? '' ?></span>
</div>

</div>

<button type="submit" name="add_faculty">➕ Add Faculty</button>

</form>

<a class="back" href="../../admin_dashboard.php">⬅ Back to Dashboard</a>

</div>

<script>
function previewPhoto(event){
    document.getElementById("previewImage").src =
        URL.createObjectURL(event.target.files[0]);
}
</script>

</body>
</html>