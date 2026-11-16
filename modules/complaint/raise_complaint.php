<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login Required");
}

$message = "";

$student_id = $_SESSION['user_id'];

if (isset($_POST['submit'])) {

    $complaint_title = trim($_POST['complaint_title'] ?? "");
    $complaint_category = trim($_POST['complaint_category'] ?? "");
    $priority = trim($_POST['priority'] ?? "Medium");
    $complaint_text = trim($_POST['complaint_text'] ?? "");

    if ($complaint_title == "" || $complaint_text == "") {
        $message = "❌ Please fill all required fields.";
    } else {

        $full_complaint = "Title: $complaint_title
Category: $complaint_category
Priority: $priority
Details: $complaint_text";

        $stmt = $conn->prepare("
            INSERT INTO complaints (student_id, complaint_text, status)
            VALUES (?, ?, 'Pending')
        ");

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("is", $student_id, $full_complaint);

        if ($stmt->execute()) {
            $message = "✅ Complaint Submitted Successfully!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Raise Complaint</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *{
            box-sizing:border-box;
            font-family:Arial, sans-serif;
        }

        body{
            background:linear-gradient(135deg,#e3f2fd,#f4f6f9);
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            margin:0;
            padding:20px;
        }

        .container{
            width:100%;
            max-width:600px;
            background:white;
            padding:35px;
            border-radius:18px;
            box-shadow:0 8px 20px rgba(0,0,0,0.1);
        }

        h2{
            text-align:center;
            margin-bottom:25px;
            color:#2c3e50;
        }

        label{
            font-weight:bold;
            display:block;
            margin-top:15px;
            margin-bottom:6px;
        }

        input, select, textarea{
            width:100%;
            padding:12px;
            border:1px solid #ccc;
            border-radius:10px;
            font-size:14px;
        }

        textarea{
            height:140px;
            resize:none;
        }

        button{
            width:100%;
            margin-top:25px;
            padding:14px;
            background:#3498db;
            color:white;
            border:none;
            border-radius:10px;
            font-size:16px;
            cursor:pointer;
            font-weight:bold;
        }

        button:hover{
            background:#2980b9;
        }

        .message{
            margin-bottom:15px;
            padding:12px;
            border-radius:10px;
            background:#eafaf1;
            color:#27ae60;
            text-align:center;
            font-weight:bold;
        }

        .error{
            background:#fdecea;
            color:#e74c3c;
        }

        @media(max-width:768px){
            .container{
                padding:25px;
            }
        }
        .back-btn{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 16px;
    background:#6c757d;
    color:white;
    text-decoration:none;
    border-radius:8px;
    font-size:14px;
}

.back-btn:hover{
    background:#5a6268;
}
    </style>
</head>
<body>

<div class="container">
    <a href="/smart_campus/faculty_dashboard.php" class="back-btn">
⬅ Back to Dashboard
</a>
    <h2>📝 Raise Complaint</h2>

    <?php if($message != ""){ ?>
        <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <label>Complaint Title *</label>
        <input type="text" name="complaint_title" placeholder="Enter complaint title" required>

        <label>Complaint Category</label>
        <select name="complaint_category">
            <option value="Academic">Academic</option>
            <option value="Hostel">Hostel</option>
            <option value="Library">Library</option>
            <option value="Transport">Transport</option>
            <option value="Infrastructure">Infrastructure</option>
            <option value="Other">Other</option>
        </select>

        <label>Priority</label>
        <select name="priority">
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select>

        <label>Complaint Details *</label>
        <textarea name="complaint_text" placeholder="Describe your complaint in detail..." required></textarea>

        <button type="submit" name="submit">Submit Complaint</button>
    </form>
</div>

</body>
</html>