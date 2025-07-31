<?php
include 'config.php';
$id = $_POST['id'];

$sql = "DELETE FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
?>
