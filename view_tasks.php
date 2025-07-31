<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginForm.php");
    exit;
}
// After session_start()
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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

$tasks_query = "
    SELECT t.task_id, t.title, t.description, t.due_date, t.priority, t.status, 
           t.created_at, t.completed_at, 
           GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
    FROM tasks t
    LEFT JOIN task_categories tc ON t.task_id = tc.task_id
    LEFT JOIN categories c ON tc.category_id = c.category_id
    WHERE t.user_id = ? 
    AND t.status != 'completed' 
    AND t.status != 'archived'
    GROUP BY t.task_id
    ORDER BY 
        CASE 
            WHEN t.due_date IS NULL THEN 1
            ELSE 0
        END,
        t.due_date ASC,
        CASE t.priority
            WHEN 'high' THEN 1
            WHEN 'medium' THEN 2
            WHEN 'low' THEN 3
            ELSE 4
        END
";

$stmt = $conn->prepare($tasks_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - MYT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        
        /* Main Layout */
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 25px;
        }
        
        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Sidebar */
        .sidebar {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass);
            height: fit-content;
        }
        
        .sidebar-section {
            margin-bottom: 30px;
        }
        
        .sidebar-section:last-child {
            margin-bottom: 0;
        }
        
        .sidebar-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .sidebar-title i {
            color: var(--primary);
        }
        
        /* Filter Styles */
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }
        
        .filter-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass);
            border-radius: 12px;
            color: var(--text);
            padding: 12px 15px;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        
        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .filter-tag {
            background: rgba(108, 92, 231, 0.15);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-tag .remove {
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .filter-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn-filter {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--glass);
            color: var(--text);
        }
        
        .btn-primary:hover {
            background: #5d4de0;
            transform: translateY(-2px);
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }
        
        /* Task Grid */
        .tasks-section {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass);
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
        
        .create-task-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .create-task-btn:hover {
            background: #5d4de0;
            transform: translateY(-2px);
        }
        
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
        
        .task-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .task-card.high::before {
            background: var(--danger);
        }
        
        .task-card.medium::before {
            background: var(--warning);
        }
        
        .task-card.low::before {
            background: var(--success);
        }
        
        .task-card .task-id {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary);
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
        
        .task-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
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
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-todo {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
        }
        
        .status-in_progress {
            background: rgba(116, 185, 255, 0.15);
            color: var(--info);
        }
        
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
        
        .task-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 20px;
        }
        
        .category-tag {
            background: rgba(108, 92, 231, 0.15);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .task-actions {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
        }
        
        .delete-btn {
            background: rgba(255, 118, 117, 0.15);
            border: 1px solid rgba(255, 118, 117, 0.3);
            color: var(--danger);
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .delete-btn:hover {
            background: rgba(255, 118, 117, 0.25);
            transform: translateY(-2px);
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
        
        /* Badge for overdue tasks */
        .overdue-badge {
            position: absolute;
            top: 20px;
            left: -30px;
            background: var(--danger);
            color: white;
            padding: 4px 30px;
            font-size: 0.8rem;
            font-weight: 500;
            transform: rotate(-45deg);
        }
        
        /* Confirmation Modal */
        .modal-content {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass);
            color: var(--text);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--glass);
        }
        
        .modal-footer {
            border-top: 1px solid var(--glass);
        }
        
        .btn-confirm-delete {
            background: var(--danger);
            border: none;
        }
        
        .btn-confirm-delete:hover {
            background: #e25d5d;
        }
        
        /* Utility Classes */
        .mb-20 {
            margin-bottom: 20px;
        }
.complete-btn {
    background: rgba(0, 184, 148, 0.15);
    border: 1px solid rgba(0, 184, 148, 0.3);
    color: var(--success);
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-right: 10px;
}

.complete-btn:hover {
    background: rgba(0, 184, 148, 0.25);
    transform: translateY(-2px);
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
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </header>
        
        <!-- Stats Section -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-tasks" style="background: rgba(108, 92, 231, 0.1); color: var(--primary);"></i>
                <div class="number"><?php echo $tasks->num_rows; ?></div>
                <div class="label">Active Tasks</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-circle" style="background: rgba(255, 118, 117, 0.1); color: var(--danger);"></i>
                <div class="number"><?php 
                    $high_priority = 0;
                    if ($tasks->num_rows > 0) {
                        $tasks->data_seek(0);
                        while ($task = $tasks->fetch_assoc()) {
                            if ($task['priority'] == 'high') $high_priority++;
                        }
                    }
                    echo $high_priority;
                ?></div>
                <div class="label">High Priority</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day" style="background: rgba(253, 203, 110, 0.1); color: var(--warning);"></i>
                <div class="number"><?php 
                    $today = date('Y-m-d');
                    $due_today = 0;
                    if ($tasks->num_rows > 0) {
                        $tasks->data_seek(0);
                        while ($task = $tasks->fetch_assoc()) {
                            if ($task['due_date'] == $today) $due_today++;
                        }
                    }
                    echo $due_today;
                ?></div>
                <div class="label">Due Today</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-tags" style="background: rgba(116, 185, 255, 0.1); color: var(--info);"></i>
                <div class="number">5</div>
                <div class="label">Categories</div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-filter"></i> Filter Tasks</h3>
                    
                    <div class="filter-group">
                        <label for="priorityFilter">Priority</label>
                        <select class="filter-select" id="priorityFilter">
                            <option value="all">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="statusFilter">Status</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Statuses</option>
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="dueDateFilter">Due Date</label>
                        <select class="filter-select" id="dueDateFilter">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                    
                    <div class="filter-tags" id="filterTags">
                        <!-- Filter tags will appear here -->
                    </div>
                    
                    <div class="filter-actions">
                        <button class="btn-filter btn-primary" id="applyFilters">
                            <i class="fas fa-check"></i> Apply
                        </button>
                        <button class="btn-filter btn-outline" id="resetFilters">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-layer-group"></i> Categories</h3>
                    <div class="categories-list">
                        <div class="category-item mb-20">
                            <div class="d-flex justify-content-between">
                                <span>Development</span>
                                <span class="text-muted">3</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar" role="progressbar" style="width: 75%; background: var(--primary);"></div>
                            </div>
                        </div>
                        <div class="category-item mb-20">
                            <div class="d-flex justify-content-between">
                                <span>Design</span>
                                <span class="text-muted">2</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar" role="progressbar" style="width: 50%; background: var(--info);"></div>
                            </div>
                        </div>
                        <div class="category-item">
                            <div class="d-flex justify-content-between">
                                <span>Marketing</span>
                                <span class="text-muted">1</span>
                            </div>
                            <div class="progress mt-2" style="height: 6px; background: rgba(255,255,255,0.1);">
                                <div class="progress-bar" role="progressbar" style="width: 25%; background: var(--success);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Tasks Section -->
            <main class="tasks-section">
                <div class="section-header">
                    <h2 class="section-title">Active Tasks</h2>
                    <button class="create-task-btn">
                        <a href="create_task.php" class="create-task-btn">
                              <i class="fas fa-plus"></i> Create A New Task!
                            </a>
                    </button>
                </div>
                
                <div class="task-grid" id="taskContainer">
                    <?php if ($tasks->num_rows > 0): ?>
                        <?php 
                        $tasks->data_seek(0);
                        while ($task = $tasks->fetch_assoc()): 
                            $priority = $task['priority'];
                            $status = $task['status'];
                            $due_date = $task['due_date'];
                            
                            // Determine if task is overdue
                            $is_overdue = $due_date && strtotime($due_date) < time() && $status !== 'completed';
                        ?>
                        <div class="task-card <?php echo $priority; ?>" 
                             data-priority="<?php echo $priority; ?>" 
                             data-status="<?php echo $status; ?>"
                             data-due-date="<?php echo $due_date; ?>"
                             data-task-id="<?php echo $task['task_id']; ?>">
                            <?php if ($is_overdue): ?>
                                <div class="overdue-badge">OVERDUE</div>
                            <?php endif; ?>
                            
                            <div class="task-id">#<?php echo $task['task_id']; ?></div>
                            
                            <div class="task-header">
                                <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <div>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php 
                                        if ($status === 'todo') echo 'To Do';
                                        if ($status === 'in_progress') echo 'In Progress';
                                        ?>
                                    </span>
                                    <span class="priority-badge priority-<?php echo $priority; ?>">
                                        <?php echo ucfirst($priority); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($task['description'])): ?>
                                <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="task-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Due Date</span>
                                    <span class="meta-value">
                                        <?php echo $due_date ? date('M j, Y', strtotime($due_date)) : 'No due date'; ?>
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Created</span>
                                    <span class="meta-value"><?php echo date('M j, Y', strtotime($task['created_at'])); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($task['categories'])): ?>
                                <div class="task-categories">
                                    <?php 
                                    $categories = explode(', ', $task['categories']);
                                    foreach ($categories as $category): 
                                    ?>
                                        <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
