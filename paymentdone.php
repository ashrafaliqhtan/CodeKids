<?php
// Start secure session
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

include('./dbConnection.php');

// Validate CSRF token first
if(!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('<div class="error-container">
            <div class="error-icon"><i class="uil uil-shield-exclamation"></i></div>
            <h2>Security Error</h2>
            <p>CSRF token validation failed. Please go back and try again.</p>
            <a href="courses.php" class="btn">Return to Courses</a>
         </div>');
}

// Validate user session
if(!isset($_SESSION['stuLogEmail'])) {
    header("Location: loginSignUp.php");
    exit();
}

// Validate required parameters
if(!isset($_POST['ORDER_ID']) || !isset($_POST['TXN_AMOUNT']) || !isset($_SESSION['course_id'])) {
    die('<div class="error-container">
            <div class="error-icon"><i class="uil uil-exclamation-triangle"></i></div>
            <h2>Invalid Request</h2>
            <p>Required parameters are missing.</p>
            <a href="courses.php" class="btn">Return to Courses</a>
         </div>');
}

date_default_timezone_set('Asia/Kolkata');
$date = date('d-m-y h:i:s');
$order_id = htmlspecialchars($_POST['ORDER_ID']);
$stu_email = $_SESSION['stuLogEmail'];
$course_id = (int)$_SESSION['course_id'];
$amount = (float)$_POST['TXN_AMOUNT'];

// Validate amount
if(!is_numeric($amount) || $amount <= 0) {
    die('<div class="error-container">
            <div class="error-icon"><i class="uil uil-money-bill-slash"></i></div>
            <h2>Invalid Amount</h2>
            <p>The payment amount is invalid.</p>
            <a href="courses.php" class="btn">Return to Courses</a>
         </div>');
}

// Insert order using prepared statement
$stmt = $conn->prepare("INSERT INTO courseorder (order_id, stu_email, course_id, status, respmsg, amount, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$status = "Success";
$respmsg = "Done";
$stmt->bind_param("ssissss", $order_id, $stu_email, $course_id, $status, $respmsg, $amount, $date);

if($stmt->execute()) {
    // Clear sensitive data from session
    unset($_SESSION['course_id']);
    unset($_SESSION['csrf_token']);
    
    // Generate new CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // Display success message
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful | CodeKids</title>
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>
            :root {
                --color-success: #00bf8e;
                --color-white: #fff;
                --color-bg: #1f2641;
            }
            
            body {
                font-family: 'Montserrat', sans-serif;
                background: var(--color-bg);
                color: var(--color-white);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 1rem;
            }
            
            .success-container {
                background: rgba(255,255,255,0.1);
                padding: 2rem;
                border-radius: 1rem;
                max-width: 500px;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            
            .success-icon {
                font-size: 4rem;
                color: var(--color-success);
                margin-bottom: 1rem;
            }
            
            .redirect-message {
                margin-top: 2rem;
                font-size: 0.9rem;
                color: rgba(255,255,255,0.7);
            }
            
            .btn {
                display: inline-block;
                padding: 0.8rem 1.5rem;
                background: var(--color-success);
                color: var(--color-white);
                border-radius: 0.5rem;
                text-decoration: none;
                margin-top: 1rem;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">
                <i class="uil uil-check-circle"></i>
            </div>
            <h2>Payment Successful!</h2>
            <p>Thank you for your purchase. The course has been added to your account.</p>
            <p class="redirect-message">You will be redirected to your courses shortly...</p>
            <a href="Student/myCourses.php" class="btn">Go to My Courses</a>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "Student/myCourses.php";
            }, 5000);
        </script>
    </body>
    </html>
    <?php
} else {
    // Display error message
    echo '<div class="error-container">
            <div class="error-icon"><i class="uil uil-exclamation-triangle"></i></div>
            <h2>Payment Error</h2>
            <p>There was an error processing your payment. Please contact support.</p>
            <a href="courses.php" class="btn">Return to Courses</a>
          </div>';
}

$stmt->close();
$conn->close();
?>