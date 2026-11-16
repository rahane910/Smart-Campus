<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* =========================
   ADD DEPARTMENT
========================= */
if (isset($_POST['add'])) {

    $dept = $_POST['department_name'] ?? '';
    $hod  = $_POST['hod_name'] ?? '';

    if ($dept == '' || $hod == '') {
        die("Please fill all fields");
    }

    $stmt = $conn->prepare("
        INSERT INTO departments (department_name, hod_name)
        VALUES (?, ?)
    ");

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ss", $dept, $hod);
    $stmt->execute();
}

if (isset($_POST['update'])) {

    $id = intval($_POST['id']);
    $dept = $_POST['department_name'] ?? '';
    $hod  = $_POST['hod_name'] ?? '';

    $stmt = $conn->prepare("
        UPDATE departments 
        SET department_name=?, hod_name=? 
        WHERE id=?
    ");

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ssi", $dept, $hod, $id);
    $stmt->execute();
}

/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/* =========================
   FETCH DATA
========================= */
$result = $conn->query("SELECT * FROM departments ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Department Management</title>

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
    margin-bottom:20px;
}

/* FORM */
.card{
    background:rgba(255,255,255,0.05);
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

input{
    width:48%;
    padding:10px;
    margin:5px;
    border:none;
    border-radius:6px;
}

button{
    padding:10px 14px;
    border:none;
    border-radius:6px;
    background:#16a34a;
    color:white;
    cursor:pointer;
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
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

.action a{
    color:#ef4444;
    text-decoration:none;
    font-weight:bold;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    color:white;
    text-decoration:none;
    background:#334155;
    padding:8px 12px;
    border-radius:6px;
}
</style>
</head>

<body>

<div class="container">

<a href="../../admin_dashboard.php" class="back">⬅ Back to Dashboard</a>

<h2>🏢 Department Management</h2>

<!-- ADD FORM -->
<div class="card">
<form method="POST">
    <input type="text" name="department_name" placeholder="Department Name" required>
    <input type="text" name="hod_name" placeholder="HOD Name" required>
    <br>
    <button name="add">➕ Add Department</button>
</form>
</div>

<!-- TABLE -->
<div class="card">

<table>
<tr>
    <th>ID</th>
    <th>Department</th>
    <th>HOD</th>
    <th>Created At</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['department_name']) ?></td>
    <td><?= htmlspecialchars($row['hod_name']) ?></td>
    <td><?= $row['created_at'] ?></td>
    <td class="action">
        <button class="btn-edit"
    onclick="openEditModal(
        '<?= $row['id'] ?>',
        '<?= htmlspecialchars($row['department_name']) ?>',
        '<?= htmlspecialchars($row['hod_name']) ?>'
    )">
    ✏ Edit
</button>

<a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete?')" class="btn-delete">
    🗑 Delete
</a>
    </td>
</tr>

<?php } ?>

</table>

</div>

</div>

<div id="editModal" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
">

<div style="
    background:white;
    padding:20px;
    border-radius:10px;
    width:350px;
    color:black;
">

<h3>Edit Department</h3>

<form method="POST">

    <input type="hidden" name="id" id="edit_id">

    <input type="text" name="department_name" id="edit_dept"
           placeholder="Department Name"
           style="width:100%;padding:10px;margin:5px 0;">

    <input type="text" name="hod_name" id="edit_hod"
           placeholder="HOD Name"
           style="width:100%;padding:10px;margin:5px 0;">

    <button name="update" style="
        width:100%;
        padding:10px;
        background:#16a34a;
        color:white;
        border:none;
        border-radius:6px;
    ">
        Update
    </button>

</form>

<br>
<button onclick="closeModal()" style="
    width:100%;
    padding:8px;
    background:#dc2626;
    color:white;
    border:none;
    border-radius:6px;
">
    Close
</button>

</div>
</div>


<script>
function openEditModal(id, dept, hod) {
    document.getElementById("editModal").style.display = "flex";

    document.getElementById("edit_id").value = id;
    document.getElementById("edit_dept").value = dept;
    document.getElementById("edit_hod").value = hod;
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>