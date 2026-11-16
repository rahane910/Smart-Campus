<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student"){
    header("Location: auth/login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$student_email = $_SESSION['email'];

/* =========================
   STUDENT DETAILS (SECURE)
========================= */
$stmt = $conn->prepare("SELECT name, roll_no, course, semester, photo FROM students WHERE email = ?");
$stmt->bind_param("s", $student_email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $student = $result->fetch_assoc();
} else {
    $student = [
        'name' => $_SESSION['name'],
        'roll_no' => 'N/A',
        'course' => 'N/A',
        'semester' => 'N/A',
        'photo' => ''
    ];
}

/* =========================
   PROFILE PHOTO
========================= */
$photo_path = "uploads/default.png";

if(!empty($student['photo'])){
    $file = "uploads/" . basename($student['photo']);
    if(file_exists($file)){
        $photo_path = $file;
    }
}

/* =========================
   UNREAD NOTICES
========================= */
$unread_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM notices n
    LEFT JOIN notice_acknowledgements na 
    ON n.id = na.notice_id AND na.student_id = $user_id
    WHERE na.notice_id IS NULL
");

if(!$unread_result){
    die("Unread Query Error: " . $conn->error);
}

$unread_count = $unread_result->fetch_assoc()['total'];

/* =========================
   LATEST NOTICES
========================= */
$notice_sql = "
    SELECT id, title, content, notice_file, created_at
    FROM notices
    ORDER BY id DESC
    LIMIT 5
";

$notice_result = $conn->query($notice_sql);

if(!$notice_result){
    die("Notice Query Error: " . $conn->error);
}
/* =========================
   ANALYTICS
========================= */

// Attendance %
$res = $conn->query("
    SELECT COUNT(*) as total,
           SUM(status='Present') as present
    FROM attendance
    WHERE student_id = $user_id
");

$row = $res->fetch_assoc();
$total = $row['total'];
$present = $row['present'];
$attendance_percent = ($total > 0) ? round(($present/$total)*100) : 0;

// Leaves
$leave = $conn->query("
    SELECT COUNT(*) as total
    FROM leave_requests
    WHERE student_id = $user_id
")->fetch_assoc()['total'];

// Complaints
$complaints = $conn->query("
    SELECT COUNT(*) as total
    FROM complaints
    WHERE student_id = $user_id
")->fetch_assoc()['total'];

// Attendance Trend
$trend = $conn->query("
    SELECT DATE(date) as day,
           SUM(status='Present') as present
    FROM attendance
    WHERE student_id = $user_id
    GROUP BY day
    ORDER BY day DESC
    LIMIT 7
");

$labels = [];
$data = [];

while($r = $trend->fetch_assoc()){
    $labels[] = $r['day'];
    $data[] = $r['present'];
}

$labels = array_reverse($labels);
$data = array_reverse($data);

?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>

<style>
body{
    margin:0;
    font-family:'Poppins', sans-serif;
    background: linear-gradient(135deg,#eef2f7,#dfe9f3);
}

/* Layout */
.dashboard{
    display:flex;
    min-height:100vh;
}

/* ================= SIDEBAR ================= */
.sidebar{
    width:260px;
    background: linear-gradient(180deg,#0f2027,#203a43,#2c5364);
    color:white;
    padding:25px 15px;
    box-shadow: 4px 0 15px rgba(0,0,0,0.2);
}

.sidebar h2{
    text-align:center;
    margin-bottom:30px;
    letter-spacing:1px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    margin-bottom:12px;
    border-radius:10px;
    text-decoration:none;
    color:white;
    transition:0.3s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.15);
    transform:translateX(5px);
}

/* ================= MAIN ================= */
.main-content{
    flex:1;
    padding:30px;
}

/* ================= HEADER ================= */
.header-card{
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(12px);
    padding:20px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
    margin-bottom:25px;
}

.profile-section{
    display:flex;
    align-items:center;
    gap:20px;
}

.profile-photo{
    width:90px;
    height:90px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #2c5364;
}

/* ================= CARDS ================= */
.analytics{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:25px;
}

.card{
    background: white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    transition:0.3s;
    position:relative;
    overflow:hidden;
}

.card:hover{
    transform:translateY(-6px);
}

.card h3{
    color:#555;
    font-size:16px;
}

.card p{
    font-size:30px;
    font-weight:bold;
    margin-top:10px;
    color:#2c5364;
}

/* Gradient top bar */
.card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:5px;
    background:linear-gradient(90deg,#2c5364,#0f2027);
}

/* ================= CHART ================= */
.chart-card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    margin-bottom:25px;
    height:350px; /* FIXED HEIGHT */
    display:flex;
    flex-direction:column;
}

.chart-card canvas{
    flex:1;
    max-height:280px;
}

/* ================= NOTICE ================= */
.notice-card{
    background:white;
    padding:20px;
    margin-bottom:20px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    transition:0.3s;
    border-left:6px solid #2c5364;
}

.notice-card:hover{
    transform:translateY(-5px);
}

.notice-card h3{
    color:#2c5364;
}

.notice-img{
    margin-top:10px;
    max-width:100%;
    border-radius:10px;
}

/* ================= BADGE ================= */
.badge{
    background:#ff4d4d;
    padding:4px 8px;
    border-radius:50%;
    font-size:12px;
    margin-left:6px;
}

/* ================= POPUP ================= */
.popup{
    position:fixed;
    top:20px;
    right:20px;
    background:white;
    padding:18px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    animation:slideIn 0.5s ease;
}

@keyframes slideIn{
    from{transform:translateX(100%); opacity:0;}
    to{transform:translateX(0); opacity:1;}
}

/* ================= RESPONSIVE ================= */
@media(max-width:768px){
    .dashboard{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        text-align:center;
    }

    .profile-section{
        flex-direction:column;
        text-align:center;
    }
}
.notice-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}

/* Notice Card Improved */
.notice-card{
    background:white;
    padding:18px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    border-left:5px solid #2c5364;

    height:280px; /* FIXED HEIGHT */
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    overflow:hidden;
}


.notice-card h3{
    font-size:16px;
    margin-bottom:6px;

    display:-webkit-box;
    -webkit-line-clamp:2; /* max 2 lines */
    -webkit-box-orient:vertical;
    overflow:hidden;
}

.notice-card p{
    font-size:14px;
    color:#555;

    display:-webkit-box;
    -webkit-line-clamp:3; /* max 3 lines */
    -webkit-box-orient:vertical;
    overflow:hidden;
}
/* Image fix */
.notice-img{
    width:100%;
    height:120px; /* FIXED HEIGHT */
    object-fit:cover;
    border-radius:10px;
    margin-top:8px;
}

/* File link */
.notice-card a{
    margin-top:10px;
    display:inline-block;
    color:#2c5364;
    font-weight:500;
}
/* Top section (title + date) */
.notice-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
}

/* Date badge */
.notice-date{
    font-size:12px;
    color:#777;
    background:#f1f3f6;
    padding:4px 8px;
    border-radius:6px;
    white-space:nowrap;
}
.notice-card:hover{
    transform:translateY(-5px);
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
/* Right section */
.right-section{
    display:flex;
    align-items:center;
    gap:6px;
}

/* NEW Badge */
.new-badge{
    background:#ff3b3b;
    color:white;
    font-size:11px;
    padding:3px 7px;
    border-radius:5px;
    font-weight:bold;
}

/* Highlight NEW notices */
.new-notice{
    border-left:5px solid #ff3b3b;
    background:#fff7f7;
}

/* Hover effect */
.notice-card:hover{
    transform:translateY(-5px);
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:1000;
}

.modal-content{
    background:white;
    padding:25px;
    border-radius:15px;
    width:90%;
    max-width:600px;
    max-height:80vh;
    overflow-y:auto;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

.close-btn{
    float:right;
    font-size:22px;
    cursor:pointer;
}

.modal img{
    width:100%;
    margin-top:10px;
    border-radius:10px;
}
</style>

</head>
<body>

<?php if($unread_count > 0){ ?>
<div class="popup">
🔔 <?php echo $unread_count; ?> new notices  
<a href="modules/notice/view_notice.php">View</a>
</div>
<?php } ?>

<div class="dashboard">


<!-- Sidebar -->
<div class="sidebar">
<h2>🎓 Student</h2>



<a href="modules/attendance/view_attendance.php">📊 Attendance</a>

<a href="modules/leave/apply_leave.php">📝 Leave</a>

<a href="modules/timetable/view_timetable.php">📅 Timetable</a>

<a href="modules/resources/view_resources.php">📚 Resources</a>

<a href="modules/notice/view_notice.php">
📢 Notices <span class="badge"><?php echo $unread_count; ?></span>
</a>

<a href="modules/complaint/raise_complaint.php">⚠ Complaints</a>

<a href="auth/logout.php">🚪 Logout</a>

</div>

<!-- Main -->
<div class="main-content">

<!-- Profile -->
<div class="header-card">
<div class="profile-section">
<img src="<?php echo htmlspecialchars($photo_path); ?>" class="profile-photo">
<div>
<h2><?php echo htmlspecialchars($student['name']); ?></h2>
<p><?php echo $student['roll_no']; ?></p>
<p><?php echo $student['course']." | ".$student['semester']; ?></p>
</div>
</div>
</div>

<!-- Analytics -->
<div class="analytics">
<div class="card">
<h3>Attendance</h3>
<p><?php echo $attendance_percent; ?>%</p>
</div>

<div class="card">
<h3>Leaves</h3>
<p><?php echo $leave; ?></p>
</div>

<div class="card">
<h3>Notices</h3>
<p><?php echo $unread_count; ?></p>
</div>

<div class="card">
<h3>Complaints</h3>
<p><?php echo $complaints; ?></p>
</div>
</div>

<!-- Chart -->
<div class="chart-card">
<h2>Attendance Trend</h2>
<canvas id="chart"></canvas>
</div>

<!-- Notices -->
<h2>Latest Notices</h2>
<div class="notice-grid">

<?php
if($notice_result && $notice_result->num_rows > 0){
    while($notice = $notice_result->fetch_assoc()){

        $title = htmlspecialchars($notice['title']);
        $content = htmlspecialchars($notice['content']);
        $files = htmlspecialchars($notice['notice_file']);

        // format date
        $date = date("d M Y", strtotime($notice['created_at']));

        echo "
        <div class='notice-card'
            onclick=\"openNotice('$title', `$content`, `$files`)\">

            <div class='notice-top'>
                <h3>$title</h3>
                <span class='notice-date'>$date</span>
            </div>

            <p>" . substr(strip_tags($notice['content']), 0, 90) . "...</p>

        </div>
        ";
    }
} else {
    echo "<p>No notices available</p>";
}
?>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById("chart"), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Attendance',
            data: <?php echo json_encode($data); ?>,
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // IMPORTANT
        plugins: {
            legend: { display: false }
        }
    }
});


function openNotice(title, content, files){
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalContent").innerText = content;

    let html = "";

    if(files){
        let fileList = files.split(",");

        fileList.forEach(file => {
            file = file.trim();
            let path = "uploads/notices/" + file;
            let ext = file.split('.').pop().toLowerCase();

            if(['jpg','jpeg','png'].includes(ext)){
                html += `<img src="${path}">`;
            } else {
                html += `<p><a href="${path}" target="_blank">📄 Open File</a></p>`;
            }
        });
    }

    document.getElementById("modalFiles").innerHTML = html;
    document.getElementById("noticeModal").style.display = "flex";
}

function closeNotice(){
    document.getElementById("noticeModal").style.display = "none";
}

// close on outside click
window.onclick = function(e){
    let modal = document.getElementById("noticeModal");
    if(e.target === modal){
        modal.style.display = "none";
    }
}
</script>
<div id="noticeModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeNotice()">✖</span>
        <h2 id="modalTitle"></h2>
        <p id="modalContent"></p>
        <div id="modalFiles"></div>
    </div>
</div>
</body>
</html>