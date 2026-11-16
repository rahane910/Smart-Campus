<?php
date_default_timezone_set("Asia/Kolkata");
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "smart_campus";

// Change this to 3306 (or 3307 if you changed your MySQL port earlier)
$port = 3307; 

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>