<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

if (!isset($_GET['id'])) {
    die("Invalid ID");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM faculty WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Faculty not found");
}

$row = $result->fetch_assoc();

/* UPDATE */
if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $mobile = $_POST['mobile'];
    $designation = $_POST['designation'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $specialization = $_POST['specialization'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $joining_date = $_POST['joining_date'];

    $photo = $row['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/faculty/" . $photo);
    }

    $stmt = $conn->prepare("
        UPDATE faculty 
        SET name=?, email=?, department=?, mobile=?, designation=?, qualification=?,
            experience=?, specialization=?, gender=?, dob=?, joining_date=?, photo=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssssssssssssi",
        $name,
        $email,
        $department,
        $mobile,
        $designation,
        $qualification,
        $experience,
        $specialization,
        $gender,
        $dob,
        $joining_date,
        $photo,
        $id
    );

    $stmt->execute();

    header("Location: manage_faculty.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Update Faculty</title>

<style>
body{
    font-family:Segoe UI;
    background:linear-gradient(135deg,#0f172a,#1e293b);
    margin:0;
    padding:30px;
    color:white;
}

.container{
    max-width:900px;
    margin:auto;
}

.card{
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(10px);
    padding:25px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

h2{
    text-align:center;
    margin-bottom:20px;
    color:#38bdf8;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}

input, select{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:none;
    outline:none;
    background:#0f172a;
    color:white;
    border:1px solid rgba(255,255,255,0.1);
}

input:focus, select:focus{
    border:1px solid #38bdf8;
}

label{
    font-size:13px;
    color:#94a3b8;
}

.full{
    grid-column:span 2;
}

.btn{
    padding:12px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
}

.update-btn{
    background:#16a34a;
    color:white;
}

.update-btn:hover{
    background:#15803d;
}

.back-btn{
    background:#334155;
    color:white;
    text-decoration:none;
    padding:12px;
    display:inline-block;
    border-radius:8px;
    margin-bottom:15px;
}

.photo-box{
    text-align:center;
}

.photo-box img{
    width:120px;
    height:120px;
    border-radius:12px;
    object-fit:cover;
    border:3px solid #38bdf8;
}
</style>
</head>

<body>

<div class="container">

<a href="manage_faculty.php" class="back-btn">⬅ Back</a>

<div class="card">

<h2>👨‍🏫 Update Faculty</h2>

<form method="POST" enctype="multipart/form-data">

<div class="photo-box full">
    <img src="../../uploads/faculty/<?= $row['photo'] ?>" id="preview">
    <br><br>
    <input type="file" name="photo" onchange="loadFile(event)">
</div>

<div class="form-grid">

<div>
<label>Name</label>
<input type="text" name="name" value="<?= $row['name'] ?>">
</div>

<div>
<label>Email</label>
<input type="email" name="email" value="<?= $row['email'] ?>">
</div>

<div>
<label>Department</label>
<input type="text" name="department" value="<?= $row['department'] ?>">
</div>

<div>
<label>Mobile</label>
<input type="text" name="mobile" value="<?= $row['mobile'] ?>">
</div>

<div>
<label>Designation</label>
<input type="text" name="designation" value="<?= $row['designation'] ?>">
</div>

<div>
<label>Qualification</label>
<input type="text" name="qualification" value="<?= $row['qualification'] ?>">
</div>

<div>
<label>Experience</label>
<input type="text" name="experience" value="<?= $row['experience'] ?>">
</div>

<div>
<label>Specialization</label>
<input type="text" name="specialization" value="<?= $row['specialization'] ?>">
</div>

<div>
<label>Gender</label>
<select name="gender">
<option <?= $row['gender']=='Male'?'selected':'' ?>>Male</option>
<option <?= $row['gender']=='Female'?'selected':'' ?>>Female</option>
</select>
</div>

<div>
<label>DOB</label>
<input type="date" name="dob" value="<?= $row['dob'] ?>">
</div>

<div class="full">
<label>Joining Date</label>
<input type="date" name="joining_date" value="<?= $row['joining_date'] ?>">
</div>

</div>

<br>

<button class="btn update-btn" type="submit" name="update">
✔ Update Faculty
</button>

</form>

</div>
</div>

<script>
function loadFile(event){
    document.getElementById('preview').src =
        URL.createObjectURL(event.target.files[0]);
}
</script>

</body>
</html>