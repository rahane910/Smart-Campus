<?php
include("../../config/db.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=faculty.xls");

$result = $conn->query("SELECT * FROM faculty");

echo "ID\tName\tEmail\tDepartment\tMobile\tDesignation\n";

while($row = $result->fetch_assoc()){
    echo "{$row['id']}\t{$row['name']}\t{$row['email']}\t{$row['department']}\t{$row['mobile']}\t{$row['designation']}\n";
}
?>