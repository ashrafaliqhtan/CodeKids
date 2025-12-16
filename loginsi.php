<?php
session_start();
include('dbConnection.php');

// Initialize variables
$show_signup = isset($_GET['show_signup']) ? (bool)$_GET['show_signup'] : false;
$login_error = '';
$signup_error = '';
$signup_success = '';

// Handle Student Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stuLogEmail'], $_POST['stuLogPass'])) {
    $stuLogEmail = trim($_POST['stuLogEmail']);
    $stuLogPass = $_POST['stuLogPass'];
    
    // Validate email format
    if (!filter_var($stuLogEmail, FILTER_VALIDATE_EMAIL)) {
        $login_error = "Invalid email format";
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT stu_id, stu_email, stu_pass, stu_name FROM students WHERE stu_email = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $stuLogEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                if (password_verify($stuLogPass, $row['stu_pass'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['is_login'] = true;
                    $_SESSION['stu_id'] = $row['stu_id'];
                    $_SESSION['stu_email'] = $row['stu_email'];
                    $_SESSION['stu_name'] = $row['stu_name'];
                    
                    // Set secure session cookie parameters
                    $cookieParams = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        session_id(),
                        [
                            'expires' => time() + 86400, // 1 day
                            'path' => '/',
                            'domain' => $cookieParams['domain'],
                            'secure' => true, // Only send over HTTPS
                            'httponly' => true, // Prevent JavaScript access
                            'samesite' => 'Strict' // Prevent CSRF
                        ]
                    );
                    
                    header('Location: index.php');
                    exit();
                } else {
                    $login_error = "Invalid email or password";
                }
            } else {
                $login_error = "Invalid email or password";
            }
            $stmt->close();
        } else {
            $login_error = "Database error";
        }
    }
}

// Handle Student Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stuname'], $_POST['stuemail'], $_POST['stupass'])) {
    $stuname = trim($_POST['stuname']);
    $stuemail = trim($_POST['stuemail']);
    $stupass = $_POST['stupass'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($stuname)) {
        $errors[] = "Name is required";
    } elseif (strlen($stuname) > 100) {
        $errors[] = "Name is too long";
    }
    
    if (!filter_var($stuemail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif (strlen($stuemail) > 100) {
        $errors[] = "Email is too long";
    }
    
    if (strlen($stupass) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if (empty($errors)) {
        // Check if email exists using prepared statement
        $check_sql = "SELECT stu_email FROM students WHERE stu_email = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if ($check_stmt) {
            $check_stmt->bind_param("s", $stuemail);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $signup_error = "Email already registered!";
                $show_signup = true;
            } else {
                // Hash password
                $hashed_password = password_hash($stupass, PASSWORD_BCRYPT);
                
                // Insert new student with prepared statement
                $insert_sql = "INSERT INTO students(stu_name, stu_email, stu_pass) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                
                if ($insert_stmt) {
                    $insert_stmt->bind_param("sss", $stuname, $stuemail, $hashed_password);
                    
                    if ($insert_stmt->execute()) {
                        $signup_success = "Registration successful! Please login.";
                        $show_signup = false;
                    } else {
                        $signup_error = "Registration failed: " . $insert_stmt->error;
                        $show_signup = true;
                    }
                    $insert_stmt->close();
                } else {
                    $signup_error = "Database error";
                    $show_signup = true;
                }
            }
            $check_stmt->close();
        } else {
            $signup_error = "Database error";
            $show_signup = true;
        }
    } else {
        $signup_error = implode("<br>", $errors);
        $show_signup = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login/Signup</title>
    <style>
        .error { color: red; }
        .success { color: green; }
        .form-container { max-width: 400px; margin: 0 auto; }
        .form-toggle { margin: 20px 0; text-align: center; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if (!empty($login_error)): ?>
            <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($signup_error)): ?>
            <div class="error"><?php echo htmlspecialchars($signup_error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($signup_success)): ?>
            <div class="success"><?php echo htmlspecialchars($signup_success); ?></div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <div id="login-form" <?php echo $show_signup ? 'class="hidden"' : ''; ?>>
            <h2>Student Login</h2>
            <form method="POST" action="">
                <div>
                    <label for="stuLogEmail">Email:</label>
                    <input type="email" id="stuLogEmail" name="stuLogEmail" required>
                </div>
                <div>
                    <label for="stuLogPass">Password:</label>
                    <input type="password" id="stuLogPass" name="stuLogPass" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <div class="form-toggle">
                Don't have an account? <a href="?show_signup=1">Sign up</a>
            </div>
        </div>
        
        <!-- Signup Form -->
        <div id="signup-form" <?php echo !$show_signup ? 'class="hidden"' : ''; ?>>
            <h2>Student Signup</h2>
            <form method="POST" action="">
                <div>
                    <label for="stuname">Full Name:</label>
                    <input type="text" id="stuname" name="stuname" required maxlength="100">
                </div>
                <div>
                    <label for="stuemail">Email:</label>
                    <input type="email" id="stuemail" name="stuemail" required maxlength="100">
                </div>
                <div>
                    <label for="stupass">Password (min 8 chars):</label>
                    <input type="password" id="stupass" name="stupass" required minlength="8">
                </div>
                <button type="submit">Sign Up</button>
            </form>
            <div class="form-toggle">
                Already have an account? <a href="?show_signup=0">Login</a>
            </div>
        </div>
    </div>
</body>
</html>