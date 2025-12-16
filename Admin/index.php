<?php
session_start();
include('../dbConnection.php');

// Redirect if already logged in
if(isset($_SESSION['is_admin_login'])) {
    header("Location: adminDashboard.php");
    exit();
}

// Initialize variables
$error = '';
$loginAttempts = $_SESSION['login_attempts'] ?? 0;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    if($loginAttempts >= 5) {
        $error = "Too many attempts! Try again later.";
    } else {
        // Validate and sanitize inputs
        $adminEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $adminPass = $_POST['password'] ?? '';

        if(empty($adminEmail) || empty($adminPass)) {
            $error = "Please fill all fields";
        } else {
            try {
                // Prepare statement to prevent SQL injection
                $stmt = $conn->prepare("SELECT admin_id, admin_pass FROM admin WHERE admin_email = ?");
                $stmt->bind_param("s", $adminEmail);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    if(password_verify($adminPass, $admin['admin_pass'])) {
                        // Successful login - regenerate session ID
                        session_regenerate_id(true);
                        
                        $_SESSION['is_admin_login'] = true;
                        $_SESSION['admin_id'] = $admin['admin_id'];
                        $_SESSION['login_attempts'] = 0;
                        
                        // Set secure session cookie params
                        setcookie(session_name(), session_id(), [
                            'expires' => time() + 86400,
                            'path' => '/',
                            'domain' => '',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]);
                        
                        header("Location: adminDashboard.php");
                        exit();
                    } else {
                        throw new Exception("Invalid credentials");
                    }
                } else {
                    throw new Exception("Account not found");
                }
            } catch(Exception $e) {
                $error = $e->getMessage();
                $_SESSION['login_attempts'] = ++$loginAttempts;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Login | CodeKids</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-white: #fff;
            --color-light: rgba(255,255,255,0.7);
            --color-bg: #1f2641;
            --color-bg1: #2e3267;
            --color-bg2: #424890;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--color-bg1);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
        }

        .login-container {
            background: var(--color-bg2);
            padding: 3rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 2rem 3rem rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-logo {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--color-white);
        }

        .login-title {
            color: var(--color-white);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-light);
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border-radius: 0.5rem;
            border: none;
            background: var(--color-bg1);
            color: var(--color-white);
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: 2px solid var(--color-primary);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: var(--color-success);
            color: var(--color-white);
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .login-btn:hover {
            background: var(--color-warning);
        }

        .error-msg {
            color: var(--color-danger);
            margin: 1rem 0;
            min-height: 1.5rem;
        }

        .attempts-warning {
            color: var(--color-warning);
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .signup-link {
            margin-top: 1.5rem;
            color: var(--color-light);
        }

        .signup-link a {
            color: var(--color-primary);
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">🎮 CodeKids</div>
        <h2 class="login-title">Admin Portal</h2>
        
        <?php if($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>

        <?php if($loginAttempts > 0): ?>
            <div class="attempts-warning">
                Login attempts: <?php echo $loginAttempts; ?>/5
            </div>
        <?php endif; ?>

        <div class="signup-link">
            Don't have an account? <a href="admin-signup.php">Sign up</a>
        </div>
    </div>
    
       <script>
        // Add interactive animations
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', function(e) {
            if(<?= $loginAttempts ?> >= 4) {
                e.preventDefault();
                form.classList.add('shake');
                setTimeout(() => form.classList.remove('shake'), 500);
            }
        });

        // Add input validation feedback
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('invalid', () => {
                input.style.borderColor = '#ff4444';
                input.parentElement.classList.add('shake');
                setTimeout(() => input.parentElement.classList.remove('shake'), 500);
            });
            
            input.addEventListener('input', () => {
                if(input.validity.valid) {
                    input.style.borderColor = '#4ECDC4';
                }
            });
        });
    </script> 
    
    
    
</body>
</html>