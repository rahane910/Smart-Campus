<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login Required");
}

$user_id = $_SESSION['user_id'];

/* Acknowledge Notice */
/* Acknowledge Notice */
if (isset($_GET['ack'])) {
    $notice_id = intval($_GET['ack']);

    // First check if already acknowledged
    $check = $conn->query("
        SELECT id
        FROM notice_acknowledgements
        WHERE notice_id = '$notice_id'
        AND student_id = '$user_id'
    ");

    if (!$check) {
        die("Check Query Error: " . $conn->error);
    }

    // Insert only if not already acknowledged
    if ($check->num_rows == 0) {
        $insert = $conn->query("
            INSERT INTO notice_acknowledgements (notice_id, student_id)
            VALUES ('$notice_id', '$user_id')
        ");

        if (!$insert) {
            die("Insert Error: " . $conn->error);
        }
    }

    // Prevent duplicate insert on page refresh
    header("Location: view_notice.php");
    exit();
}

/* Fetch Notices */
$result = $conn->query("
    SELECT n.*,
           CASE 
               WHEN na.notice_id IS NOT NULL THEN 1
               ELSE 0
           END AS is_acknowledged
    FROM notices n
    LEFT JOIN notice_acknowledgements na
        ON n.id = na.notice_id
        AND na.student_id = '$user_id'
    ORDER BY n.created_at DESC
");

if (!$result) {
    die("SQL Error: " . $conn->error);
}

/* Store notices in array */
$notices = [];
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Notices</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
        }

        body{
            background:#f4f6f9;
            padding:20px;
        }

        .container{
            display:flex;
            gap:20px;
            min-height:90vh;
        }

        .notice-list{
            width:30%;
            background:white;
            border-radius:15px;
            padding:20px;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
            overflow-y:auto;
            max-height:90vh;
        }

        .notice-item{
            padding:15px;
            margin-bottom:12px;
            border-radius:10px;
            background:#f8f9fa;
            cursor:pointer;
            transition:0.3s;
            border-left:4px solid transparent;
        }

        .notice-item:hover,
        .notice-item.active{
            background:#eaf4ff;
            border-left:4px solid #3498db;
        }

        .notice-item h4{
            margin-bottom:5px;
            color:#2c3e50;
        }

        #noticeSearch{
            width:100%;
            padding:10px;
            margin:15px 0;
            border:1px solid #ccc;
            border-radius:8px;
            font-size:14px;
        }

        .notice-item small{
            color:gray;
        }

        .new-badge{
            background:#2ecc71;
            color:white;
            padding:3px 8px;
            border-radius:12px;
            font-size:11px;
            margin-left:8px;
        }

        .notice-view{
            flex:1;
            background:white;
            border-radius:15px;
            padding:25px;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
        }

        .notice-view h2{
            margin-bottom:15px;
            color:#2c3e50;
        }

        .notice-view p{
            line-height:1.6;
            margin-bottom:15px;
        }

        .notice-image{
            width:100%;
            max-width:600px;
            border-radius:10px;
            margin-top:15px;
        }

        iframe{
            width:100%;
            height:500px;
            border:none;
            border-radius:10px;
            margin-top:15px;
        }

        .download-btn{
            display:inline-block;
            margin-top:15px;
            padding:10px 15px;
            background:#3498db;
            color:white;
            text-decoration:none;
            border-radius:8px;
        }

        @media(max-width:768px){
            .container{
                flex-direction:column;
            }

            .notice-list{
                width:100%;
                max-height:300px;
            }
        }
        .ack-btn{
    display:inline-block;
    margin-top:15px;
    padding:10px 18px;
    background:#2ecc71;
    color:white;
    text-decoration:none;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:14px;
}

.ack-btn:hover{
    background:#27ae60;
}
    </style>
</head>
<body>

<div class="container">

    <!-- Notice List -->
    <div class="notice-list">
        <h3>📢 Notices</h3>

        <input type="text" id="noticeSearch" placeholder="🔍 Search notices..." onkeyup="filterNotices()">

        <?php foreach($notices as $index => $row){ 
            $isNew = (strtotime($row['created_at']) >= strtotime('-3 days'));
        ?>
            <div class="notice-item <?php echo $index==0 ? 'active' : ''; ?>"
                 data-title="<?php echo strtolower(htmlspecialchars($row['title'])); ?>"
                 data-date="<?php echo strtolower($row['created_at']); ?>"
                 onclick="showNotice(<?php echo $index; ?>, this)">
                 
                <h4>
                    <?php echo htmlspecialchars($row['title']); ?>
                    <?php if($isNew){ ?>
                        <span class="new-badge">NEW</span>
                    <?php } ?>
                </h4>

                <small><?php echo $row['created_at']; ?></small>
            </div>
        <?php } ?>
    </div>

    <!-- Notice View -->
    <div class="notice-view" id="noticeView">
        <!-- Notice content loads here -->
    </div>

</div>

<script>
    const notices = <?php echo json_encode($notices); ?>;

    function filterNotices(){
        let search = document.getElementById("noticeSearch").value.toLowerCase();
        let items = document.querySelectorAll(".notice-item");

        items.forEach(item => {
            let title = item.getAttribute("data-title");
            let date = item.getAttribute("data-date");

            if(title.includes(search) || date.includes(search)){
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }
        });
    }

    function showNotice(index, element){
        const notice = notices[index];

        let fileHtml = "";
        let downloadBtn = "";

        if(notice.notice_file){
            let file = "../../uploads/notices/" + notice.notice_file;
            let ext = notice.notice_file.split('.').pop().toLowerCase();

            if(["jpg","jpeg","png"].includes(ext)){
                fileHtml = `<img src="${file}" class="notice-image">`;
            }
            else if(ext === "pdf"){
                fileHtml = `<iframe src="${file}#toolbar=0&navpanes=0"></iframe>`;
            }

            downloadBtn = `
                <a href="${file}" download class="download-btn">
                    ⬇ Download Notice
                </a>
            `;
        }

        document.getElementById("noticeView").innerHTML = `
    <h2>${notice.title}</h2>
    <p>${notice.content.replace(/\n/g, "<br>")}</p>
    <small>📅 ${notice.created_at}</small>
    ${fileHtml}
    ${downloadBtn}

    ${
        notice.is_acknowledged == 1
        ? `<button class="ack-btn" disabled>✅ Acknowledged</button>`
        : `<a href="?ack=${notice.id}" class="ack-btn">✔ Mark as Read</a>`
    }
`;

        document.querySelectorAll(".notice-item").forEach(item => {
            item.classList.remove("active");
        });

        element.classList.add("active");
    }

    window.onload = function(){
        if(notices.length > 0){
            showNotice(0, document.querySelector(".notice-item"));
        }
    }
</script>

</body>
</html>