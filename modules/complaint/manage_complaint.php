<?php
session_start();
include("../../config/db.php");

/* =========================
   SESSION FIX (IMPORTANT)
========================= */
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') != "faculty") {
    die("Access Denied");
}

/* =========================
   UPDATE STATUS (SECURE)
========================= */
if (isset($_GET['id']) && isset($_GET['status'])) {

    $id = intval($_GET['id']);
    $status = $_GET['status'];

    $allowed = ['Pending', 'Resolved', 'Rejected'];

    if (in_array($status, $allowed)) {

        $stmt = $conn->prepare("UPDATE complaints SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        header("Location: manage_complaints.php");
        exit();
    }
}

/* =========================
   FETCH COMPLAINTS
   (FIXED JOIN)
========================= */
$sql = "
SELECT c.*, s.name AS student_name
FROM complaints c
JOIN students s ON c.student_id = s.id
ORDER BY c.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Complaints</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

/* CONTAINER */
.container{
    max-width:1100px;
    margin:auto;
    background:#111827;
    padding:20px;
    border-radius:12px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th, td{
    border:1px solid #1f2937;
    padding:12px;
    text-align:left;
}

th{
    background:#1e293b;
    color:#38bdf8;
}

/* BUTTONS */
.btn{
    padding:6px 10px;
    border-radius:6px;
    color:white;
    text-decoration:none;
    font-size:13px;
    margin-right:5px;
    display:inline-block;
}

.pending{ background:#f59e0b; color:black; }
.resolved{ background:#22c55e; }
.rejected{ background:#ef4444; }

/* STATUS */
.status{
    padding:5px 10px;
    border-radius:8px;
    font-size:12px;
}

.Pending{background:#f59e0b;color:black;}
.Resolved{background:#22c55e;color:black;}
.Rejected{background:#ef4444;color:white;}

/* BACK BUTTON */
.back-btn{
    display:inline-block;
    padding:10px 16px;
    background:#1e293b;
    color:white;
    text-decoration:none;
    border-radius:8px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="container">

<a href="../../faculty_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<h2>🛠 Manage Complaints</h2>

<table>

<tr>
    <th>ID</th>
    <th>Student</th>
    <th>Complaint</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>

    <td><?= $row['id'] ?></td>

    <td><?= htmlspecialchars($row['student_name']) ?></td>

    <td><?= nl2br(htmlspecialchars($row['complaint_text'] ?? '')) ?></td>

    <td>
        <span class="status <?= $row['status'] ?>">
            <?= $row['status'] ?>
        </span>
    </td>

    <td><?= $row['created_at'] ?></td>

    <td>

        <a class="btn pending"
           href="?id=<?= $row['id'] ?>&status=Pending">
           Pending
        </a>

        <a class="btn resolved"
           href="?id=<?= $row['id'] ?>&status=Resolved">
           Resolve
        </a>

        <a class="btn rejected"
           href="?id=<?= $row['id'] ?>&status=Rejected"
           onclick="return confirm('Reject this complaint?')">
           Reject
        </a>

    </td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>