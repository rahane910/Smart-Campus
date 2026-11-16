<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =====================
   FACULTY INFO
===================== */
$facultyName = $_SESSION['name'] ?? "Faculty";

/* DEFAULT IMAGE */
$profileImage = "uploads/default_user.png";

/* FETCH FROM FACULTY TABLE */
$q = $conn->query("SELECT name, photo FROM faculty WHERE id='$user_id'");

if ($q && $q->num_rows > 0) {
    $row = $q->fetch_assoc();

    // NAME
    if (!empty($row['name'])) {
        $facultyName = $row['name'];
    }

    // PHOTO FIX
    if (!empty($row['photo'])) {

        // IMPORTANT: correct absolute project path
        $filePath = "uploads/" . $row['photo'];

        // check file exists inside smart_campus/uploads
        if (file_exists($filePath)) {
            $profileImage = $filePath;
        } else {
            // fallback debug (optional)
            $profileImage = "assets/default_user.png";
        }
    }
}

/* =====================
   REAL STATS
===================== */
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'] ?? 0;

$totalClasses = $conn->query("SELECT COUNT(DISTINCT course) as total FROM students")->fetch_assoc()['total'] ?? 0;

$totalNotices = $conn->query("SELECT COUNT(*) as total FROM notices")->fetch_assoc()['total'] ?? 0;

$totalComplaints = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Faculty Dashboard</title>

<style>

body{
    margin:0;
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
}

/* TOP BAR */
.topbar{
    position:fixed;
    top:0;
    left:0;
    right:0;
    height:60px;
    background:#1e293b;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 20px;
    z-index:1000;
}

/* PROFILE */
.profile{
    display:flex;
    align-items:center;
    gap:10px;
}

.profile img{
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #38bdf8;
}

/* MENU BUTTON */
.menu-btn{
    font-size:28px;
    cursor:pointer;
    transition:0.4s;
}

/* ROTATE */
.menu-btn.rotate{
    transform:rotate(180deg);
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:60px;
    left:-280px;
    width:260px;
    height:100%;
    background:#111827;
    padding-top:20px;
    transition:0.35s ease;
}

.sidebar.open{
    left:0;
}

.sidebar a{
    display:block;
    padding:12px 20px;
    color:white;
    text-decoration:none;
    transition:0.2s;
}

.sidebar a:hover{
    background:#1e293b;
    color:#38bdf8;
    padding-left:25px;
}

/* MAIN */
.main{
    margin-top:70px;
    padding:20px;
    transition:0.35s ease;
}

.main.shift{
    margin-left:260px;
}

/* STATS */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-bottom:20px;
}

.stat-card{
    background:#111827;
    border:1px solid #1f2937;
    padding:15px;
    border-radius:12px;
    text-align:center;
}

.stat-card h2{
    color:#38bdf8;
}

/* MODULE CARDS */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
}

.card{
    background:#111827;
    border:1px solid #1f2937;
    padding:18px;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
    border-color:#38bdf8;
}

</style>
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">

    <div class="menu-btn" id="menuBtn" onclick="toggleSidebar()">☰</div>

    <div class="profile">
        <img src="<?php echo $profileImage; ?>">
        <div>
            <div><?php echo htmlspecialchars($facultyName); ?></div>
            <small>Faculty</small>
        </div>
    </div>

</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <a href="faculty_dashboard.php">🏠 Dashboard</a>
    <a href="modules/attendance/mark_attendance.php">📋 Mark Attendance</a>
    <a href="modules/attendance/view_attendance.php">📊 View Attendance</a>
    <a href="modules/leave/apply_leave.php">📝 Leave</a>
    <a href="modules/notice/view_notice.php">📢 Notices</a>
    <a href="modules/complaint/raise_complaint.php">⚠ Complaints</a>

    <a href="modules/timetable/upload_timetable.php">🗓 Timetable</a>
    <a href="modules/resources/upload_resources.php">📚 Resources</a>
    <a href="modules/calendar/faculty_calendar.php">📅 Calendar</a>

    <a href="auth/logout.php">🚪 Logout</a>

</div>

<!-- MAIN -->
<div class="main" id="main">

    <h2>📊 Faculty Dashboard</h2>

    <!-- REAL STATS -->
    <div class="stats">

        <div class="stat-card">
            <h2><?php echo $totalStudents; ?></h2>
            <p>Students</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $totalClasses; ?></h2>
            <p>Courses</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $totalNotices; ?></h2>
            <p>Notices</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $totalComplaints; ?></h2>
            <p>Pending Complaints</p>
        </div>

    </div>

    <!-- MODULES -->
    <div class="grid">

        <div class="card" onclick="location.href='modules/attendance/view_attendance.php'">
            📊 Attendance
        </div>

        <div class="card" onclick="location.href='modules/timetable/upload_timetable.php'">
            🗓 Timetable
        </div>

        <div class="card" onclick="location.href='modules/resources/upload_resources.php'">
            📚 Resources
        </div>

        <div class="card" onclick="location.href='modules/calendar/faculty_calendar.php'">
            📅 Calendar
        </div>

    </div>

</div>

<script>

let isOpen = false;

function toggleSidebar(){
    isOpen = !isOpen;

    document.getElementById("sidebar").classList.toggle("open");
    document.getElementById("main").classList.toggle("shift");

    const btn = document.getElementById("menuBtn");
    btn.classList.toggle("rotate");

    btn.innerHTML = isOpen ? "✖" : "☰";
}

</script>

</body>
</html>