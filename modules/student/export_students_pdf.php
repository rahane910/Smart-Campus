<?php
session_start();
include("../../config/db.php");

use Dompdf\Dompdf;
use Dompdf\Options;

require '../../vendor/autoload.php';

/* SECURITY CHECK */
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    die("Access Denied");
}

/* FETCH DATA */
$result = $conn->query("SELECT * FROM students ORDER BY id DESC");

/* BUILD HTML */
$html = '
<!DOCTYPE html>
<html>
<head>
<style>
body{
    font-family: Arial;
    font-size: 11px;
}

h2{
    text-align:center;
    color:#1e293b;
    margin-bottom:5px;
}

p{
    text-align:center;
    margin-bottom:15px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#1e293b;
    color:white;
    padding:6px;
    font-size:10px;
}

td{
    padding:5px;
    border-bottom:1px solid #ddd;
    font-size:9px;
    text-align:center;
}

/* PHOTO */
img{
    width:40px;
    height:40px;
    border-radius:5px;
    object-fit:cover;
}
</style>
</head>
<body>

<h2>🎓 Smart Campus - Student Full Report</h2>
<p>Generated on: '.date("d M Y h:i A").'</p>

<table>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Student ID</th>
    <th>Roll No</th>
    <th>PRN No</th>
    <th>Course</th>
    <th>Sem</th>
    <th>Dept</th>
    <th>Year</th>
    <th>DOB</th>
    <th>Gender</th>
    <th>Mobile</th>
    <th>Parent Mobile</th>
    <th>Address</th>
    <th>Photo</th>
</tr>
';

while ($row = $result->fetch_assoc()) {

    /* PHOTO PATH */
    $photoPath = "../../uploads/students/".$row['photo'];

    $photoHtml = "";
    if (!empty($row['photo']) && file_exists($photoPath)) {
        $photoHtml = '<img src="'.$photoPath.'">';
    } else {
        $photoHtml = 'N/A';
    }

    $html .= '
    <tr>
        <td>'.$row["id"].'</td>
        <td>'.$row["name"].'</td>
        <td>'.$row["email"].'</td>
        <td>'.$row["student_id"].'</td>
        <td>'.$row["roll_no"].'</td>
        <td>'.$row["prn_no"].'</td>
        <td>'.$row["course"].'</td>
        <td>'.$row["semester"].'</td>
        <td>'.$row["department"].'</td>
        <td>'.$row["admission_year"].'</td>
        <td>'.$row["dob"].'</td>
        <td>'.$row["gender"].'</td>
        <td>'.$row["mobile"].'</td>
        <td>'.$row["parent_mobile"].'</td>
        <td>'.$row["address"].'</td>
        <td>'.$photoHtml.'</td>
    </tr>';
}

$html .= '
</table>

</body>
</html>
';

/* DOMPDF SETTINGS */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // IMPORTANT for wide table
$dompdf->render();

/* DOWNLOAD */
$dompdf->stream("full_student_report.pdf", ["Attachment" => true]);
?>