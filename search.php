<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: auth/login.php");
    exit();
}

$search = $_GET['query'] ?? '';
$search = trim($search);

$studentResults = [];
$facultyResults = [];

if ($search != "") {

    $term = "%$search%";

    /* =========================
       STUDENTS SEARCH
    ========================== */
    $stmt1 = $conn->prepare("
        SELECT id, name, email, student_id, 'Student' AS type
        FROM students
        WHERE name LIKE ? 
           OR email LIKE ? 
           OR student_id LIKE ?
    ");

    $stmt1->bind_param("sss", $term, $term, $term);
    $stmt1->execute();
    $res1 = $stmt1->get_result();

    while ($row = $res1->fetch_assoc()) {
        $studentResults[] = $row;
    }

    /* =========================
       FACULTY SEARCH
    ========================== */
    $stmt2 = $conn->prepare("
        SELECT id, name, email, faculty_id AS unique_id, 'Faculty' AS type
        FROM faculty
        WHERE name LIKE ? 
           OR email LIKE ? 
           OR faculty_id LIKE ?
    ");

    $stmt2->bind_param("sss", $term, $term, $term);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    while ($row = $res2->fetch_assoc()) {
        $facultyResults[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Global Search</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{
    max-width:1000px;
    margin:auto;
}

h2{
    text-align:center;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:rgba(255,255,255,0.05);
    border-radius:10px;
    overflow:hidden;
}

th{
    background:#1e293b;
    color:#38bdf8;
    padding:12px;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}

.student{background:#16a34a;}
.faculty{background:#3b82f6;}
</style>
</head>

<body>

<div class="container">

<h2>🔍 Search Results: "<?= htmlspecialchars($search) ?>"</h2>

<!-- STUDENTS -->
<?php if (count($studentResults) > 0) { ?>
<h3>👨‍🎓 Students</h3>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Student ID</th>
    <th>Type</th>
</tr>

<?php foreach ($studentResults as $s) { ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= htmlspecialchars($s['name']) ?></td>
    <td><?= htmlspecialchars($s['email']) ?></td>
    <td><?= $s['student_id'] ?></td>
    <td><span class="badge student">Student</span></td>
</tr>
<?php } ?>

</table>
<?php } ?>

<!-- FACULTY -->
<?php if (count($facultyResults) > 0) { ?>
<h3 style="margin-top:30px;">👨‍🏫 Faculty</h3>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Faculty ID</th>
    <th>Type</th>
</tr>

<?php foreach ($facultyResults as $f) { ?>
<tr>
    <td><?= $f['id'] ?></td>
    <td><?= htmlspecialchars($f['name']) ?></td>
    <td><?= htmlspecialchars($f['email']) ?></td>
    <td><?= $f['unique_id'] ?></td>
    <td><span class="badge faculty">Faculty</span></td>
</tr>
<?php } ?>

</table>
<?php } ?>

<?php if (count($studentResults) == 0 && count($facultyResults) == 0) { ?>
    <p style="text-align:center;margin-top:20px;">No results found.</p>
<?php } ?>

<br>
<a href="admin_dashboard.php" style="color:#38bdf8;">⬅ Back to Dashboard</a>

</div>

</body>
</html>