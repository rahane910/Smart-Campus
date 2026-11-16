<?php
include("../../config/db.php");

$id = $_POST['id'];

$stmt = $conn->prepare("
UPDATE students SET
name=?, email=?, student_id=?, roll_no=?, prn_no=?,
course=?, semester=?, department=?, admission_year=?,
dob=?, gender=?, mobile=?, parent_mobile=?, address=?
WHERE id=?
");

$stmt->bind_param(
"ssssssssssssssi",
$_POST['name'],
$_POST['email'],
$_POST['student_id'],
$_POST['roll_no'],
$_POST['prn_no'],
$_POST['course'],
$_POST['semester'],
$_POST['department'],
$_POST['admission_year'],
$_POST['dob'],
$_POST['gender'],
$_POST['mobile'],
$_POST['parent_mobile'],
$_POST['address'],
$id
);

echo $stmt->execute() ? "Updated Successfully" : "Update Failed";
?>