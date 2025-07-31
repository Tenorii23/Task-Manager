<!-- Add this to your dashboard after the statistics section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h3 class="card-title">Create New Task</h3>
        <form action="create_task.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="datetime-local" class="form-control" id="due_date" name="due_date">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Categories</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    // Fetch user's categories
                    $categories_query = "SELECT category_id, name FROM categories WHERE user_id = ?";
                    $stmt = $conn->prepare($categories_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $categories = $stmt->get_result();
                    
                    while ($category = $categories->fetch_assoc()) {
                        echo '<div class="form-check">';
                        echo '<input class="form-check-input" type="checkbox" name="categories[]" value="'.$category['category_id'].'" id="cat_'.$category['category_id'].'">';
                        echo '<label class="form-check-label" for="cat_'.$category['category_id'].'">'.$category['name'].'</label>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Task</button>
        </form>
    </div>
</div>