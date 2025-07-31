<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginForm.php");
    exit;
}

// Display messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}

// Database connection
require_once 'config.php';

$user_id = $_SESSION['user_id'];

// Get dashboard statistics
$dashboard_query = "
    SELECT 
        (SELECT COUNT(*) FROM tasks WHERE user_id = ?) AS total_tasks,
        (SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed') AS completed_tasks,
        (SELECT COUNT(*) FROM tasks WHERE user_id = ? AND due_date < NOW() AND status != 'completed') AS overdue_tasks,
        (SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'in_progress') AS in_progress_tasks,
        (SELECT COUNT(*) FROM subtasks s 
         JOIN tasks t ON s.task_id = t.task_id 
         WHERE t.user_id = ? AND s.is_completed = FALSE) AS pending_subtasks
";


$stmt = $conn->prepare($dashboard_query);
$stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$dashboard_data = $result->fetch_assoc();



if (!isset($_SESSION['user_id'])) {
    header("Location: loginForm.php");
    exit;
}

// Handle session messages
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];

// Get dashboard stats
$dashboard_data = [
    'total_tasks' => 0,
    'completed_tasks' => 0,
    'in_progress_tasks' => 0,
    'overdue_tasks' => 0,
    'pending_subtasks' => 0,
];
if ($user_id) {
    // Count main tasks
    $query = $conn->query("SELECT status, due_date FROM tasks WHERE user_id = $user_id");

    while ($task = $query->fetch_assoc()) {
        $dashboard_data['total_tasks']++;

        if ($task['status'] === 'completed') {
            $dashboard_data['completed_tasks']++;
        } elseif ($task['status'] === 'in-progress') {
            $dashboard_data['in_progress_tasks']++;
        }

        if (!empty($task['due_date']) && $task['status'] !== 'completed') {
            $due_date = strtotime($task['due_date']);
            $today = strtotime(date('Y-m-d'));

            if ($due_date < $today) {
                $dashboard_data['overdue_tasks']++;
            }
        }
    }
 }
// Get upcoming tasks
$upcoming_query = "
    SELECT task_id, title, due_date 
    FROM tasks 
    WHERE user_id = ? 
    AND due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
    AND status != 'completed'
    ORDER BY due_date ASC
    LIMIT 5
";

$stmt = $conn->prepare($upcoming_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --success: #00b894;
            --danger: #ff7675;
            --warning: #fdcb6e;
            --info: #74b9ff;
            --dark: #121826;
            --darker: #0d1117;
            --light: #f8f9fa;
            --card-bg: rgba(30, 33, 47, 0.7);
            --glass: rgba(255, 255, 255, 0.08);
            --text: #e0e6ed;
            --text-secondary: #a6adbb;
        }
        
        body {
            background: linear-gradient(135deg, var(--darker), var(--dark));
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }
        
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header Styles */
        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--glass);
        }
        
        .app-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .app-title h1 {
            font-weight: 700;
            font-size: 2.2rem;
            margin: 0;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .app-title i {
            font-size: 2.5rem;
            color: var(--primary);
            background: rgba(108, 92, 231, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .welcome-message {
            font-size: 1.3rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 5px 0;
        }
        
        .stat-card .label {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        
        /* Tasks Section */
        .tasks-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass);
            margin-top: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .btn-group {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            background: #5d4de0;
        }
        
        .btn-success:hover {
            background: #00a07b;
        }
        
        /* Task Grid */
        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        
        @media (max-width: 768px) {
            .task-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Task Card */
        .task-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--glass);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
            border-color: rgba(108, 92, 231, 0.3);
        }
        
        .task-header {
            margin-bottom: 20px;
        }
        
        .task-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text);
        }
        
        .task-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--text);
        }
        
        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 1.1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .task-card {
            animation: fadeIn 0.4s ease forwards;
        }
        
        /* Utility Classes */
        .mb-20 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="app-title">
                <i class="fas fa-tasks"></i>
                <h1>Task Manager</h1>
                <div class="welcome-message">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                </div>
            </div>
            <div>
                <a href="logout.php" class="btn btn-outline" style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass); color: var(--text);">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </header>
        
        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-tasks" style="background: rgba(108, 92, 231, 0.1); color: var(--primary);"></i>
                <div class="number"><?php echo $dashboard_data['total_tasks']; ?></div>
                <div class="label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle" style="background: rgba(0, 184, 148, 0.1); color: var(--success);"></i>
                <div class="number"><?php echo $dashboard_data['completed_tasks']; ?></div>
                <div class="label">Completed</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-spinner" style="background: rgba(116, 185, 255, 0.1); color: var(--info);"></i>
                <div class="number"><?php echo $dashboard_data['in_progress_tasks']; ?></div>
                <div class="label">In Progress</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-circle" style="background: rgba(255, 118, 117, 0.1); color: var(--danger);"></i>
                <div class="number"><?php echo $dashboard_data['overdue_tasks']; ?></div>
                <div class="label">Overdue</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-list-check" style="background: rgba(253, 203, 110, 0.1); color: var(--warning);"></i>
                <div class="number"><?php echo $dashboard_data['pending_subtasks']; ?></div>
                <div class="label">Pending Subtasks</div>
            </div>
        </div>
        
        <!-- Upcoming Tasks Section -->
        <div class="tasks-section">
            <div class="section-header">
                <h2 class="section-title">Upcoming Deadlines</h2>
                <div class="btn-group">
                    <a href="create_task.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Task
                    </a>
                    <a href="view_tasks.php" class="btn btn-success">
                        <i class="fas fa-eye"></i> View Tasks
                    </a>
                </div>
            </div>
            
            <div class="task-grid">
                <?php if ($upcoming_tasks->num_rows > 0): ?>
                    <?php while ($task = $upcoming_tasks->fetch_assoc()): 
                        $days_until_due = ceil((strtotime($task['due_date']) - time()) / (60 * 60 * 24));
                        $priority = $days_until_due <= 1 ? 'high' : ($days_until_due <= 3 ? 'medium' : 'low');
                    ?>
                    <div class="task-card">
                        <div class="task-header">
                            <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                        </div>
                        
                        <div class="task-meta">
                            <div class="meta-item">
                                <span class="meta-label">Due Date</span>
                                <span class="meta-value"><?php echo date('M j, Y', strtotime($task['due_date'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Priority</span>
                                <span class="meta-value">
                                    <span class="priority-badge priority-<?php echo $priority; ?>">
                                        <?php echo ucfirst($priority); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Days Left</span>
                                <span class="meta-value"><?php echo $days_until_due; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>No Upcoming Deadlines</h3>
                        <p>You don't have any upcoming deadlines in the next week. Great job!</p>
                        <a href="create_task.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Task
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add badge styles
        const style = document.createElement('style');
        style.innerHTML = `
            .priority-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
            }
            
            .priority-high {
                background: rgba(255, 118, 117, 0.15);
                color: var(--danger);
            }
            
            .priority-medium {
                background: rgba(253, 203, 110, 0.15);
                color: var(--warning);
            }
            
            .priority-low {
                background: rgba(0, 184, 148, 0.15);
                color: var(--success);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>