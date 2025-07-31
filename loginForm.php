<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login | Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --dark: #121826;
            --darker: #0d1117;
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            max-width: 460px;
            width: 100%;
            animation: fadeIn 0.6s ease forwards;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .card-header {
            background: rgba(108, 92, 231, 0.15);
            border-bottom: 1px solid var(--glass);
            padding: 25px;
            text-align: center;
        }

        .card-header h3 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            color: var(--text);
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass);
            color: var(--text);
            border-radius: 12px;
            padding: 14px 18px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            color: var(--text);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 10;
        }

        .input-with-icon {
            padding-left: 50px;
        }

        .btn {
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #5d4de0;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
        }

        .form-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--glass);
            color: var(--text-secondary);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .form-footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-sign-in-alt me-2"></i>Login to Your Account</h3>
            </div>
            <div class="card-body">
                <form method="post" action="loginLogin.php">
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" class="form-control input-with-icon" placeholder="Enter your username" required autofocus />
                    </div>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="Enter your password" required />
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-sign-in-alt"></i>Login
                    </button>
                </form>

                <div class="form-footer">
                    <p>Don't have an account? <a href="signupForm.php"><i class="fas fa-user-plus"></i> Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Input focus effect
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (icon) icon.style.color = 'var(--secondary)';
                });

                input.addEventListener('blur', function () {
                    const icon = this.parentElement.querySelector('.input-icon');
                    if (icon) icon.style.color = 'var(--primary)';
                });
            });
        });
    </script>
</body>
</html>
