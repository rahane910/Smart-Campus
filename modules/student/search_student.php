<?php
include("../../config/db.php");

$search = $_GET['query'] ?? '';

$stmt = $conn->prepare("
    SELECT * FROM students
    WHERE name LIKE ? OR email LIKE ? OR roll_no LIKE ?
    LIMIT 20
");

$like = "%$search%";
$stmt->bind_param("sss", $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= $row['roll_no'] ?></td>
    <td><?= $row['course'] ?></td>
</tr>
<?php } ?>