<div class="task-actions">
    <button class="complete-btn" data-bs-toggle="modal" data-bs-target="#completeModal" data-task-id="<?php echo $task['task_id']; ?>">
        <i class="fas fa-check"></i> Complete Task
    </button>
                                <button class="delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal" data-task-id="<?php echo $task['task_id']; ?>">
                                    <i class="fas fa-trash"></i> Delete Task
                                </button>
                            </div>
                        </div>




                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <h3>No Active Tasks</h3>
                            <p>You don't have any active tasks at the moment. Great job! Create a new task to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
   <!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this task? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> All task data will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-confirm-delete" id="confirmDelete">Delete Task</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Completion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark this task as complete?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-confirm-complete" id="confirmComplete">Complete Task</button>

            </div>
        </div>
    </div>
</div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const taskContainer = document.getElementById('taskContainer');
    const deleteModal = document.getElementById('deleteModal');
    const completeModal = document.getElementById('completeModal');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const confirmCompleteBtn = document.getElementById('confirmComplete');
    let taskToDelete = null;
    let taskToComplete = null;

    // Filter elements
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dueDateFilter = document.getElementById('dueDateFilter');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const filterTagsContainer = document.getElementById('filterTags');
    const taskCards = document.querySelectorAll('.task-card');

    // ===== DELETE TASK =====
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        taskToDelete = button.getAttribute('data-task-id');
    });

    confirmDeleteBtn.addEventListener('click', function () {
        if (!taskToDelete) return;

        fetch('delete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task_id: taskToDelete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const taskCard = document.querySelector(`.task-card[data-task-id="${taskToDelete}"]`);
                if (taskCard) taskCard.remove();

                updateTaskCount();
                showNotification('Task deleted successfully!', 'success');
                checkEmptyState();
            } else {
                showNotification(data.message || 'Failed to delete task', 'error');
            }
        })
        .catch(() => showNotification('Error deleting task', 'error'))
        .finally(() => {
            bootstrap.Modal.getInstance(deleteModal).hide();
        });
    });

    // ===== COMPLETE TASK =====
    completeModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        taskToComplete = button.getAttribute('data-task-id');
    });

    confirmCompleteBtn.addEventListener('click', function () {
        if (!taskToComplete) return;

        fetch('complete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task_id: taskToComplete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const taskCard = document.querySelector(`.task-card[data-task-id="${taskToComplete}"]`);
                if (taskCard) taskCard.remove();

                updateTaskCount();
                showNotification('Task marked as complete!', 'success');
                checkEmptyState();
            } else {
                showNotification(data.message || 'Failed to complete task', 'error');
            }
        })
        .catch(() => showNotification('Error completing task', 'error'))
        .finally(() => {
            bootstrap.Modal.getInstance(completeModal).hide();
        });
    });

    // ===== FILTER FUNCTIONALITY =====
    applyFiltersBtn.addEventListener('click', applyFilters);
    resetFiltersBtn.addEventListener('click', function() {
        priorityFilter.value = 'all';
        statusFilter.value = 'all';
        dueDateFilter.value = 'all';
        filterTagsContainer.innerHTML = '';
        applyFilters();
    });

    function applyFilters() {
        const selectedPriority = priorityFilter.value;
        const selectedStatus = statusFilter.value;
        const selectedDueDate = dueDateFilter.value;

        // Clear existing filter tags
        filterTagsContainer.innerHTML = '';

        // Add filter tags for selected filters
        if (selectedPriority !== 'all') {
            addFilterTag('Priority: ' + selectedPriority.charAt(0).toUpperCase() + selectedPriority.slice(1), 'priorityFilter');
        }

        if (selectedStatus !== 'all') {
            addFilterTag('Status: ' + selectedStatus.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '), 'statusFilter');
        }

        if (selectedDueDate !== 'all') {
            addFilterTag('Due Date: ' + selectedDueDate.charAt(0).toUpperCase() + selectedDueDate.slice(1), 'dueDateFilter');
        }

        // Filter the task cards
        taskCards.forEach(card => {
            const cardPriority = card.dataset.priority;
            const cardStatus = card.dataset.status;
            const cardDueDate = card.dataset.dueDate;

            let priorityMatch = true;
            let statusMatch = true;
            let dueDateMatch = true;

            // Check priority filter
            if (selectedPriority !== 'all' && selectedPriority !== cardPriority) {
                priorityMatch = false;
            }

            // Check status filter
            if (selectedStatus !== 'all' && selectedStatus !== cardStatus) {
                statusMatch = false;
            }

            // Check due date filter
            if (selectedDueDate !== 'all') {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (cardDueDate) {
                    const dueDate = new Date(cardDueDate);
                    dueDate.setHours(0, 0, 0, 0);

                    const oneWeekFromNow = new Date();
                    oneWeekFromNow.setDate(today.getDate() + 7);

                    switch (selectedDueDate) {
                        case 'today':
                            dueDateMatch = dueDate.getTime() === today.getTime();
                            break;
                        case 'week':
                            dueDateMatch = dueDate >= today && dueDate <= oneWeekFromNow;
                            break;
                        case 'overdue':
                            dueDateMatch = dueDate < today;
                            break;
                    }
                } else {
                    // If the task has no due date, only show it if "all" is selected
                    dueDateMatch = selectedDueDate === 'all';
                }
            }

            // Show or hide the card based on the filters
            if (priorityMatch && statusMatch && dueDateMatch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Check if we need to show the empty state
        checkEmptyState();
    }

    function addFilterTag(text, filterId) {
        const tag = document.createElement('div');
        tag.className = 'filter-tag';
        tag.innerHTML = `
            ${text}
            <span class="remove" onclick="removeFilterTag('${filterId}')">×</span>
        `;
        filterTagsContainer.appendChild(tag);
    }

    // Make the removeFilterTag function available globally
    window.removeFilterTag = function(filterId) {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.value = 'all';
            applyFilters();
        }
    };

    // ===== Helper Functions =====
    function updateTaskCount() {
        const taskCount = document.querySelectorAll('.task-card[style="display: flex"]').length;
        const statNumber = document.querySelector('.stat-card .number');
        if (statNumber) statNumber.textContent = taskCount;
    }

    function checkEmptyState() {
        const visibleTasks = document.querySelectorAll('.task-card[style="display: flex"]');
        const emptyState = document.querySelector('.empty-state');

        if (visibleTasks.length === 0) {
            if (!emptyState) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'empty-state';
                emptyDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <h3>No Tasks Match Your Filters</h3>
                    <p>Try adjusting your filter criteria to see more tasks.</p>
                    <button class="btn-filter btn-outline" id="resetFiltersFromEmpty">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                `;
                taskContainer.appendChild(emptyDiv);

                // Add event listener to the reset button in the empty state
                document.getElementById('resetFiltersFromEmpty').addEventListener('click', function() {
                    priorityFilter.value = 'all';
                    statusFilter.value = 'all';
                    dueDateFilter.value = 'all';
                    filterTagsContainer.innerHTML = '';
                    applyFilters();
                });
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    }

    function showNotification(message, type = 'success') {
        document.querySelectorAll('.custom-notification').forEach(el => el.remove());
        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Notification style
    const style = document.createElement('style');
    style.innerHTML = `
        .custom-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s, fadeOut 0.5s 2.5s forwards;
        }
        .success { background: var(--success); }
        .error { background: var(--danger); }
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        @keyframes fadeOut {
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});

</script>




</body>

</html>