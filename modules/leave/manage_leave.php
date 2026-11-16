<?php
session_start();
include("../../config/db.php");

// Total leaves
$totalLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests")
    ->fetch_assoc()['total'];

// Approved
$approvedLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status='Approved'")
    ->fetch_assoc()['total'];

// Rejected
$rejectedLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status='Rejected'")
    ->fetch_assoc()['total'];

// Pending
$pendingLeaves = $conn->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status='Pending'")
    ->fetch_assoc()['total'];

// Monthly trend (last 6 months)
$monthlyData = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        COUNT(*) AS total
    FROM leave_requests
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
");

$months = [];
$counts = [];

while($row = $monthlyData->fetch_assoc()){
    $months[] = $row['month'];
    $counts[] = $row['total'];
}

// reverse for proper order
$months = array_reverse($months);
$counts = array_reverse($counts);


if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* APPROVE */
if (isset($_POST['approve'])) {
    $id = intval($_POST['leave_id']);

    $stmt = $conn->prepare("UPDATE leave_requests SET status='Approved' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/* REJECT */
if (isset($_POST['reject'])) {
    $id = intval($_POST['leave_id']);

    $stmt = $conn->prepare("UPDATE leave_requests SET status='Rejected' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/* FILTER */
$status = $_GET['status'] ?? 'All';

if ($status == 'All') {
    $sql = "
        SELECT leave_requests.*, students.name
        FROM leave_requests
       JOIN students ON leave_requests.student_id = students.student_id
        ORDER BY leave_requests.id DESC
    ";
} else {
    $sql = "
        SELECT leave_requests.*, students.name
        FROM leave_requests
        JOIN students ON leave_requests.student_id = students.student_id
        WHERE leave_requests.status='$status'
        ORDER BY leave_requests.id DESC
    ";
}

$result = $conn->query($sql);

if (!$result) {
    die("SQL ERROR: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Leave Management</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{
    font-family: 'Segoe UI', sans-serif;
    background: #0f172a;
    margin:0;
    padding:20px;
    color:#e2e8f0;
}

/* TITLE */
h2{
    text-align:center;
    margin-bottom:20px;
    font-size:26px;
}

/* DASHBOARD GRID */
.analytics-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-top:20px;
}

/* GLASS CARDS */
.card{
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    padding:20px;
    border-radius:16px;
    text-align:center;
    transition:0.3s;
}

.card:hover{
    transform: translateY(-5px);
    background: rgba(255,255,255,0.12);
}

.card h2{
    font-size:30px;
    margin-top:10px;
    color:#38bdf8;
}

/* FILTER BAR */
.filter{
    text-align:center;
    margin:25px 0;
}

.filter a{
    padding:10px 18px;
    margin:5px;
    text-decoration:none;
    background:#1e293b;
    color:white;
    border-radius:8px;
    transition:0.3s;
}

.filter a:hover{
    background:#38bdf8;
    color:#0f172a;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background: rgba(255,255,255,0.06);
    border-radius:14px;
    overflow:hidden;
    backdrop-filter: blur(10px);
}

th{
    background:#1e293b;
    color:#38bdf8;
    padding:14px;
    font-size:14px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

tr:hover{
    background: rgba(255,255,255,0.05);
}

/* STATUS BADGES */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.Pending{background:#f59e0b;color:black;}
.Approved{background:#22c55e;color:black;}
.Rejected{background:#ef4444;color:white;}

/* BUTTONS */
button{
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.approve{background:#22c55e;}
.reject{background:#ef4444;}

/* CHART AREA */
.chart-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:25px;
}

.chart-box{
    background: rgba(255,255,255,0.06);
    padding:20px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,0.1);
}

/* RESPONSIVE */
@media(max-width:900px){
    .analytics-grid{
        grid-template-columns:1fr 1fr;
    }

    .chart-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:600px){
    .analytics-grid{
        grid-template-columns:1fr;
    }
}
.back-btn{
    display:inline-block;
    padding:10px 18px;
    background:linear-gradient(135deg,#2563eb,#38bdf8);
    color:white;
    text-decoration:none;
    border-radius:10px;
    font-weight:bold;
    transition:0.3s;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.back-btn:hover{
    transform:translateY(-2px);
    opacity:0.9;
}
</style>
</head>

<body>

<h2>📝 Leave Management dashboard</h2>

<div style="text-align:center; margin-bottom:15px;">
    <a href="../../admin_dashboard.php" 
       style="
        display:inline-block;
        padding:10px 18px;
        background:#1e293b;
        color:#fff;
        text-decoration:none;
        border-radius:8px;
        transition:0.3s;
       "
       onmouseover="this.style.background='#38bdf8'; this.style.color='#0f172a';"
       onmouseout="this.style.background='#1e293b'; this.style.color='#fff';">
        ⬅ Back to Dashboard
    </a>
</div>



<!-- ANALYTICS CARDS -->
<div class="analytics-grid">

    <div class="card">
        <h4>Total Leaves</h4>
        <h2><?= $totalLeaves ?></h2>
    </div>

    <div class="card">
        <h4>Approved</h4>
        <h2><?= $approvedLeaves ?></h2>
    </div>

    <div class="card">
        <h4>Rejected</h4>
        <h2><?= $rejectedLeaves ?></h2>
    </div>

    <div class="card">
        <h4>Pending</h4>
        <h2><?= $pendingLeaves ?></h2>
    </div>

</div>

<!-- CHARTS -->
<div class="chart-grid">

    <div class="chart-box">
        <h3>📊 Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>📈 Monthly Trend</h3>
        <canvas id="monthlyChart"></canvas>
    </div>

</div>

<!-- FILTER -->
<div class="filter">
    <a href="?status=All">All</a>
    <a href="?status=Pending">Pending</a>
    <a href="?status=Approved">Approved</a>
    <a href="?status=Rejected">Rejected</a>
</div>

<table>
<tr>
<th>Student</th>
<th>Reason</th>
<th>From</th>
<th>To</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['reason']) ?></td>
<td><?= $row['from_date'] ?></td>
<td><?= $row['to_date'] ?></td>

<td>
<span class="status <?= $row['status'] ?>">
    <?= $row['status'] ?>
</span>
</td>

<td>

<?php if($row['status'] == 'Pending') { ?>

<form method="POST" style="display:inline;">
    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
    <button class="approve" name="approve">Approve</button>
</form>

<form method="POST" style="display:inline;">
    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
    <button class="reject" name="reject">Reject</button>
</form>

<?php } else { ?>
    <span style="color:gray;">Done</span>
<?php } ?>

</td>
</tr>

<?php } ?>

</table>
<script>
// Status Pie Chart
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Approved', 'Rejected', 'Pending'],
        datasets: [{
            data: [
                <?= $approvedLeaves ?>,
                <?= $rejectedLeaves ?>,
                <?= $pendingLeaves ?>
            ]
        }]
    }
});

// Monthly Trend Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Leaves Per Month',
            data: <?= json_encode($counts) ?>,
            fill: true,
            tension: 0.4
        }]
    }
});
</script>
</body>
</html>