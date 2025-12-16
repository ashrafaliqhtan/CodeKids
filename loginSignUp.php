<?php  
session_start();  
include('./dbConnection.php');  
  
// Handle form toggling  
$show_signup = isset($_GET['show_signup']) ? true : false;  
  
// Handle Student Login  
if(isset($_POST['stuLogEmail']) && isset($_POST['stuLogPass'])) {  
    $stuLogEmail = $_POST['stuLogEmail'];  
    $stuLogPass = $_POST['stuLogPass'];  
      
    $sql = "SELECT stu_email, stu_pass FROM students WHERE stu_email='".$stuLogEmail."'";  
    $result = $conn->query($sql);  
      
    if($result->num_rows == 1) {  
        $row = $result->fetch_assoc();  
        if(password_verify($stuLogPass, $row['stu_pass'])) {  
            $_SESSION['is_login'] = true;  
            $_SESSION['stuLogEmail'] = $stuLogEmail;  
            header('Location: Student/studentProfile.php');  
            exit();  
        } else {  
            $login_error = "Invalid Email or Password";  
        }  
    } else {  
        $login_error = "Invalid Email or Password";  
    }  
}  
  
// Handle Student Signup  
if(isset($_POST['stuname']) && isset($_POST['stuemail']) && isset($_POST['stupass'])) {  
    $stuname = $_POST['stuname'];  
    $stuemail = $_POST['stuemail'];  
    $stupass = password_hash($_POST['stupass'], PASSWORD_BCRYPT);  
      
    // Check if email exists  
    $check_sql = "SELECT stu_email FROM students WHERE stu_email='".$stuemail."'";  
    $check_result = $conn->query($check_sql);  
      
    if($check_result->num_rows > 0) {  
        $signup_error = "Email already registered!";  
        $show_signup = true;  
    } else {  
        $insert_sql = "INSERT INTO students(stu_name, stu_email, stu_pass) VALUES('$stuname', '$stuemail', '$stupass')";  
        if($conn->query($insert_sql)) {  
            $_SESSION['is_login'] = true;  
            $_SESSION['stuLogEmail'] = $stuemail;
            header('Location: Student/studentProfile.php');
            exit();
        } else {  
            $signup_error = "Registration failed: " . $conn->error;  
            $show_signup = true;  
        }  
    }  
}  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8" />  
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />  
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />  
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />  
    <link rel="shortcut icon" type="image/png" href="images/logo.png" />  
    <link rel="preconnect" href="https://fonts.googleapis.com">  
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>  
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">  
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>  
    <link rel="stylesheet" href="css/style.css" />  
    <title>CodeKids | Login/Signup</title>  
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #4a42e8;
            --accent-color: #ff6584;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --error-color: #dc3545;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .overlay {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            margin-top: 80px;
            width: 100%;
        }
        
        .wrapper {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: var(--dark-color);
            text-decoration: none;
            z-index: 10;
        }
        
        .column {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .details {
            background: white;
            position: relative;
        }
        
        .content {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .content p {
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 80%;
        }
        
        .stu-form-header {
            font-size: 2rem;
            color: var(--dark-color);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .span-header {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        
        input {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            width: 100%;
            box-sizing: border-box;
        }
        
        input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
            outline: none;
        }
        
        .form-submit {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .form-submit:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .form-span {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .toggle-form {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .toggle-form:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        a[href="#"] {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            text-align: right;
            transition: all 0.3s;
        }
        
        a[href="#"]:hover {
            color: var(--primary-color);
        }
        
        small {
            display: block;
            text-align: center;
            margin-top: -0.5rem;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .signin, .signup {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .wrapper {
                flex-direction: column;
                min-height: auto;
            }
            
            .content {
                padding: 2rem 1rem;
            }
            
            .content p {
                max-width: 100%;
            }
            
            .overlay {
                margin-top: 60px;
                padding: 1rem;
            }
        }
        
        /* Floating labels effect */
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group input {
            padding: 15px 15px 15px 45px;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        /* Button loading effect */
        .btn-loading .form-submit {
            position: relative;
            pointer-events: none;
        }
        
        .btn-loading .form-submit:after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: button-loading-spinner 1s linear infinite;
        }
        
        @keyframes button-loading-spinner {
            from { transform: rotate(0turn); }
            to { transform: rotate(1turn); }
        }
    </style>
</head>  
<body>  
    <nav>  
        <div class="container nav_container">  
            <a class="home_button" href="index.php"><h3>🎮 CodeKids</h3></a>  
            <ul class="nav_menu">  
                <?php if(isset($_SESSION['is_login'])): ?>  
                    <li><a href="myCourses.php"><i class="uil uil-book-alt"></i> My Courses</a></li>  
                    <li><a href="logout.php"><i class="uil uil-signout"></i> Logout</a></li>  
                <?php else: ?>  
                    <li><a href="?show_signup=true#explore"><i class="uil uil-user-plus"></i> Sign Up</a></li>  
                    <li><a href="#explore"><i class="uil uil-signin"></i> Login</a></li>  
                <?php endif; ?>  
            </ul>  
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>  
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>  
        </div>  
    </nav>  

    <div id="explore" class="overlay" style="<?= (isset($_GET['show_signup']) || isset($_POST['stuname']) || isset($_POST['stuLogEmail'])) ? 'display:flex' : 'display:none' ?>">  
        <div class="wrapper">  
            <a href="#" class="close" onclick="document.getElementById('explore').style.display='none'">&times;</a>  
            <div class="column details">  
                <?php if(!$show_signup && !isset($signup_error)): ?>  
                <!-- Login Form -->  
                <div class="signin">  
                    <h2 class="stu-form-header"><span class="span-header">Student</span> Sign in</h2>  
                    <form method="POST" action="">  
                        <div class="input-group">
                            <i class="uil uil-envelope"></i>
                            <input type="email" placeholder="Email Address" name="stuLogEmail" required   
                                   value="<?= isset($_POST['stuLogEmail']) ? htmlspecialchars($_POST['stuLogEmail']) : '' ?>"/>
                        </div>
                        <div class="input-group">
                            <i class="uil uil-lock"></i>
                            <input type="password" placeholder="Password" name="stuLogPass" required />
                        </div>
                        <?php if(isset($login_error)): ?>  
                            <small style="color:var(--error-color); margin-bottom:.3rem"><?= $login_error ?></small>  
                        <?php endif; ?>  
                        <?php if(isset($signup_success)): ?>  
                            <small style="color:var(--success-color); margin-bottom:.3rem"><?= $signup_success ?></small>  
                        <?php endif; ?>  
                        <a href="#">Forgot Password?</a>  
                        <button type="submit" class="form-submit">Log in</button>  
                    </form>  
                    <span class="form-span">Don't have an account yet?   
                        <a href="?show_signup=true#explore" class="toggle-form">Create one now</a>  
                    </span>  
                </div>  
                <?php else: ?>  
                <!-- Signup Form -->  
                <div class="signup">  
                    <h2 class="stu-form-header"><span class="span-header">Student</span> Sign Up</h2>  
                    <form method="POST" action="">  
                        <div class="input-group">
                            <i class="uil uil-user"></i>
                            <input type="text" placeholder="Full Name" name="stuname" required   
                                   value="<?= isset($_POST['stuname']) ? htmlspecialchars($_POST['stuname']) : '' ?>"/>
                        </div>
                        <div class="input-group">
                            <i class="uil uil-envelope"></i>
                            <input type="email" placeholder="Email Address" name="stuemail" required   
                                   value="<?= isset($_POST['stuemail']) ? htmlspecialchars($_POST['stuemail']) : '' ?>"/>
                        </div>
                        <div class="input-group">
                            <i class="uil uil-lock"></i>
                            <input type="password" placeholder="Password" name="stupass" required />
                        </div>
                        <?php if(isset($signup_error)): ?>  
                            <small style="color:var(--error-color); margin-bottom:.3rem"><?= $signup_error ?></small>  
                        <?php endif; ?>  
                        <button type="submit" class="form-submit">Sign up</button>  
                    </form>  
                    <span class="form-span">Already have an account?   
                        <a href="#explore" class="toggle-form">Sign in here</a>  
                    </span>  
                </div>  
                <?php endif; ?>  
            </div>  
            <div class="column content">  
                <?php if(!$show_signup && !isset($signup_error)): ?>  
                <div class="signin">  
                    <h1>Welcome Back!</h1>  
                    <p>To continue your coding adventure, please sign in with your personal details.</p>  
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Welcome" style="width: 150px; margin-top: 2rem;">  
                </div>  
                <?php else: ?>  
                <div class="signup">  
                    <h1>Hello, Friend!</h1>  
                    <p>Start your coding journey with us today and unlock a world of learning opportunities!</p>  
                    <img src="https://cdn-icons-png.flaticon.com/512/4406/4406251.png" alt="Join Us" style="width: 150px; margin-top: 2rem;">  
                </div>  
                <?php endif; ?>  
            </div>  
        </div>  
    </div>  

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>  
    <script src="js/main.js"></script>  
    <script>
        // Add loading effect to buttons
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('.form-submit');
                submitBtn.innerHTML = '';
                submitBtn.classList.add('btn-loading');
            });
        });
        
        // Show/hide forms based on URL hash
        window.addEventListener('load', function() {
            if(window.location.hash === '#explore') {
                document.getElementById('explore').style.display = 'flex';
            }
        });
        
        // Smooth animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = 'var(--primary-color)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = '#999';
            });
        });
    </script>
</body>  
</html>