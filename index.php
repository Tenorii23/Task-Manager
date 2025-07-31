<?php
include 'config.php';

$sql = "SELECT id, username FROM users";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo $row['username'] . " ";
    echo "<a href='edit.php?id=" . $row['id'] . "'>Edit</a> ";
    echo "<a href='delete.php?id=" . $row['id'] . "'>Delete</a><br>";
}
?>
<a href="logout.php">Logout</a>


