<?php
session_start();
include("../../config/db.php");

/* ADMIN CHECK */
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* PAGINATION */
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) AS total FROM students");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* DATA */
$result = $conn->query("
    SELECT * FROM students
    ORDER BY id DESC
    LIMIT $start, $limit
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Students</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{
    max-width:1300px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:15px;
}

.top-bar{
    display:flex;
    justify-content:space-between;
    margin-bottom:15px;
}

.btn{
    padding:10px 14px;
    border-radius:8px;
    text-decoration:none;
    color:white;
    font-weight:bold;
}

.back{background:#1e293b;}
.excel{background:#16a34a;}
.pdf{background:#dc2626;}

.search-box{
    text-align:center;
    margin-bottom:15px;
}

.search-box input{
    width:320px;
    padding:10px;
    border-radius:8px;
    border:none;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:rgba(255,255,255,0.05);
    table-layout:fixed;
}

th,td{
    padding:10px;
    font-size:12px;
    text-align:center;
    color:#e2e8f0;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

th{
    background:#1e293b;
    color:#38bdf8;
}

td{
    word-wrap:break-word;
    white-space:normal;
}

tr:hover{
    background:rgba(255,255,255,0.06);
}

/* PHOTO */
img.photo{
    width:40px;
    height:40px;
    border-radius:6px;
    object-fit:cover;
    border:2px solid #38bdf8;
    cursor:pointer;
}

/* BUTTONS */
.edit-btn{
    background:#f59e0b;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    color:white;
    cursor:pointer;
}

.delete-btn{
    background:#dc2626;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    color:white;
    cursor:pointer;
}

/* PAGINATION */
.pagination{
    text-align:center;
    margin-top:20px;
}

.pagination a{
    padding:8px 12px;
    margin:3px;
    background:#1e293b;
    color:white;
    text-decoration:none;
    border-radius:6px;
}

.pagination a.active{
    background:#38bdf8;
    color:#0f172a;
}

/* MODAL */
#imgModal, #editModal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

#modalImg{
    max-width:80%;
    max-height:80%;
    border-radius:10px;
    border:3px solid #38bdf8;
}

.edit-box{
    background:white;
    padding:20px;
    border-radius:10px;
    width:400px;
    color:black;
}
</style>
</head>

<body>

<div class="container">

<!-- TOP -->
<div class="top-bar">
    <a href="../../admin_dashboard.php" class="btn back">⬅ Dashboard</a>
    <div>
        <a href="export_students_excel.php" class="btn excel">⬇ Excel</a>
        <a href="export_students_pdf.php" class="btn pdf">⬇ PDF</a>
    </div>
</div>

<h2>👨‍🎓 Manage Students</h2>

<!-- SEARCH -->
<div class="search-box">
    <input type="text" placeholder="Search students..."
           onkeyup="searchStudents(this.value)">
</div>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Photo</th>
    <th>Email</th>
    <th>Student ID</th>
    <th>Roll</th>
    <th>PRN</th>
    <th>Course</th>
    <th>Sem</th>
    <th>Dept</th>
    <th>Mobile</th>
    <th>Actions</th>
</tr>
</thead>

<tbody id="studentTable">

<?php while($row = $result->fetch_assoc()) { ?>

<?php
$photoPath = "../../uploads/".$row['photo'];
$photo = (!empty($row['photo']) && file_exists($photoPath))
    ? $photoPath
    : "../../assets/default.png";
?>

<tr>

    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>

    <td>
        <img src="<?= $photo ?>" class="photo" onclick="openImage(this.src)">
    </td>

    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= $row['student_id'] ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['prn_no'] ?></td>
    <td><?= $row['course'] ?></td>
    <td><?= $row['semester'] ?></td>
    <td><?= $row['department'] ?></td>
    <td><?= $row['mobile'] ?></td>

    <td>
        <button class="edit-btn" onclick="editStudent(<?= $row['id'] ?>)">Edit</button>
        <button class="delete-btn" onclick="deleteStudent(<?= $row['id'] ?>)">Del</button>
    </td>

</tr>

<?php } ?>

</tbody>
</table>

<!-- PAGINATION -->
<div class="pagination">
<?php for($i=1;$i<=$totalPages;$i++){ ?>
<a href="?page=<?= $i ?>" class="<?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
<?php } ?>
</div>

</div>

<!-- IMAGE MODAL -->
<div id="imgModal">
    <img id="modalImg">
</div>

<!-- EDIT MODAL -->
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
    z-index:9999;
">

<div style="
    background:white;
    padding:20px;
    border-radius:10px;
    width:500px;
    max-height:90vh;
    overflow:auto;
">

<h3>Edit Student (Full Details)</h3>

<input type="hidden" id="edit_id">

<input type="text" id="name" placeholder="Name" style="width:100%;margin:5px 0;">
<input type="email" id="email" placeholder="Email" style="width:100%;margin:5px 0;">
<input type="text" id="student_id" placeholder="Student ID" style="width:100%;margin:5px 0;">
<input type="text" id="roll_no" placeholder="Roll No" style="width:100%;margin:5px 0;">
<input type="text" id="prn_no" placeholder="PRN No" style="width:100%;margin:5px 0;">
<input type="text" id="course" placeholder="Course" style="width:100%;margin:5px 0;">
<input type="text" id="semester" placeholder="Semester" style="width:100%;margin:5px 0;">
<input type="text" id="department" placeholder="Department" style="width:100%;margin:5px 0;">
<input type="text" id="admission_year" placeholder="Admission Year" style="width:100%;margin:5px 0;">
<input type="date" id="dob" style="width:100%;margin:5px 0;">
<input type="text" id="gender" placeholder="Gender" style="width:100%;margin:5px 0;">
<input type="text" id="mobile" placeholder="Mobile" style="width:100%;margin:5px 0;">
<input type="text" id="parent_mobile" placeholder="Parent Mobile" style="width:100%;margin:5px 0;">
<textarea id="address" placeholder="Address" style="width:100%;margin:5px 0;"></textarea>

<button onclick="saveStudent()" style="
    background:#16a34a;
    color:white;
    padding:10px;
    border:none;
    width:100%;
    border-radius:6px;
">
Update Student
</button>

</div>
</div>

<script>

/* SEARCH */
function searchStudents(value){
    fetch("search_student.php?query="+value)
    .then(res=>res.text())
    .then(data=>{
        document.getElementById("studentTable").innerHTML=data;
    });
}

/* IMAGE */
function openImage(src){
    document.getElementById("imgModal").style.display="flex";
    document.getElementById("modalImg").src=src;
}
document.getElementById("imgModal").onclick=function(){
    this.style.display="none";
};

/* EDIT */
function editStudent(id){
    fetch("get_student.php?id="+id)
    .then(res => res.json())
    .then(data => {

        document.getElementById("edit_id").value = data.id;

        document.getElementById("name").value = data.name;
        document.getElementById("email").value = data.email;
        document.getElementById("student_id").value = data.student_id;
        document.getElementById("roll_no").value = data.roll_no;
        document.getElementById("prn_no").value = data.prn_no;
        document.getElementById("course").value = data.course;
        document.getElementById("semester").value = data.semester;
        document.getElementById("department").value = data.department;
        document.getElementById("admission_year").value = data.admission_year;
        document.getElementById("dob").value = data.dob;
        document.getElementById("gender").value = data.gender;
        document.getElementById("mobile").value = data.mobile;
        document.getElementById("parent_mobile").value = data.parent_mobile;
        document.getElementById("address").value = data.address;

        document.getElementById("editModal").style.display = "flex";
    });
}
/* SAVE */
function saveStudent(){

    let fd = new FormData();

    fd.append("id", document.getElementById("edit_id").value);
    fd.append("name", document.getElementById("name").value);
    fd.append("email", document.getElementById("email").value);
    fd.append("student_id", document.getElementById("student_id").value);
    fd.append("roll_no", document.getElementById("roll_no").value);
    fd.append("prn_no", document.getElementById("prn_no").value);
    fd.append("course", document.getElementById("course").value);
    fd.append("semester", document.getElementById("semester").value);
    fd.append("department", document.getElementById("department").value);
    fd.append("admission_year", document.getElementById("admission_year").value);
    fd.append("dob", document.getElementById("dob").value);
    fd.append("gender", document.getElementById("gender").value);
    fd.append("mobile", document.getElementById("mobile").value);
    fd.append("parent_mobile", document.getElementById("parent_mobile").value);
    fd.append("address", document.getElementById("address").value);

    fetch("update_student.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        location.reload();
    });
}

/* DELETE */
function deleteStudent(id){
    if(confirm("Delete this student?")){
        fetch("delete_student.php?id="+id)
        .then(res=>res.text())
        .then(msg=>{
            alert(msg);
            location.reload();
        });
    }
}

</script>

</body>
</html>