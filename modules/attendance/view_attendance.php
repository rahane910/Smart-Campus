<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$role = $_SESSION['role'];

/* STUDENT ONLY FILTER */
if ($role == "student") {
    $sid = $_SESSION['user_id'];

    $sql = "
        SELECT 
            students.name,
            students.student_id,
            students.roll_no,
            students.prn_no,
            students.course,
            students.semester,
            students.dob,
            students.mobile,
            students.photo,
            attendance.subject,
            attendance.date,
            attendance.status
        FROM attendance
        INNER JOIN students 
            ON attendance.student_id = students.student_id
        WHERE students.student_id = '$sid'
        ORDER BY attendance.date DESC
    ";
} else {

    /* ADMIN + FACULTY SEE ALL */
    $sql = "
        SELECT 
            students.name,
            students.student_id,
            students.roll_no,
            students.prn_no,
            students.course,
            students.semester,
            students.dob,
            students.mobile,
            students.photo,
            attendance.subject,
            attendance.date,
            attendance.status
        FROM attendance
        INNER JOIN students 
            ON attendance.student_id = students.student_id
        ORDER BY attendance.date DESC
    ";
}

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Report</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{
    max-width:1200px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:rgba(255,255,255,0.05);
    border-radius:10px;
    overflow:hidden;
}

th{
    background:#1e293b;
    color:#38bdf8;
    padding:10px;
    font-size:13px;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
    font-size:13px;
}

img{
    width:40px;
    height:40px;
    border-radius:6px;
    object-fit:cover;
}

.present{color:#22c55e;font-weight:bold;}
.absent{color:#ef4444;font-weight:bold;}
</style>
</head>

<body>

<div class="container">

<h2>📊 Attendance Report</h2>

<table>
<tr>
    <th>Photo</th>
    <th>Name</th>
    <th>Student ID</th>
    <th>Roll No</th>
    <th>PRN</th>
    <th>Course</th>
    <th>Sem</th>
    <th>DOB</th>
    <th>Mobile</th>
    <th>Subject</th>
    <th>Date</th>
    <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td>
        <img src="../../uploads/<?= $row['photo'] ?>" 
             onerror="this.src='../../assets/default.png'">
    </td>

    <td><?= $row['name'] ?></td>
    <td><?= $row['student_id'] ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['prn_no'] ?></td>
    <td><?= $row['course'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['dob'] ?></td>
    <td><?= $row['mobile'] ?></td>

    <td><?= $row['subject'] ?></td>
    <td><?= $row['date'] ?></td>

    <td class="<?= strtolower($row['status']) ?>">
        <?= $row['status'] ?>
    </td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>