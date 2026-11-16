<?php
session_start();
include("../../config/db.php");
date_default_timezone_set("Asia/Kolkata");
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    die("Access Denied");
}

$date = date("Y-m-d");

$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")
                     ->fetch_assoc()['total'];

/* PRESENT COUNT */
$presentCount = $conn->query("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE date='$date' AND status='Present'
")->fetch_assoc()['total'];

/* ABSENT COUNT */
$absentCount = $conn->query("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE date='$date' AND status='Absent'
")->fetch_assoc()['total'];

$notMarked = $totalStudents - ($presentCount + $absentCount);


function calculateAttendancePercent($conn, $student_id) {

    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present
        FROM attendance
        WHERE student_id='$student_id'
    ");

    $data = $result->fetch_assoc();

    $total = $data['total'];
    $present = $data['present'];

    if ($total == 0) return 0;

    return round(($present / $total) * 100, 2);
}


$time = date("H:i:s", time());

/* =========================
   MARK ATTENDANCE (INSERT)
========================= */
if (isset($_GET['student']) && isset($_GET['status'])) {

    $student_id = $_GET['student'];
    $status = $_GET['status'];

    /* CHECK ALREADY MARKED TODAY */
    $check = $conn->query("
        SELECT id FROM attendance
        WHERE student_id='$student_id' AND date='$date'
    ");

    if ($check && $check->num_rows > 0) {
        echo "<script>
            alert('⚠ Attendance already marked today!');
            window.location.href='mark_attendance.php';
        </script>";
        exit();
    }

    /* INSERT ATTENDANCE */
$stmt = $conn->prepare("
    INSERT INTO attendance (student_id, subject, status, date, time)
    VALUES (?, 'General', ?, CURDATE(), CURRENT_TIME)
");

$stmt->bind_param("ss", $student_id, $status);
$stmt->execute();
    header("Location: mark_attendance.php");
    exit();
}

/* =========================
   FETCH STUDENTS + ATTENDANCE
========================= */
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
        attendance.status,
        attendance.date,
        attendance.time
    FROM students
    LEFT JOIN attendance 
        ON students.student_id = attendance.student_id 
        AND attendance.date = '$date'
    ORDER BY students.name ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{
    max-width:1300px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

.summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-bottom:20px;
}

.card{
    padding:15px;
    border-radius:12px;
    text-align:center;
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.1);
}

.card h3{
    font-size:22px;
    margin:5px 0;
}

.present{border-left:5px solid #22c55e;}
.absent{border-left:5px solid #ef4444;}
.pending{border-left:5px solid #f59e0b;}
.total{border-left:5px solid #38bdf8;}

/* TABLE */
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

/* PHOTO */
.student-photo{
    width:70px;
    height:70px;
    border-radius:10px;
    object-fit:cover;
    border:2px solid #38bdf8;
}

/* BUTTONS */
.btn{
    padding:5px 8px;
    border-radius:6px;
    text-decoration:none;
    color:white;
    font-size:12px;
    margin:2px;
}

.present{background:#22c55e;}
.absent{background:#ef4444;}

/* STATUS */
.present-text{color:#22c55e;font-weight:bold;}
.absent-text{color:#ef4444;font-weight:bold;}
.pending{color:#f59e0b;font-weight:bold;}


.back-btn{
    display:inline-block;
    padding:10px 14px;
    margin-bottom:15px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:8px;
    font-weight:500;
    transition:0.3s;
}

.back-btn:hover{
    background:#475569;
    transform:translateY(-2px);
}
</style>
</head>

<body>

<div class="container">

<a href="../../faculty_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
<h2>📋 Mark Attendance (With Time Tracking)</h2>

<div class="summary">

    <div class="card present">
        <h3><?= $presentCount ?></h3>
        <p>Present</p>
    </div>

    <div class="card absent">
        <h3><?= $absentCount ?></h3>
        <p>Absent</p>
    </div>

    <div class="card pending">
        <h3><?= $notMarked ?></h3>
        <p>Not Marked</p>
    </div>

    <div class="card total">
        <h3><?= $totalStudents ?></h3>
        <p>Total Students</p>
    </div>

</div>

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
    <th>%</th>
    <th>Status</th>
    <th>Date</th>
    <th>Time</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <img class="student-photo"
             src="../../uploads/<?= $row['photo'] ?>"
             onerror="this.src='../../assets/default.png'">
    </td>

    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= $row['student_id'] ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['prn_no'] ?></td>
    <td><?= $row['course'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['dob'] ?></td>
    <td><?= $row['mobile'] ?></td>

    <td>
    <?php
        echo calculateAttendancePercent($conn, $row['student_id']) . "%";
    ?>
</td>

    <td>
        <?php
        if ($row['status'] == "Present") {
            echo "<span class='present-text'>Present</span>";
        } elseif ($row['status'] == "Absent") {
            echo "<span class='absent-text'>Absent</span>";
        } else {
            echo "<span class='pending'>Not Marked</span>";
        }
        ?>
    </td>

    <td><?= $row['date'] ?? '-' ?></td>
    <td><?= $row['time'] ?? '-' ?></td>

    <td>
        <a class="btn present"
           href="?student=<?= $row['student_id'] ?>&status=Present">
           Present
        </a>

        <a class="btn absent"
           href="?student=<?= $row['student_id'] ?>&status=Absent">
           Absent
        </a>
    </td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>