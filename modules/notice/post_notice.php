<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

$message = "";
$type = "";

/* Create upload folder if not exists */
$upload_dir = "../../uploads/notices/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* FORM SUBMIT */
if (isset($_POST['submit'])) {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $posted_by = $_SESSION['user_id'];

    $file_names = [];

    /* FILE UPLOAD */
    if (isset($_FILES['notice_files']) && !empty($_FILES['notice_files']['name'][0])) {

        foreach ($_FILES['notice_files']['name'] as $key => $file_name) {

            if ($_FILES['notice_files']['error'][$key] == 0) {

                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $allowed = [
                    'jpg', 'jpeg', 'png', 'webp',
                    'pdf', 'doc', 'docx',
                    'ppt', 'pptx', 'zip', 'txt'
                ];

                if (in_array($ext, $allowed)) {

                    $new_name = "NOTICE_" . time() . "_" . rand(1000, 9999) . "." . $ext;

                    $target_path = $upload_dir . $new_name;

                    if (move_uploaded_file($_FILES['notice_files']['tmp_name'][$key], $target_path)) {
                        $file_names[] = $new_name;
                    }
                }
            }
        }
    }

    /* Validation:
       Either typed notice OR uploaded file required
    */
    if (empty($title) && empty($content) && empty($file_names)) {
        $message = "Please type notice or upload file.";
        $type = "error";
    } else {

        $file_string = implode(",", $file_names);

        $stmt = $conn->prepare("
            INSERT INTO notices (title, content, posted_by, notice_file)
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("ssis", $title, $content, $posted_by, $file_string);

        if ($stmt->execute()) {
            $message = "Notice published successfully!";
            $type = "success";
        } else {
            $message = "Failed to publish notice!";
            $type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Notice</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI', sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:30px;
            background:linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
        }

        .container{
            width:100%;
            max-width:1100px;
            background:rgba(255,255,255,0.1);
            backdrop-filter:blur(18px);
            border-radius:24px;
            padding:35px;
            color:white;
            box-shadow:0 20px 50px rgba(0,0,0,0.4);
        }

        h2{
            text-align:center;
            margin-bottom:8px;
        }

        .subtitle{
            text-align:center;
            margin-bottom:25px;
            opacity:0.8;
        }

        .message{
            padding:12px;
            border-radius:10px;
            margin-bottom:20px;
            text-align:center;
            font-weight:600;
        }

        .success{
            background:rgba(34,197,94,0.2);
            border:1px solid #22c55e;
        }

        .error{
            background:rgba(239,68,68,0.2);
            border:1px solid #ef4444;
        }

        .grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        }

        .box{
            background:rgba(255,255,255,0.08);
            padding:20px;
            border-radius:16px;
        }

        label{
            display:block;
            margin-bottom:8px;
            font-weight:600;
        }

        input, textarea{
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            margin-bottom:15px;
            outline:none;
        }

        textarea{
            height:220px;
            resize:none;
        }

        input[type="file"]{
            background:white;
            color:black;
        }

        button{
            width:100%;
            margin-top:20px;
            padding:14px;
            border:none;
            border-radius:12px;
            background:linear-gradient(135deg,#3b82f6,#1d4ed8);
            color:white;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
        }

        button:hover{
            opacity:0.9;
        }

        .back{
            display:block;
            text-align:center;
            margin-top:18px;
            color:white;
            text-decoration:none;
        }

        @media(max-width:900px){
            .grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <h2>📢 Professional Notice Board</h2>
    <p class="subtitle">Type notice manually OR upload file directly</p>

    <?php if ($message != "") { ?>
        <div class="message <?php echo $type; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="grid">

            <!-- MANUAL NOTICE -->
            <div class="box">
                <h3>📝 Manual Notice (Optional)</h3>
                <br>

                <label>Title</label>
                <input type="text" name="title" placeholder="Enter notice title">

                <label>Content</label>
                <textarea name="content" placeholder="Type notice content..."></textarea>
            </div>

            <!-- FILE NOTICE -->
            <div class="box">
                <h3>📎 Upload File Notice (Optional)</h3>
                <br>

                <label>Select Files</label>
                <input type="file" name="notice_files[]" multiple
                       accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.ppt,.pptx,.zip,.txt">

                <p style="font-size:13px; opacity:0.8;">
                    Supported: PDF, Images, Word, PPT, ZIP, TXT
                </p>
            </div>

        </div>

        <button type="submit" name="submit">🚀 Publish Notice</button>

    </form>

    <a href="../../admin_dashboard.php" class="back">⬅ Back to Dashboard</a>

</div>

</body>
</html>