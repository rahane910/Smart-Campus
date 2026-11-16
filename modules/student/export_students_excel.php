<?php
include("../../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students.xls");

echo "ID\tName\tEmail\tRoll No\tCourse\n";

$result = $conn->query("SELECT * FROM students");

while($row = $result->fetch_assoc()){
    echo "{$row['id']}\t{$row['name']}\t{$row['email']}\t{$row['roll_no']}\t{$row['course']}\n";
}
?>