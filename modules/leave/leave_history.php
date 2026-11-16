<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login Required");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM leave_requests WHERE user_id='$user_id' ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave History</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            padding: 20px;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>My Leave Requests</h2>

<table>
    <tr>
        <th>From Date</th>
        <th>To Date</th>
        <th>Reason</th>
        <th>Status</th>
    </tr>

    <?php while($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['from_date']; ?></td>
        <td><?php echo $row['to_date']; ?></td>
        <td><?php echo $row['reason']; ?></td>
        <td class="<?php echo strtolower($row['status']); ?>">
            <?php echo $row['status']; ?>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>