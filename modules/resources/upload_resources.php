<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    die("Access Denied");
}

$faculty_id = $_SESSION['user_id'];

/* =========================
   FILTER VALUES
========================= */
$class_filter = $_GET['class'] ?? "";
$course_filter = $_GET['course'] ?? "";

/* =========================
   DELETE RESOURCE
========================= */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT file_name FROM resources WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $fileData = $stmt->get_result()->fetch_assoc();

    if ($fileData) {
        $filePath = __DIR__ . "/../../uploads/resources/" . $fileData['file_name'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM resources WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: upload_resources.php");
    exit();
}

/* =========================
   UPLOAD RESOURCE (SAFE)
========================= */
if (isset($_POST['upload'])) {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $class_name = $_POST['class_name'];
    $course = $_POST['course'];
    $subject = $_POST['subject'];

    if (!isset($_FILES['file']) || $_FILES['file']['name'] == "") {
        die("❌ Please select a file");
    }

    $file = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];

    $uploadDir = __DIR__ . "/../../uploads/resources/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFile = time() . "_" . basename($file);
    $path = $uploadDir . $newFile;

    if (move_uploaded_file($tmp, $path)) {

        $stmt = $conn->prepare("
            INSERT INTO resources
            (title, description, class_name, course, subject, file_name, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            die("SQL ERROR: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssi",
            $title,
            $description,
            $class_name,
            $course,
            $subject,
            $newFile,
            $faculty_id
        );

        $stmt->execute();

        echo "<script>alert('✅ Resource Uploaded Successfully'); window.location.href='upload_resources.php';</script>";

    } else {
        die("❌ File upload failed");
    }
}

/* =========================
   FETCH CLASS & COURSE
========================= */
$class_list = $conn->query("
    SELECT DISTINCT class_name 
    FROM students 
    WHERE class_name IS NOT NULL AND class_name != ''
");

if (!$class_list) {
    die("SQL ERROR (class): " . $conn->error);
}

$course_list = $conn->query("
    SELECT DISTINCT course 
    FROM students 
    WHERE course IS NOT NULL AND course != ''
");

if (!$course_list) {
    die("SQL ERROR (course): " . $conn->error);
}

/* =========================
   FETCH RESOURCES
========================= */
$sql = "SELECT * FROM resources WHERE 1=1";

if ($class_filter != "") {
    $sql .= " AND class_name='" . $conn->real_escape_string($class_filter) . "'";
}

if ($course_filter != "") {
    $sql .= " AND course='" . $conn->real_escape_string($course_filter) . "'";
}

$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("SQL ERROR (resources fetch): " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Resources</title>

<style>
body{
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    margin:0;
    padding:20px;
    color:#e5e7eb;
}

/* MAIN CONTAINER */
.container{
    max-width:1100px;
    margin:auto;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    padding:25px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
    border:1px solid rgba(255,255,255,0.08);
}

/* TITLE */
h2, h3{
    color:#38bdf8;
    margin-bottom:10px;
}

/* BACK LINK */
a{
    color:#60a5fa;
    text-decoration:none;
    font-weight:500;
}
a:hover{
    color:#38bdf8;
}

/* FILTER BOX */
form{
    margin-top:10px;
}

select, input{
    padding:12px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.15);
    background:#0f172a;
    color:white;
    outline:none;
    width:100%;
    margin-bottom:10px;
}

button{
    padding:12px 18px;
    border:none;
    border-radius:10px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform: translateY(-2px);
    box-shadow:0 6px 15px rgba(59,130,246,0.4);
}

/* CARD DESIGN */
.card{
    background: rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);
    padding:18px;
    border-radius:14px;
    margin-bottom:15px;
    transition:0.3s;
}

.card:hover{
    transform: translateY(-3px);
    background: rgba(255,255,255,0.09);
}

/* META INFO */
.meta{
    font-size:13px;
    color:#94a3b8;
    margin-bottom:8px;
}

/* FILE LINK */
.card a{
    display:inline-block;
    margin-top:8px;
    color:#38bdf8;
}

/* DELETE BUTTON */
.delete{
    color:#f87171 !important;
    float:right;
}

/* UPLOAD BOX */
.upload-box{
    background: rgba(255,255,255,0.04);
    padding:15px;
    border-radius:12px;
    margin-bottom:20px;
    border:1px solid rgba(255,255,255,0.08);
}
</style>
</head>

<body>

<div class="container">

<h2>📚 Resources Module</h2>

<a href="../../faculty_dashboard.php">⬅ Back</a>

<hr>

<!-- FILTER -->
<form method="GET">
    <select name="class">
        <option value="">All Classes</option>
        <?php while($c = $class_list->fetch_assoc()) { ?>
            <option value="<?= $c['class_name'] ?>" <?= $class_filter==$c['class_name']?'selected':'' ?>>
                <?= $c['class_name'] ?>
            </option>
        <?php } ?>
    </select>

    <select name="course">
        <option value="">All Courses</option>
        <?php while($c = $course_list->fetch_assoc()) { ?>
            <option value="<?= $c['course'] ?>" <?= $course_filter==$c['course']?'selected':'' ?>>
                <?= $c['course'] ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Filter</button>
</form>

<hr>

<!-- UPLOAD -->
<h3>Upload Resource</h3>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br><br>
    <input type="text" name="description" placeholder="Description"><br><br>

    <select name="class_name" required>
        <option value="">Select Class</option>
        <?php
        $cls = $conn->query("SELECT DISTINCT class_name FROM students");
        while($c = $cls->fetch_assoc()){
            echo "<option>{$c['class_name']}</option>";
        }
        ?>
    </select><br><br>

    <input type="text" name="course" placeholder="Course" required><br><br>
    <input type="text" name="subject" placeholder="Subject" required><br><br>
    <input type="file" name="file" required><br><br>

    <button type="submit" name="upload">Upload</button>
</form>

<hr>

<!-- LIST -->
<h3>Resources</h3>

<?php while($row = $result->fetch_assoc()) { ?>

<div class="card">
    <h4><?= $row['title'] ?></h4>

    <div class="meta">
        <?= $row['class_name'] ?> |
        <?= $row['course'] ?> |
        <?= $row['subject'] ?>
    </div>

    <p><?= $row['description'] ?></p>

    <a href="../../uploads/resources/<?= $row['file_name'] ?>" target="_blank">
        View File
    </a>

    |
    <a class="delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete?')">
        Delete
    </a>
</div>

<?php } ?>

</div>

</body>
</html>