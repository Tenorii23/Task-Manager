<?php
include 'config.php';
$username = 'admin';
$password = password_hash('password', PASSWORD_DEFAULT);

$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

echo "Admin user created with hash: $password <br>";
echo "<a href='loginForm.php'>Go to login</a>";
?>
