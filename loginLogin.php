<?php
session_start();
include 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Processing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121830;
            color: #eee;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 100px;
            max-width: 600px;
        }
        .card {
            background-color: #1e273f;
            border: 1px solid #3a3f58;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(108, 92, 231, 0.2);
        }
        .debug {
            font-family: monospace;
            background-color: #252f52;
            color: #a29bfe;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .success {
            color: #00cec9;
            font-weight: bold;
        }
        .error {
            color: #ff7675;
            font-weight: bold;
        }
        a {
            color: #a29bfe;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
            color: #dfe6e9;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h3 class="mb-4">Login Debug Info</h3>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $sql = "SELECT id, password FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $hashedPassword);
                $stmt->fetch();

                echo "<div class='debug'>Stored hash: <code>" . htmlentities($hashedPassword) . "</code></div>";
                echo "<div class='debug'>You entered: <code>" . htmlentities($password) . "</code></div>";

                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    echo "<p class='success'>✅ Login success! Redirecting...</p>";
                    echo "<script>setTimeout(() => window.location.href = 'dashboard.php', 2000);</script>";
                } else {
                    echo "<p class='error'>❌ Incorrect password.</p>";
                }
            } else {
                echo "<p class='error'>❌ Username not found or multiple entries exist.</p>";
            }

            $stmt->close();
        } else {
            echo "<p class='error'>Form was not submitted correctly.</p>";
        }

        $conn->close();
        ?>
        <p class="mt-3"><a href="loginForm.php">Back to login</a></p>
    </div>
</div>
</body>
</html>
