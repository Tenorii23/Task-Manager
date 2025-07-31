<?php
session_start();
require_once 'config.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Read POST data
$data = json_decode(file_get_contents('php://input'), true);
$task_id = isset($data['task_id']) ? (int)$data['task_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Task ID not provided']);
    exit;
}

$conn->begin_transaction();

try {
    // Delete from task_categories
    $stmt = $conn->prepare("DELETE FROM task_categories WHERE task_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();

    // Delete from subtasks
    $stmt = $conn->prepare("DELETE FROM subtasks WHERE task_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();

    // Delete the main task
    $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Task not found or not owned by user']);
    }
    $stmt->close();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Delete Task Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
