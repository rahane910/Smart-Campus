<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $file = $conn->query("SELECT notice_file FROM notices WHERE id=$id")->fetch_assoc();

    if (!empty($file['notice_file'])) {
        $path = "../../uploads/notices/" . $file['notice_file'];
        if (file_exists($path)) unlink($path);
    }

    $conn->query("DELETE FROM notices WHERE id=$id");
    header("Location: manage_notices.php");
    exit();
}

/* DATA */
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Notice Dashboard</title>

<style>
body{
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    margin:0;
}

/* TOP BAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 25px;
    background:#111827;
    position:sticky;
    top:0;
}

.topbar h2{
    margin:0;
    font-size:20px;
}

/* BUTTON */
.btn{
    padding:9px 14px;
    border-radius:8px;
    border:none;
    cursor:pointer;
    text-decoration:none;
    color:white;
    font-weight:500;
}

.add{
    background:#22c55e;
}

/* CONTAINER */
.container{
    padding:20px;
}

/* CARD TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:rgba(255,255,255,0.04);
    border-radius:12px;
    overflow:hidden;
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

/* ACTION BUTTONS */
.edit{background:#f59e0b;}
.delete{background:#ef4444;}
.view{background:#38bdf8;}

/* FILE BADGE */
.file-btn{
    background:#22c55e;
    padding:6px 10px;
    border-radius:6px;
    color:white;
    text-decoration:none;
    font-size:12px;
}

/* ROW HOVER */
tr:hover{
    background:rgba(255,255,255,0.05);
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.85);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-box{
    background:#fff;
    color:#000;
    width:450px;
    padding:20px;
    border-radius:10px;
}

input,textarea{
    width:100%;
    padding:10px;
    margin:8px 0;
    border-radius:6px;
    border:1px solid #ddd;
}
</style>
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">
    <h2>📢 Notice Management</h2>

    <div>
        <a class="btn add" href="post_notice.php">+ Add Notice</a>
        <a class="btn view" href="../../admin_dashboard.php">Dashboard</a>
    </div>
</div>

<div class="container">

<table>
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Content</th>
    <th>File</th>
    <th>Date</th>
    <th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['title']) ?></td>
    <td><?= htmlspecialchars(substr($row['content'],0,50)) ?>...</td>

    <td>
        <?php if($row['notice_file']) { ?>
            <button class="file-btn"
                onclick="openPreview('<?= $row['notice_file'] ?>')">
                View File
            </button>
        <?php } else echo "No File"; ?>
    </td>

    <td><?= $row['created_at'] ?></td>

    <td>
        <button class="btn edit"
            onclick="openEdit(
                <?= $row['id'] ?>,
                '<?= addslashes($row['title']) ?>',
                '<?= addslashes($row['content']) ?>'
            )">
            Edit
        </button>

        <a class="btn delete"
           href="?delete=<?= $row['id'] ?>"
           onclick="return confirm('Delete this notice?')">
           Delete
        </a>
    </td>
</tr>

<?php } ?>

</table>

</div>

<!-- FILE PREVIEW MODAL -->
<div class="modal" id="previewModal">
    <div class="modal-box" style="width:80%;height:80%;">
        <iframe id="fileFrame" style="width:100%;height:100%;border:none;"></iframe>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="editModal">
    <div class="modal-box">

        <h3>Edit Notice</h3>

        <form method="POST" action="manage_notices.php">

            <input type="hidden" name="id" id="edit_id">

            <label>Title</label>
            <input type="text" name="title" id="edit_title" required>

            <label>Content</label>
            <textarea name="content" id="edit_content" required></textarea>

            <button class="btn add" name="update_notice">Update</button>
            <button type="button" class="btn delete" onclick="closeEdit()">Close</button>

        </form>

    </div>
</div>

<script>
function openEdit(id,title,content){
    document.getElementById("edit_id").value=id;
    document.getElementById("edit_title").value=title;
    document.getElementById("edit_content").value=content;
    document.getElementById("editModal").style.display="flex";
}

function closeEdit(){
    document.getElementById("editModal").style.display="none";
}

function openPreview(file){
    document.getElementById("fileFrame").src="../../uploads/notices/"+file;
    document.getElementById("previewModal").style.display="flex";
}

window.onclick = function(e){
    if(e.target.classList.contains("modal")){
        e.target.style.display="none";
    }
}
</script>

</body>
</html>