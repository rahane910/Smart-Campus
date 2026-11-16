<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login Required");
}

$message = "";
$user_id = $_SESSION['user_id'];

/* ROLE BASED DASHBOARD */
$dashboard = "../../student_dashboard.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "faculty") {
        $dashboard = "../../faculty_dashboard.php";
    } elseif ($_SESSION['role'] == "admin") {
        $dashboard = "../../admin_dashboard.php";
    }
}

/* =========================
   APPLY LEAVE
========================= */
if (isset($_POST['submit'])) {

    $from = trim($_POST['from_date']);
    $to = trim($_POST['to_date']);
    $reason = trim($_POST['reason']);

    if (empty($from) || empty($to) || empty($reason)) {
        $message = "<p class='error'>❌ All fields are required.</p>";
    }
    elseif ($to < $from) {
        $message = "<p class='error'>❌ To Date cannot be earlier than From Date.</p>";
    }
    else {

        /* CHECK DUPLICATE REQUEST */
        $check = $conn->prepare("
            SELECT id 
            FROM leave_requests
            WHERE user_id=? AND from_date=? AND to_date=?
        ");

        if (!$check) {
            die("SQL Prepare Error: " . $conn->error);
        }

        $check->bind_param("iss", $user_id, $from, $to);
        $check->execute();
        $exists = $check->get_result();

        if ($exists->num_rows > 0) {
            $message = "<p class='error'>⚠ Leave already applied for selected dates.</p>";
        } 
        else {

            $stmt = $conn->prepare("
                INSERT INTO leave_requests
                (user_id, from_date, to_date, reason, status)
                VALUES (?, ?, ?, ?, 'Pending')
            ");

            if (!$stmt) {
                die("SQL Prepare Error: " . $conn->error);
            }

            $stmt->bind_param("isss", $user_id, $from, $to, $reason);

            if ($stmt->execute()) {
                $message = "<p class='success'>✅ Leave Applied Successfully!</p>";
            } else {
                $message = "<p class='error'>❌ Database Error: {$conn->error}</p>";
            }

            $stmt->close();
        }

        $check->close();
    }
}

/* =========================
   LEAVE HISTORY
========================= */
$history = $conn->prepare("
    SELECT *
    FROM leave_requests
    WHERE user_id=?
    ORDER BY from_date DESC
");

if (!$history) {
    die("SQL Prepare Error: " . $conn->error);
}

$history->bind_param("i", $user_id);
$history->execute();

$leaveResult = $history->get_result();

if (!$leaveResult) {
    die("SQL Result Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Apply Leave</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
    margin:0;
}

.container{
    max-width:950px;
    margin:auto;
}

.form-box{
    background:rgba(255,255,255,0.05);
    padding:25px;
    border-radius:12px;
    margin-bottom:25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:500;
}

input, textarea{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    margin-bottom:15px;
    outline:none;
}

button{
    background:#22c55e;
    color:white;
    padding:10px 16px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    opacity:0.9;
}

.back-btn{
    display:inline-block;
    margin-bottom:15px;
    padding:10px 14px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:8px;
}

.success{
    color:#22c55e;
    font-weight:bold;
}

.error{
    color:#ef4444;
    font-weight:bold;
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
    color:#38bdf8;
    padding:12px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

.Pending{
    color:#f59e0b;
    font-weight:bold;
}

.Approved{
    color:#22c55e;
    font-weight:bold;
}

.Rejected{
    color:#ef4444;
    font-weight:bold;
}
</style>
</head>
<body>

<div class="container">

<a href="<?= $dashboard ?>" class="back-btn">⬅ Back to Dashboard</a>

<div class="form-box">
    <h2>📝 Apply Leave</h2>

    <?= $message ?>

    <form method="POST">

        <label>From Date</label>
        <input type="date" name="from_date" required>

        <label>To Date</label>
        <input type="date" name="to_date" required>

        <label>Reason</label>
        <textarea name="reason" rows="4" required></textarea>

        <button type="submit" name="submit">Apply Leave</button>

    </form>
</div>

<h2>📋 Leave History</h2>

<table>
<tr>
    <th>From Date</th>
    <th>To Date</th>
    <th>Total Days</th>
    <th>Reason</th>
    <th>Status</th>
</tr>

<?php if($leaveResult->num_rows > 0){ ?>

    <?php while($row = $leaveResult->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['from_date'] ?></td>
        <td><?= $row['to_date'] ?></td>

        <td>
            <?= floor((strtotime($row['to_date']) - strtotime($row['from_date'])) / 86400) + 1 ?>
        </td>

        <td><?= htmlspecialchars($row['reason']) ?></td>

        <td class="<?= $row['status'] ?>">
            <?= $row['status'] ?>
        </td>
    </tr>
    <?php } ?>

<?php } else { ?>

    <tr>
        <td colspan="5">No leave history found</td>
    </tr>

<?php } ?>

</table>

</div>
</body>
</html>