<?php
session_start();


include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: auth/login.php");
    exit();
}

/* Default counts */
$student_count = 0;
$faculty_count = 0;
$leave_count = 0;
$complaint_count = 0;
$department_count = 0;
$attendance_count = 0;
$fee_count = 0;

/* Students Count */
$result = $conn->query("SELECT COUNT(*) AS total FROM students");
if ($result) {
    $student_count = $result->fetch_assoc()['total'];
}

/* Faculty Count */
$result = $conn->query("SELECT COUNT(*) AS total FROM faculty");
if ($result) {
    $faculty_count = $result->fetch_assoc()['total'];
}

/* Leaves */
$result = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'");
if ($result) {
    $leave_count = $result->fetch_assoc()['total'];
}

/* Complaints */
$result = $conn->query("SELECT COUNT(*) AS total FROM complaints");
if ($result) {
    $complaint_count = $result->fetch_assoc()['total'];
}

/* Departments */
$result = $conn->query("SELECT COUNT(*) AS total FROM departments");
if ($result) {
    $department_count = $result->fetch_assoc()['total'];
}

/* Attendance */
$result = $conn->query("SELECT COUNT(*) AS total FROM attendance");
if ($result) {
    $attendance_count = $result->fetch_assoc()['total'];
}

/* Fees */
$result = $conn->query("SELECT COUNT(*) AS total FROM fee_payments");
if ($result) {
    $fee_count = $result->fetch_assoc()['total'];
}

/* Latest Notices */
$notice_result = $conn->query("
    SELECT title, content, notice_file, created_at
    FROM notices
    ORDER BY id DESC
    LIMIT 5
");;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f1f5f9;
    display:flex;
    min-height:100vh;
}

