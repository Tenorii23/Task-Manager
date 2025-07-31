<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginForm.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;
    $due_date = !empty($_POST['due_date']) ? date('Y-m-d H:i:s', strtotime($_POST['due_date'])) : null;
    $priority = $_POST['priority'] ?? 'medium';
    $categories = $_POST['categories'] ?? [];

    // Insert the task
    $insert_task = "INSERT INTO tasks (user_id, title, description, due_date, priority, status) 
                    VALUES (?, ?, ?, ?, ?, 'todo')";
    
    $stmt = $conn->prepare($insert_task);
    $stmt->bind_param("issss", $user_id, $title, $description, $due_date, $priority);
    
    if ($stmt->execute()) {
        $task_id = $stmt->insert_id;
        
        // Insert task categories if any
        if (!empty($categories)) {
            $insert_categories = "INSERT INTO task_categories (task_id, category_id) VALUES (?, ?)";
            $stmt_cat = $conn->prepare($insert_categories);
            
            foreach ($categories as $category_id) {
                $stmt_cat->bind_param("ii", $task_id, $category_id);
                $stmt_cat->execute();
            }
            $stmt_cat->close();
        }
        
        $_SESSION['success_message'] = "Task created successfully!";
    } else {
        $_SESSION['error_message'] = "Error creating task: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Task - Task Manager</title>
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
            max-width: 1000px;
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
        
        /* Card Styles */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .card-header {
            background: rgba(108, 92, 231, 0.15);
            border-bottom: 1px solid var(--glass);
            padding: 20px 25px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 30px;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass);
            color: var(--text);
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            color: var(--text);
        }
        
        .form-control::placeholder {
            color: var(--text-secondary);
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
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
        
        /* Alerts */
        .alert {
            border-radius: 12px;
            border: 1px solid var(--glass);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
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
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </header>
        
        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle me-2"></i>Create New Task
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="create_task.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg" required placeholder="Enter task title">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Describe your task..."></textarea>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Categories</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="1" id="category1">
                                <label class="form-check-label" for="category1">
                                    <span class="badge bg-primary bg-opacity-15 text-primary p-2">Development</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="2" id="category2">
                                <label class="form-check-label" for="category2">
                                    <span class="badge bg-info bg-opacity-15 text-info p-2">Design</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="3" id="category3">
                                <label class="form-check-label" for="category3">
                                    <span class="badge bg-success bg-opacity-15 text-success p-2">Marketing</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="4" id="category4">
                                <label class="form-check-label" for="category4">
                                    <span class="badge bg-warning bg-opacity-15 text-warning p-2">Research</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-5">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Save Task
                        </button>
                        <a href="dashboard.php" class="btn btn-outline px-4">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add subtle hover effect to form elements
            const formControls = document.querySelectorAll('.form-control, .form-select');
            formControls.forEach(control => {
                control.addEventListener('mouseover', function() {
                    this.style.borderColor = 'rgba(108, 92, 231, 0.5)';
                });
                control.addEventListener('mouseout', function() {
                    this.style.borderColor = 'rgba(255, 255, 255, 0.08)';
                });
            });
        });
    </script>
</body>
</html>