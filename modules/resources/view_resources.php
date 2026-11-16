<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login Required");
}

$result = $conn->query("
    SELECT id, title, description, class_name, course, subject, file_name, uploaded_by, created_at
    FROM resources
    ORDER BY id DESC
");

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Study Resources</title>

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#0f172a;
    color:white;
    padding:20px;
}

.container{
    max-width:1200px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(5, 1fr);
    gap:18px;
}

/* Laptop */
@media(max-width:1400px){
    .grid{
        grid-template-columns:repeat(4, 1fr);
    }
}

/* Tablet */
@media(max-width:1100px){
    .grid{
        grid-template-columns:repeat(3, 1fr);
    }
}

/* Small tablet */
@media(max-width:800px){
    .grid{
        grid-template-columns:repeat(2, 1fr);
    }
}

/* Mobile */
@media(max-width:500px){
    .grid{
        grid-template-columns:repeat(1, 1fr);
    }
}

/* CARD */
.card{
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:16px;
    overflow:hidden;
    transition:0.3s;

    display:flex;
    flex-direction:column;
    height:100%;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 25px rgba(0,0,0,0.35);
}

/* THUMBNAIL */
.thumb{
    width:100%;
    height:140px;
    background:#1e293b;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}

.thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* PDF ICON */
.pdf-box{
    font-size:40px;
    color:#ef4444;
}

/* CONTENT */
.content{
    padding:12px;
    display:flex;
    flex-direction:column;
    flex:1;
}

.desc{
    font-size:13px;
    color:#e2e8f0;
    flex:1;
    overflow:hidden;
}

.title{
    font-size:16px;
    font-weight:bold;
    color:#38bdf8;
    margin-bottom:6px;
}

.meta{
    font-size:12px;
    color:#cbd5e1;
    margin-bottom:8px;
}



/* BUTTON */
.btn{
    display:inline-block;
    margin-top:10px;
    padding:8px 12px;
    background:#22c55e;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-size:13px;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    padding:10px 14px;
    background:#334155;
    color:white;
    text-decoration:none;
    border-radius:8px;
}
</style>

</head>

<body>

<div class="container">

<a href="../../student_dashboard.php" class="back">⬅ Back to Dashboard</a>

<h2>📚 Study Resources</h2>

<div class="grid">

<?php while($row = $result->fetch_assoc()) { 

$file = $row['file_name'];
$path = "../../uploads/resources/" . $file;
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

?>

<div class="card">

   <!-- THUMBNAIL -->
  <div class="thumb">

<?php
$file = $row['file_name'];
$path = "../../uploads/resources/" . $file;
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
?>

<?php if(in_array($ext, ['jpg','jpeg','png','gif'])) { ?>

    <img src="<?= $path ?>">

<?php } elseif($ext == 'pdf') { ?>

    <!-- PDF FIRST PAGE PREVIEW -->
    <canvas class="pdf-thumb" data-pdf="<?= $path ?>"></canvas>

<?php } else { ?>

    <div class="pdf-box">📁 FILE</div>

<?php } ?>

</div>

    <!-- CONTENT -->
    <div class="content">

        <div class="title"><?= htmlspecialchars($row['title']) ?></div>

        <div class="meta">
            📘 <?= $row['class_name'] ?> |
            🎓 <?= $row['course'] ?> |
            📖 <?= $row['subject'] ?>
        </div>

        <div class="desc">
            <?= htmlspecialchars($row['description']) ?>
        </div>

        <a class="btn" href="<?= $path ?>" target="_blank">
            Open Resource
        </a>

    </div>

</div>

<?php } ?>

</div>

</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
const canvases = document.querySelectorAll(".pdf-thumb");

canvases.forEach(async (canvas) => {

    const url = canvas.getAttribute("data-pdf");

    const loadingTask = pdfjsLib.getDocument(url);

    const pdf = await loadingTask.promise;

    const page = await pdf.getPage(1); // FIRST PAGE

    const viewport = page.getViewport({ scale: 0.4 });

    const context = canvas.getContext("2d");

    canvas.height = viewport.height;
    canvas.width = viewport.width;

    await page.render({
        canvasContext: context,
        viewport: viewport
    }).promise;

});
</script>
</body>
</html>