<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    die("Access Denied");
}

$faculty_id = $_SESSION['user_id'];

/* =========================
   DELETE EVENT
========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $conn->query("
        DELETE FROM faculty_calendar
        WHERE id='$id' AND faculty_id='$faculty_id'
    ");

    header("Location: faculty_calendar.php");
    exit();
}

/* =========================
   ADD EVENT
========================= */
if (isset($_POST['add_event'])) {

    $title = $conn->real_escape_string($_POST['event_title']);
    $date  = $conn->real_escape_string($_POST['event_date']);
    $type  = $conn->real_escape_string($_POST['event_type']);

    $conn->query("
        INSERT INTO faculty_calendar (faculty_id, event_title, event_date, event_type)
        VALUES ('$faculty_id', '$title', '$date', '$type')
    ");

    header("Location: faculty_calendar.php");
    exit();
}

/* =========================
   UPDATE EVENT
========================= */
if (isset($_POST['update_event'])) {

    $id    = intval($_POST['event_id']);
    $title = $conn->real_escape_string($_POST['event_title']);
    $date  = $conn->real_escape_string($_POST['event_date']);
    $type  = $conn->real_escape_string($_POST['event_type']);

    $conn->query("
        UPDATE faculty_calendar SET
        event_title='$title',
        event_date='$date',
        event_type='$type'
        WHERE id='$id' AND faculty_id='$faculty_id'
    ");

    header("Location: faculty_calendar.php");
    exit();
}

/* =========================
   MONTH HANDLING
========================= */
$month = isset($_GET['month']) ? intval($_GET['month']) : date("m");
$year  = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

$start_date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
$end_date   = date("Y-m-t", strtotime($start_date));

/* =========================
   FETCH EVENTS
========================= */
$events = [];

$result = $conn->query("
    SELECT *
    FROM faculty_calendar
    WHERE faculty_id='$faculty_id'
    AND event_date BETWEEN '$start_date' AND '$end_date'
");

while ($row = $result->fetch_assoc()) {
    $events[$row['event_date']][] = $row;
}

/* =========================
   EXAM REMINDER
========================= */
$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));

$reminder = $conn->query("
    SELECT * FROM faculty_calendar
    WHERE faculty_id='$faculty_id'
    AND event_type='Exam'
    AND (event_date='$today' OR event_date='$tomorrow')
");

$first_day = date("N", strtotime($start_date));
$total_days = date("t", strtotime($start_date));
?>

<!DOCTYPE html>
<html>
<head>
<title>Faculty Calendar</title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    padding:20px;
}

.container{
    max-width:1200px;
    margin:auto;
}

.card{
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid #ddd;
    width:14%;
    height:120px;
    vertical-align:top;
    padding:5px;
}

th{
    background:#1e293b;
    color:white;
}

.today{ background:#dbeafe; }

.date{
    font-weight:bold;
}

.event{
    padding:3px;
    margin-top:3px;
    border-radius:5px;
    font-size:12px;
    color:white;
}

.Lecture{ background:#2563eb; }
.Meeting{ background:#f59e0b; }
.Exam{ background:#ef4444; }
.Holiday{ background:#22c55e; }
.General{ background:#64748b; }

/* POPUP */
.popup{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.5);
}

.popup-box{
    background:white;
    width:350px;
    padding:20px;
    border-radius:10px;
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
}

input, select{
    width:100%;
    padding:10px;
    margin:8px 0;
}

button{
    padding:8px 12px;
    background:#22c55e;
    color:white;
    border:none;
    cursor:pointer;
}

.btn-danger{
    background:#ef4444;
}

.back{
    background:#475569;
    color:white;
    padding:8px 12px;
    text-decoration:none;
    border-radius:6px;
}
</style>
</head>

<body>

<div class="container">

<div class="card">

<a class="back" href="../../faculty_dashboard.php">⬅ Back</a>

<h2>📅 Faculty Calendar</h2>

<?php if ($reminder && $reminder->num_rows > 0) { ?>
    <div style="background:red;color:white;padding:10px;border-radius:8px;margin:10px 0;">
        🔔 Exam Reminder: You have exams Today or Tomorrow!
    </div>
<?php } ?>

<table>
<tr>
    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th>
</tr>

<tr>
<?php
$cell = 1;

for ($i = 1; $i < $first_day; $i++) {
    echo "<td></td>";
    $cell++;
}

for ($day = 1; $day <= $total_days; $day++) {

    $date = "$year-$month-" . str_pad($day, 2, "0", STR_PAD_LEFT);

    echo "<td onclick=\"openPopup('$date')\">";

    echo "<div class='date'>$day</div>";

    if (isset($events[$date])) {
        foreach ($events[$date] as $ev) {
            echo "<div class='event {$ev['event_type']}'>
                    {$ev['event_title']}
                    <a href='?delete={$ev['id']}' style='float:right;color:white'>❌</a>
                    <a href='#' onclick='editEvent(".json_encode($ev).")' style='float:right;color:white;margin-right:5px'>✏</a>
                  </div>";
        }
    }

    echo "</td>";

    if ($cell % 7 == 0) echo "</tr><tr>";

    $cell++;
}

while (($cell-1) % 7 != 0) {
    echo "<td></td>";
    $cell++;
}
?>
</tr>
</table>

</div>
</div>

<!-- ADD POPUP -->
<div class="popup" id="addPopup">
<div class="popup-box">
<h3>Add Event</h3>

<form method="POST">
<input type="hidden" name="event_date" id="add_date">

<input type="text" name="event_title" placeholder="Title" required>

<select name="event_type">
<option>Lecture</option>
<option>Meeting</option>
<option>Exam</option>
<option>Holiday</option>
<option>General</option>
</select>

<button name="add_event">Save</button>
<button type="button" class="btn-danger" onclick="closePopup()">Close</button>
</form>
</div>
</div>

<!-- EDIT POPUP -->
<div class="popup" id="editPopup">
<div class="popup-box">
<h3>Edit Event</h3>

<form method="POST">
<input type="hidden" name="event_id" id="edit_id">

<input type="text" name="event_title" id="edit_title">

<input type="date" name="event_date" id="edit_date">

<select name="event_type" id="edit_type">
<option>Lecture</option>
<option>Meeting</option>
<option>Exam</option>
<option>Holiday</option>
<option>General</option>
</select>

<button name="update_event">Update</button>
<button type="button" class="btn-danger" onclick="closeEdit()">Close</button>
</form>
</div>
</div>

<script>
function openPopup(date){
    document.getElementById("addPopup").style.display="block";
    document.getElementById("add_date").value=date;
}

function closePopup(){
    document.getElementById("addPopup").style.display="none";
}

function editEvent(ev){
    document.getElementById("editPopup").style.display="block";
    document.getElementById("edit_id").value=ev.id;
    document.getElementById("edit_title").value=ev.event_title;
    document.getElementById("edit_date").value=ev.event_date;
    document.getElementById("edit_type").value=ev.event_type;
}

function closeEdit(){
    document.getElementById("editPopup").style.display="none";
}
</script>

</body>
</html>