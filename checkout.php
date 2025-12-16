<?php
// Start secure session
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

include('./dbConnection.php');

// Validate user session
if(!isset($_SESSION['stuLogEmail'])) {
    header("Location: loginSignUp.php");
    exit();
}

// Validate CSRF token
if(!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('<div class="error-container">
            <div class="error-icon"><i class="uil uil-shield-exclamation"></i></div>
            <h2>Security Error</h2>
            <p>CSRF token validation failed. Please go back and try again.</p>
            <a href="courses.php" class="btn">Return to Courses</a>
         </div>');
}

// Validate course ID
if(!isset($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
    header("Location: courses.php");
    exit();
}

// Store course ID in session
$_SESSION['course_id'] = (int)$_POST['course_id'];

// Fetch course price
$stmt = $conn->prepare("SELECT course_price FROM course WHERE course_id = ?");
$stmt->bind_param("i", $_SESSION['course_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: courses.php");
    exit();
}

$course = $result->fetch_assoc();
$course_price = $course['course_price'];

// Security headers
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | CodeKids</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-danger: #f75842;
            --color-white: #fff;
            --color-light: rgba(255,255,255,0.7);
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
            padding: 1rem;
        }
        
        .checkout-container {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        
        .heading {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .heading h1 {
            font-size: 1.8rem;
            color: var(--color-primary);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--color-light);
        }
        
        input {
            width: 100%;
            padding: 0.8rem;
            border-radius: 0.5rem;
            border: none;
            background: rgba(255,255,255,0.1);
            color: var(--color-white);
            font-size: 1rem;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--color-success);
            color: var(--color-white);
        }
        
        .btn-secondary {
            background: var(--color-danger);
            color: var(--color-white);
        }
        
        .note {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--color-light);
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
        }
        
        .error-icon {
            font-size: 3rem;
            color: var(--color-danger);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="heading">
            <h1><i class="uil uil-lock-alt"></i> Secure Checkout</h1>
        </div>
        <form method="POST" action="paymentdone.php" id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="ORDER_ID">Order ID</label>
                <input type="text" id="ORDER_ID" name="ORDER_ID" value="<?php echo 'ORDS' . rand(10000, 99999999); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="CUST_ID">Student Email</label>
                <input id="CUST_ID" name="CUST_ID" type="email" value="<?php echo htmlspecialchars($_SESSION['stuLogEmail']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="TXN_AMOUNT">Amount (Rs.)</label>
                <input type="text" id="TXN_AMOUNT" name="TXN_AMOUNT" value="<?php echo htmlspecialchars($course_price); ?>" readonly>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="uil uil-check-circle"></i> Complete Payment
                </button>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="uil uil-times-circle"></i> Cancel
                </a>
            </div>
        </form>
        <p class="note"><i class="uil uil-shield-check"></i> Your payment is secured with 256-bit encryption</p>
    </div>
    
    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add any client-side validation here
            this.submit();
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>