/* TOP NAVBAR */
.topbar{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:70px;
    background:linear-gradient(90deg,#0f172a,#1e293b);
    color:white;
    display:flex;
    align-items:center;
    padding:0 25px;
    z-index:1000;
    box-shadow:0 4px 20px rgba(0,0,0,0.15);
}

.topbar h2{
    font-size:24px;
    font-weight:600;
}

.menu-btn{
    font-size:28px;
    margin-right:20px;
    cursor:pointer;
    background:none;
    border:none;
    color:white;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    top:70px;
    left:0;
    width:270px;
    height:100%;
    background:linear-gradient(180deg,#1e293b,#0f172a);
    padding:25px 18px;
    box-shadow:4px 0 20px rgba(0,0,0,0.08);
}

.sidebar h2{
    color:white;
    text-align:center;
    margin-bottom:25px;
    font-size:22px;
}

.sidebar a{
    display:block;
    padding:14px 16px;
    margin-bottom:12px;
    color:#e2e8f0;
    text-decoration:none;
    border-radius:12px;
    transition:all 0.3s ease;
    font-size:15px;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.1);
    transform:translateX(6px);
}

/* MAIN CONTENT */
.main{
    margin-left:270px;
    margin-top:70px;
    padding:30px;
    width:calc(100% - 270px);
}

/* HEADER CARD */
.header{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:white;
    padding:30px;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(37,99,235,0.25);
    margin-bottom:30px;
}

.header h2{
    font-size:30px;
    margin-bottom:8px;
}

.header p{
    font-size:15px;
    opacity:0.95;
}

/* SEARCH */
.search-box input{
    width:100%;
    padding:16px 18px;
    border:none;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    margin-bottom:25px;
    font-size:15px;
}

/* QUICK ACTIONS */
.quick-actions{
    margin-bottom:25px;
}

.quick-actions a{
    display:inline-block;
    padding:12px 20px;
    margin:8px 10px 8px 0;
    background:white;
    color:#1e293b;
    text-decoration:none;
    border-radius:12px;
    box-shadow:0 6px 16px rgba(0,0,0,0.08);
    font-weight:600;
    transition:0.3s;
}

.quick-actions a:hover{
    transform:translateY(-3px);
}

/* NOTICE */
.notice-box{
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 8px 22px rgba(0,0,0,0.06);
    margin-bottom:30px;
    max-height:420px;
    overflow-y:auto;
}

.notice-list{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.notice-card{
    background:#f8fafc;
    padding:16px;
    border-radius:14px;
    border-left:5px solid #2563eb;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
    max-height:180px;
    overflow:hidden;
}

.notice-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:8px;
}

.notice-header h4{
    margin:0;
    color:#1e293b;
    font-size:16px;
    max-width:70%;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.notice-header span{
    font-size:12px;
    color:#64748b;
}

.notice-content{
    color:#475569;
    line-height:1.5;
    font-size:14px;
    max-height:55px;
    overflow:hidden;
    text-overflow:ellipsis;
}

.notice-file{
    margin-top:10px;
}

.notice-file img{
    width:100%;
    max-width:220px;
    max-height:90px;
    object-fit:cover;
    border-radius:8px;
}

.notice-file a{
    font-size:14px;
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
}

/* STATS CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.card{
    padding:28px;
    border-radius:20px;
    color:white;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    font-size:17px;
    margin-bottom:12px;
}

.card p{
    font-size:34px;
    font-weight:700;
}

/* Different gradients */
.card:nth-child(1){background:linear-gradient(135deg,#3b82f6,#2563eb);}
.card:nth-child(2){background:linear-gradient(135deg,#8b5cf6,#7c3aed);}
.card:nth-child(3){background:linear-gradient(135deg,#f59e0b,#d97706);}
.card:nth-child(4){background:linear-gradient(135deg,#ef4444,#dc2626);}
.card:nth-child(5){background:linear-gradient(135deg,#10b981,#059669);}
.card:nth-child(6){background:linear-gradient(135deg,#06b6d4,#0891b2);}
.card:nth-child(7){background:linear-gradient(135deg,#6366f1,#4f46e5);}

/* CHARTS */
.charts-container{
    margin-top:30px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.chart-box{
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 8px 22px rgba(0,0,0,0.06);
}

.chart-box h3{
    margin-bottom:15px;
    color:#1e293b;
}

/* RESPONSIVE */
@media(max-width:900px){
    .sidebar{
        width:100%;
        position:relative;
        height:auto;
    }

    .main{
        margin-left:0;
        width:100%;
    }

    .charts-container{
        grid-template-columns:1fr;
    }
}
</style>
</head>
<body>


<!-- TOP BAR -->
<div class="topbar">
    <button class="menu-btn" onclick="toggleSidebar()">☰</button>
    <h2>Admin Dashboard</h2>
</div>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar">
    <h2>🎓 Admin Panel</h2>

    <a href="modules/student/manage_students.php">👨‍🎓 Manage Students</a>
    <a href="modules/faculty/manage_faculty.php">👨‍🏫 Manage Faculty</a>
    <a href="modules/attendance/attendance_reports.php">📊 Attendance Reports</a>
    <a href="modules/notice/manage_notices.php">📢 Manage Notices</a>
    <a href="modules/leave/manage_leave.php">📝 Leave Approval</a>
    <a href="modules/complaint/manage_complaint.php">💬 Complaints</a>
    <a href="modules/department/manage_department.php">🏢 Departments</a>
    <a href="modules/fees/manage_fees.php">💳 Fees Module</a>
    <a href="auth/logout.php">🚪 Logout</a>
</div>

<div class="main" id="main">

    <div class="header">
    <h2>🎓 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
    <p>Manage your Smart Campus operations in one place</p>
    <p>📅 <?php echo date("d M Y | h:i A"); ?></p>
</div>

    <form class="search-box" action="search.php" method="GET">
        <input type="text" name="query" placeholder="🔍 Search students or faculty..." required>
    </form>

    <div class="quick-actions">
        <a href="modules/student/add_student.php">➕ Add Student</a>
        <a href="modules/faculty/add_faculty.php">➕ Add Faculty</a>
        <a href="modules/notice/post_notice.php">📢 Post Notice</a>
        <a href="modules/leave/manage_leave.php">📝 Approve Leaves</a>
    </div>

    <div class="notice-box">
    <h3>📢 Latest Notices</h3>

    <?php if($notice_result && $notice_result->num_rows > 0){ ?>
        
        <div class="notice-list">
            <?php while($notice = $notice_result->fetch_assoc()){ ?>
                
                <div class="notice-card">
                    
                    <div class="notice-header">
                        <h4>
                            <?= !empty($notice['title']) 
                                ? htmlspecialchars($notice['title']) 
                                : "Notice Attachment" ?>
                        </h4>

                        <span>
                            <?= date("d M Y", strtotime($notice['created_at'])) ?>
                        </span>
                    </div>

                    <?php if(!empty($notice['content'])){ ?>
                        <p class="notice-content">
                            <?= nl2br(htmlspecialchars(substr($notice['content'], 0, 150))) ?>
                        </p>
                    <?php } ?>

                    <?php if(!empty($notice['notice_file'])){ ?>
                        <?php
                        $files = explode(",", $notice['notice_file']);
                        foreach($files as $file){
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        ?>
                            
                            <div class="notice-file">
                                <?php if(in_array($ext, ['jpg','jpeg','png','webp'])){ ?>
                                    <img src="uploads/notices/<?= $file ?>" width="100%">
                                <?php } else { ?>
                                    <a href="uploads/notices/<?= $file ?>" target="_blank">
                                        📎 View Attachment
                                    </a>
                                <?php } ?>
                            </div>

                        <?php } ?>
                    <?php } ?>

                </div>

            <?php } ?>
        </div>

    <?php } else { ?>
        <p>No notices available</p>
    <?php } ?>
</div>

    <div class="cards">
        <div class="card">
            <h3>👨‍🎓 Total Students</h3>
            <p><?php echo $student_count; ?></p>
        </div>

        <div class="card">
            <h3>👨‍🏫 Total Faculty</h3>
            <p><?php echo $faculty_count; ?></p>
        </div>

        <div class="card">
            <h3>📝 Pending Leaves</h3>
            <p><?php echo $leave_count; ?></p>
        </div>

        <div class="card">
            <h3>💬 Complaints</h3>
            <p><?php echo $complaint_count; ?></p>
        </div>

        <div class="card">
            <h3>🏢 Departments</h3>
            <p><?php echo $department_count; ?></p>
        </div>

        <div class="card">
            <h3>📅 Attendance</h3>
            <p><?php echo $attendance_count; ?></p>
        </div>

        <div class="card">
            <h3>💳 Fee Payments</h3>
            <p><?php echo $fee_count; ?></p>
        </div>

       
    </div>


     <div class="charts-container">
    <div class="chart-box">
        <h3>📊 Students vs Faculty</h3>
        <canvas id="userChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>📈 System Analytics</h3>
        <canvas id="systemChart"></canvas>
    </div>
</div>
</div>
    <script>
const studentCount = <?php echo $student_count; ?>;
const facultyCount = <?php echo $faculty_count; ?>;
const attendanceCount = <?php echo $attendance_count; ?>;
const feeCount = <?php echo $fee_count; ?>;

// Students vs Faculty
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: ['Students', 'Faculty'],
        datasets: [{
            label: 'Total Users',
            data: [studentCount, facultyCount]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        }
    }
});

// Attendance + Fees
new Chart(document.getElementById('systemChart'), {
    type: 'pie',
    data: {
        labels: ['Attendance', 'Fee Payments'],
        datasets: [{
            data: [attendanceCount, feeCount]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
<script>
function toggleSidebar(){
    let sidebar = document.getElementById("sidebar");
    let main = document.getElementById("main");

    sidebar.classList.toggle("active");
    main.classList.toggle("shift");
}
</script>
</body>
</html>