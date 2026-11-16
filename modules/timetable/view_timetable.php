<?php
include("../../config/db.php");

/* FETCH ALL TIMETABLE */
$sql = "SELECT * FROM timetable ORDER BY id ASC";
$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Timetable</title>

<style>
body{
    margin:0;
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

/* BACK BUTTON */
.back{
    display:inline-block;
    margin-bottom:15px;
    padding:10px 14px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:8px;
}

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
    padding:12px;
    color:#38bdf8;
    text-align:center;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

tr:hover{
    background:rgba(255,255,255,0.08);
}

.badge{
    padding:4px 8px;
    background:#22c55e;
    border-radius:6px;
    font-size:12px;
}
</style>

</head>
<body>

<div class="container">

<a href="../../student_dashboard.php" class="back">⬅ Back to Dashboard</a>

<h2>📅 Full Timetable</h2>

<table>
<tr>
    <th>ID</th>
    <th>Class</th>
    <th>Course</th>
    <th>Time</th>
    <th>Monday</th>
    <th>Tuesday</th>
    <th>Wednesday</th>
    <th>Thursday</th>
    <th>Friday</th>
    <th>Saturday</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['class_name']) ?></td>
    <td><?= htmlspecialchars($row['course']) ?></td>
    <td><?= htmlspecialchars($row['time']) ?></td>

    <td><?= !empty($row['monday']) ? $row['monday'] : '-' ?></td>
    <td><?= !empty($row['tuesday']) ? $row['tuesday'] : '-' ?></td>
    <td><?= !empty($row['wednesday']) ? $row['wednesday'] : '-' ?></td>
    <td><?= !empty($row['thursday']) ? $row['thursday'] : '-' ?></td>
    <td><?= !empty($row['friday']) ? $row['friday'] : '-' ?></td>
    <td><?= !empty($row['saturday']) ? $row['saturday'] : '-' ?></td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>