<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

/* =========================
   FILTERS
========================= */
$class = $_GET['class'] ?? "";
$course = $_GET['course'] ?? "";
$date = $_GET['date'] ?? "";

/* =========================
   MAIN QUERY (SAFE BUILD)
========================= */
$sql = "
SELECT 
    a.id,
    a.student_id,
    s.name,
    s.roll_no,
    s.class_name,
    s.course,
    a.status,
    a.date
FROM attendance a
JOIN students s ON a.student_id = s.student_id
WHERE 1=1
";

if ($class != "") {
    $class = $conn->real_escape_string($class);
    $sql .= " AND s.class_name='$class'";
}

if ($course != "") {
    $course = $conn->real_escape_string($course);
    $sql .= " AND s.course='$course'";
}

if ($date != "") {
    $date = $conn->real_escape_string($date);
    $sql .= " AND a.date='$date'";
}

$sql .= " ORDER BY a.date DESC";

$result = $conn->query($sql);

if(!$result){
    die("SQL Error: " . $conn->error);
}

/* =========================
   SUMMARY CALCULATION
========================= */
$total = 0;
$present = 0;
$absent = 0;

$temp = $conn->query($sql);
while($r = $temp->fetch_assoc()){
    $total++;
    if(strtolower($r['status']) == "present") $present++;
    else $absent++;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Enterprise Attendance System</title>

<style>

body{
    margin:0;
    font-family:Segoe UI;
    background:#0b1220;
    color:white;
}

/* CONTAINER */
.container{
    padding:20px;
}

/* FILTER BOX */
.filters{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:10px;
    margin-bottom:20px;
}

select,input,button{
    padding:10px;
    border-radius:8px;
    border:none;
    outline:none;
}

button{
    background:#38bdf8;
    font-weight:bold;
    cursor:pointer;
}

/* SUMMARY */
.summary{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
    gap:10px;
    margin-bottom:20px;
}

.card{
    background:#111827;
    padding:15px;
    border-radius:12px;
    text-align:center;
    border:1px solid #1f2937;
}

.card h2{
    margin:0;
    color:#38bdf8;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:#111827;
    border-radius:12px;
    overflow:hidden;
}

th,td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #1f2937;
}

th{
    background:#1f2937;
}

.present{ color:#22c55e; font-weight:bold; }
.absent{ color:#ef4444; font-weight:bold; }

/* AUTO REFRESH INDICATOR */
.live{
    color:#22c55e;
    font-size:14px;
}

</style>
</head>

<body>

<div class="container">

<h2>📊 Enterprise Attendance Dashboard</h2>

<p class="live">🟢 Live System Enabled (Auto Refresh every 10 sec)</p>

<!-- FILTERS -->
<form method="GET" class="filters">

    <input type="text" name="class" placeholder="Class (FY BCA)" value="<?php echo $class; ?>">

    <input type="text" name="course" placeholder="Course" value="<?php echo $course; ?>">

    <input type="date" name="date" value="<?php echo $date; ?>">

    <button type="submit">🔍 Filter</button>

</form>

<!-- SUMMARY -->
<div class="summary">

    <div class="card">
        <h2><?php echo $total; ?></h2>
        <p>Total Records</p>
    </div>

    <div class="card">
        <h2><?php echo $present; ?></h2>
        <p>Present</p>
    </div>

    <div class="card">
        <h2><?php echo $absent; ?></h2>
        <p>Absent</p>
    </div>

</div>

<!-- TABLE -->
<table>

<tr>
    <th>ID</th>
    <th>Student</th>
    <th>Roll No</th>
    <th>Class</th>
    <th>Course</th>
    <th>Status</th>
    <th>Date</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['roll_no']; ?></td>
    <td><?php echo $row['class_name']; ?></td>
    <td><?php echo $row['course']; ?></td>

    <td class="<?php echo strtolower($row['status']); ?>">
        <?php echo $row['status']; ?>
    </td>

    <td><?php echo $row['date']; ?></td>
</tr>

<?php } ?>

</table>

</div>

<!-- =========================
     AUTO REFRESH (REAL TIME)
========================= -->
<script>
setInterval(() => {
    location.reload();
}, 10000); // 10 seconds
</script>

</body>
</html>