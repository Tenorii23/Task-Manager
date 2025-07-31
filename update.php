<?php
include 'config.php';

$id = $_POST['id'];
$username = $_POST['username'];

$sql = "UPDATE users SET username=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $username, $id);
$stmt->execute();

header("Location: dashboard.php");
?>
