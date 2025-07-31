<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginForm.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "projektpt");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get form data
$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$description = $_POST['description'] ?? null;
$due_date = $_POST['due_date'] ?? null;
$priority = $_POST['priority'] ?? 'medium';

// Insert task into database
$sql = "INSERT INTO tasks (user_id, title, description, due_date, priority, status) 
        VALUES (?, ?, ?, ?, ?, 'todo')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $user_id, $title, $description, $due_date, $priority);

if ($stmt->execute()) {
    $_SESSION['message'] = "Task created successfully!";
} else {
    $_SESSION['error'] = "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
header("Location: dashboard.php"); // Redirect back to dashboard
exit;
?>