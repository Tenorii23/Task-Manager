<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Task Manager</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .app-container {
            max-width: 460px;
            width: 100%;
            animation: fadeIn 0.6s ease forwards;
        }
        
        .app-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .app-title {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .app-title h1 {
            font-weight: 700;
            font-size: 2.5rem;
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
        
        .app-description {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Card Styles */
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
        
        /* Form Styles */
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
            transition: all 0.3s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .form-footer a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes floatUp {
            0% { transform: translateY(10px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        
        .form-group {
            animation: floatUp 0.5s ease forwards;
            animation-delay: calc(var(--i) * 0.1s);
        }
        
        /* Decorative Elements */
        .decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), transparent);
            filter: blur(60px);
            z-index: -1;
            opacity: 0.3;
        }
        
        .decoration:nth-child(1) {
            top: 10%;
            left: 10%;
        }
        
        .decoration:nth-child(2) {
            bottom: 10%;
            right: 10%;
            background: linear-gradient(45deg, var(--info), transparent);
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .card-body {
                padding: 25px 20px;
            }
            
            .app-title h1 {
                font-size: 2rem;
            }
            
            .card-header h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative background elements -->
    <div class="decoration"></div>
    <div class="decoration"></div>
    
    <div class="app-container">
        <header class="app-header">
            <div class="app-title">
                <i class="fas fa-tasks"></i>
                <h1>Task Manager</h1>
            </div>
            <p class="app-description">Join thousands of users who manage their tasks efficiently with our platform</p>
        </header>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-plus me-2"></i>Create Your Account</h3>
            </div>
            <div class="card-body">
                <form method="post" action="register.php">
                    <div class="form-group" style="--i: 1">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i>Username
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-user-circle"></i>
                            </span>
                            <input type="text" name="username" id="username" class="form-control input-with-icon" required placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="form-group" style="--i: 2">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i>Password
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control input-with-icon" required placeholder="Create a strong password">
                        </div>
                    </div>
                    
                    <div class="form-group" style="--i: 3">
                        <div class="input-group">
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-user-plus"></i>Register Now
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="loginForm.php"><i class="fas fa-sign-in-alt"></i>Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to form elements
        document.addEventListener('DOMContentLoaded', function() {
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.style.opacity = '0';
            });
            
            setTimeout(() => {
                formGroups.forEach(group => {
                    group.style.animation = 'floatUp 0.5s ease forwards';
                });
            }, 300);
            
            // Add focus effects
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--secondary)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--primary)';
                });
            });
        });
    </script>
</body>
</html>