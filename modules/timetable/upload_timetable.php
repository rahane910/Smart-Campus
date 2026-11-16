<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    die("Access Denied");
}

/* FILTER VALUES */
$selected_class = $_GET['class_name'] ?? '';
$selected_course = $_GET['course'] ?? '';

/* FETCH COURSES */
$course_result = $conn->query("
    SELECT DISTINCT course
    FROM students
    WHERE course IS NOT NULL
    AND course != ''
    ORDER BY course ASC
");

if (!$course_result) {
    die("Course Fetch Error: " . $conn->error);
}

/* ADD NEW ROW */
if (isset($_GET['add'])) {
    $class_name = $conn->real_escape_string($_GET['class_name'] ?? '');
    $course = $conn->real_escape_string($_GET['course'] ?? '');

    $conn->query("
        INSERT INTO timetable
        (
            time,
            class_name,
            course,
            monday,
            tuesday,
            wednesday,
            thursday,
            friday,
            saturday
        )
        VALUES
        (
            '09:00 - 10:00',
            '$class_name',
            '$course',
            '',
            '',
            '',
            '',
            '',
            ''
        )
    ");

    header("Location: upload_timetable.php?class_name=$class_name&course=$course");
    exit();
}

/* ADD LUNCH BREAK */
if (isset($_GET['lunch'])) {
    $class_name = $conn->real_escape_string($_GET['class_name'] ?? '');
    $course = $conn->real_escape_string($_GET['course'] ?? '');

    $conn->query("
        INSERT INTO timetable
        (
            time,
            class_name,
            course,
            monday,
            tuesday,
            wednesday,
            thursday,
            friday,
            saturday
        )
        VALUES
        (
            '01:00 - 02:00',
            '$class_name',
            '$course',
            'LUNCH BREAK',
            'LUNCH BREAK',
            'LUNCH BREAK',
            'LUNCH BREAK',
            'LUNCH BREAK',
            'LUNCH BREAK'
        )
    ");

    header("Location: upload_timetable.php?class_name=$class_name&course=$course");
    exit();
}

/* DELETE ROW */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $conn->query("DELETE FROM timetable WHERE id='$id'");

    header("Location: upload_timetable.php?class_name=$selected_class&course=$selected_course");
    exit();
}

/* RESET */
if (isset($_GET['reset'])) {
    $class_name = $conn->real_escape_string($selected_class);
    $course = $conn->real_escape_string($selected_course);

    $conn->query("
        UPDATE timetable
        SET
            monday='',
            tuesday='',
            wednesday='',
            thursday='',
            friday='',
            saturday=''
        WHERE class_name='$class_name'
        AND course='$course'
    ");

    header("Location: upload_timetable.php?class_name=$class_name&course=$course");
    exit();
}

/* SAVE TABLE */
if (isset($_POST['save_table'])) {

    $class_name = $conn->real_escape_string($_POST['class_name']);
    $course = $conn->real_escape_string($_POST['course']);

    foreach ($_POST['rows'] as $id => $row) {

        $time = $conn->real_escape_string($row['time']);
        $monday = $conn->real_escape_string($row['monday']);
        $tuesday = $conn->real_escape_string($row['tuesday']);
        $wednesday = $conn->real_escape_string($row['wednesday']);
        $thursday = $conn->real_escape_string($row['thursday']);
        $friday = $conn->real_escape_string($row['friday']);
        $saturday = $conn->real_escape_string($row['saturday']);

        $conn->query("
            UPDATE timetable
            SET
                time='$time',
                class_name='$class_name',
                course='$course',
                monday='$monday',
                tuesday='$tuesday',
                wednesday='$wednesday',
                thursday='$thursday',
                friday='$friday',
                saturday='$saturday'
            WHERE id='$id'
        ");
    }

    header("Location: upload_timetable.php?class_name=$class_name&course=$course");
    exit();
}

/* FETCH TIMETABLE */
if (!empty($selected_class) && !empty($selected_course)) {

    $class_name = $conn->real_escape_string($selected_class);
    $course = $conn->real_escape_string($selected_course);

    $result = $conn->query("
        SELECT *
        FROM timetable
        WHERE class_name='$class_name'
        AND course='$course'
        ORDER BY id ASC
    ");
} else {
    $result = $conn->query("
        SELECT *
        FROM timetable
        ORDER BY id ASC
    ");
}

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Wise Timetable</title>
    <style>
        body{
            font-family:Arial;
            background:#f4f6f9;
            padding:20px;
        }

        .container{
            max-width:1500px;
            margin:auto;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:18px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
        }

        .meta-grid{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:10px;
            margin-bottom:20px;
        }

        .toolbar{
            display:flex;
            gap:10px;
            margin-bottom:20px;
            flex-wrap:wrap;
        }

        .btn{
            padding:10px 16px;
            border:none;
            border-radius:8px;
            text-decoration:none;
            color:white;
            font-weight:bold;
            cursor:pointer;
        }

        .add-btn{background:#22c55e;}
        .lunch-btn{background:#f59e0b;}
        .save-btn{background:#2563eb;}
        .delete-btn{background:#ef4444;}
        .print-btn{background:#6b7280;}
        .reset-btn{background:#f97316;}

        table{
            width:100%;
            border-collapse:collapse;
        }

        th, td{
            border:1px solid #ddd;
            padding:10px;
        }

        th{
            background:#1e293b;
            color:white;
        }

        input, select{
            width:100%;
            padding:10px;
        }
        .back-btn{
    background:#475569;
}
    </style>
</head>
<body>

<div class="container">
<div class="card">
<h2>🗓 Course Wise & Class Wise Timetable</h2>

<form method="GET">
    <div class="meta-grid">

        <select name="class_name" required>
            <option value="">Select Class</option>
            <option value="FY" <?= $selected_class=="FY" ? "selected" : "" ?>>FY</option>
            <option value="SY" <?= $selected_class=="SY" ? "selected" : "" ?>>SY</option>
            <option value="TY" <?= $selected_class=="TY" ? "selected" : "" ?>>TY</option>
        </select>

        <select name="course" required>
            <option value="">Select Course</option>
            <?php while($course = $course_result->fetch_assoc()) { ?>
                <option value="<?= $course['course']; ?>"
                    <?= $selected_course == $course['course'] ? "selected" : "" ?>>
                    <?= $course['course']; ?>
                </option>
            <?php } ?>
        </select>

    </div>

    <button type="submit" class="btn save-btn">🔍 Load Timetable</button>
</form>

<br>

<div class="toolbar">
    <a href="../../faculty_dashboard.php" class="btn back-btn">⬅ Back to Dashboard</a>

    <a href="?add=1&class_name=<?= $selected_class ?>&course=<?= $selected_course ?>" class="btn add-btn">➕ Add Row</a>

    <a href="?lunch=1&class_name=<?= $selected_class ?>&course=<?= $selected_course ?>" class="btn lunch-btn">🍱 Lunch</a>

    <a href="?reset=1&class_name=<?= $selected_class ?>&course=<?= $selected_course ?>" class="btn reset-btn">🔄 Reset</a>

    <button onclick="window.print()" class="btn print-btn" type="button">🖨 Print</button>
</div>

<form method="POST">
    <input type="hidden" name="class_name" value="<?= $selected_class ?>">
    <input type="hidden" name="course" value="<?= $selected_course ?>">

    <table>
        <tr>
            <th>Time</th>
            <th>Monday</th>
            <th>Tuesday</th>
            <th>Wednesday</th>
            <th>Thursday</th>
            <th>Friday</th>
            <th>Saturday</th>
            <th>Action</th>
        </tr>

        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><input type="text" name="rows[<?= $row['id'] ?>][time]" value="<?= $row['time'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][monday]" value="<?= $row['monday'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][tuesday]" value="<?= $row['tuesday'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][wednesday]" value="<?= $row['wednesday'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][thursday]" value="<?= $row['thursday'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][friday]" value="<?= $row['friday'] ?>"></td>
            <td><input type="text" name="rows[<?= $row['id'] ?>][saturday]" value="<?= $row['saturday'] ?>"></td>
            <td>
                <a href="?delete=<?= $row['id'] ?>&class_name=<?= $selected_class ?>&course=<?= $selected_course ?>" class="btn delete-btn">🗑</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <br>
    <button type="submit" name="save_table" class="btn save-btn">💾 Save</button>
</form>

</div>
</div>

</body>
</html>