<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $task_id = $data['task_id'];

    $update_query = "UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE task_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
