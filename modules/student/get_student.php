<?php
include("../../config/db.php");

$id = intval($_GET['id']);

$result = $conn->query("SELECT * FROM students WHERE id=$id");

if($row = $result->fetch_assoc()){
    echo json_encode($row);
} else {
    echo json_encode(["error"=>"Student not found"]);
}
?>