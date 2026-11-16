<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* FILTER */
$search = $_GET['search'] ?? '';
$dept = $_GET['dept'] ?? '';

/* PAGINATION */
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

/* WHERE */
$where = "WHERE 1=1";

if (!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR faculty_id LIKE '%$search%')";
}

if (!empty($dept)) {
    $where .= " AND department='$dept'";
}

/* TOTAL */
$total = $conn->query("SELECT COUNT(*) as total FROM faculty $where")->fetch_assoc()['total'];
$pages = ceil($total / $limit);

/* DATA */
$result = $conn->query("
    SELECT * FROM faculty
    $where
    ORDER BY id DESC
    LIMIT $start, $limit
");

/* DEPARTMENTS */
$deptList = $conn->query("SELECT DISTINCT department FROM faculty");
?>

<!DOCTYPE html>
<html>
<head>
<title>Faculty Management</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{max-width:1400px;margin:auto;}

h2{text-align:center;}

/* FILTER */
.filter-box{
    display:flex;
    gap:10px;
    justify-content:center;
    margin-bottom:15px;
}

input,select{
    padding:10px;
    border-radius:6px;
    border:none;
    width:200px;
}

/* BUTTONS */
.btn{
    padding:8px 12px;
    border-radius:6px;
    color:white;
    text-decoration:none;
    border:none;
    cursor:pointer;
}

.add{background:#16a34a;}
.excel{background:#22c55e;}
.pdf{background:#ef4444;}
.edit{background:#f59e0b;}
.delete{background:#dc2626;}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:rgba(255,255,255,0.05);
}

th{
    background:#1e293b;
    color:#38bdf8;
    padding:10px;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

/* PHOTO */
img{
    width:40px;
    height:40px;
    border-radius:6px;
}

/* PAGINATION */
.pagination{
    margin-top:20px;
    text-align:center;
}

.pagination a{
    padding:6px 10px;
    margin:3px;
    background:#1e293b;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

.pagination a.active{
    background:#38bdf8;
    color:black;
}
</style>
</head>

<body>

<div class="container">

<h2>👨‍🏫 Faculty Management System</h2>


<div style="text-align:left; margin-bottom:15px;">
    <a href="../../admin_dashboard.php" class="btn" style="
        background:#334155;
        padding:10px 14px;
        border-radius:6px;
        color:white;
        text-decoration:none;
        display:inline-block;
    ">
        ⬅ Back to Dashboard
    </a>
</div>

<!-- FILTER -->
<form method="GET" class="filter-box">

<input type="text" name="search" placeholder="Search..." value="<?= $search ?>">

<select name="dept">
    <option value="">All Departments</option>
    <?php while($d = $deptList->fetch_assoc()) { ?>
        <option value="<?= $d['department'] ?>" <?= $dept==$d['department']?'selected':'' ?>>
            <?= $d['department'] ?>
        </option>
    <?php } ?>
</select>

<button class="btn add">Search</button>

<a href="export_faculty_excel.php" class="btn excel">Excel</a>
<a href="export_faculty_pdf.php" class="btn pdf">PDF</a>

</form>

<!-- TABLE -->
<table>
<tr>
    <th>ID</th>
    <th>Photo</th>
    <th>Name</th>
    <th>Email</th>
    <th>Faculty ID</th>
    <th>Department</th>
    <th>Mobile</th>
    <th>Designation</th>
    <th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['id'] ?></td>

    <td>
        <img src="../../uploads/<?= $row['photo'] ?>">
    </td>

    <td><?= $row['name'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['faculty_id'] ?></td>
    <td><?= $row['department'] ?></td>
    <td><?= $row['mobile'] ?></td>
    <td><?= $row['designation'] ?></td>

    <td>
        <button class="btn edit" onclick="editFaculty(<?= $row['id'] ?>)">Edit</button>
        <button class="btn delete" onclick="deleteFaculty(<?= $row['id'] ?>)">Delete</button>
    </td>
</tr>

<?php } ?>

</table>

<!-- PAGINATION -->
<div class="pagination">

<?php for($i=1;$i<=$pages;$i++){ ?>
    <a href="?page=<?= $i ?>&search=<?= $search ?>&dept=<?= $dept ?>"
       class="<?= $i==$page?'active':'' ?>">
        <?= $i ?>
    </a>
<?php } ?>

</div>

</div>

<script>
function editFaculty(id){
    window.location = "update_faculty.php?id="+id;
}

function deleteFaculty(id){
    if(confirm("Delete this faculty?")){
        window.location = "delete_faculty.php?id="+id;
    }
}
</script>

</body>
</html>