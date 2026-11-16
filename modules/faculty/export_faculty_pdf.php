<?php
session_start();
include("../../config/db.php");

require '../../vendor/autoload.php';

use Dompdf\Dompdf;

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

$result = $conn->query("SELECT * FROM faculty ORDER BY id DESC");

/* Base path for images */
$basePath = "http://localhost/smart_campus/uploads/";

$html = '
<h2 style="text-align:center;">Faculty Report</h2>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
<tr style="background:#1e293b;color:white;">
    <th>ID</th>
    <th>Photo</th>
    <th>Name</th>
    <th>Email</th>
    <th>Faculty ID</th>
    <th>Department</th>
    <th>Qualification</th>
    <th>Mobile</th>
    <th>Designation</th>
</tr>
';

while ($row = $result->fetch_assoc()) {

    // PHOTO PATH
    $photo = (!empty($row['photo']))
        ? $basePath . $row['photo']
        : "https://via.placeholder.com/60";

    $html .= "
    <tr>
        <td>{$row['id']}</td>

        <td>
            <img src='{$photo}' width='60' height='60'
                 style='border-radius:6px;object-fit:cover;'>
        </td>

        <td>{$row['name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['faculty_id']}</td>
        <td>{$row['department']}</td>
        <td>{$row['qualification']}</td>
        <td>{$row['mobile']}</td>
        <td>{$row['designation']}</td>
    </tr>
    ";
}

$html .= "</table>";

$dompdf = new Dompdf();

/* IMPORTANT: enable remote images */
$options = $dompdf->getOptions();
$options->set(['isRemoteEnabled' => true]);
$dompdf->setOptions($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("faculty_report.pdf", ["Attachment" => true]);
?>