<?php
session_start();
include('./dbConnection.php'); // Make sure the path to your database connection file is correct

$loginMsg = "";
$regMsg   = "";

// Process login form submission
if (isset($_POST['login'])) {
    $stuLemail = trim($_POST['stuLemail']);
    $stuLpass  = trim($_POST['stuLpass']);

    // Query to verify user credentials
    $sql = "SELECT * FROM students WHERE stu_email='$stuLemail' AND stu_pass='$stuLpass'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $_SESSION['is_login']    = true;
        $_SESSION['stuLogEmail'] = $stuLemail;
        // Redirect to checkout.php upon successful login
        header("Location: quiz.php");
        exit();
    } else {
        $loginMsg = "Invalid credentials, please try again.";
    }
}

// Process registration form submission
if (isset($_POST['register'])) {
    $stuname   = trim($_POST['stuname']);
    $stuemail  = trim($_POST['stuemail']);
    $stupass   = trim($_POST['stupass']);
    $stuCpass  = trim($_POST['stuCpass']);
    $stu_occ   = trim($_POST['stu_occ']);
    
    // Default image value for new users
    $stu_img   = "default.jpg";

    // Validate that password and confirm password match
    if ($stupass !== $stuCpass) {
        $regMsg = "Passwords do not match.";
    } else {
        // Check if the email is already registered
        $checkSql = "SELECT * FROM students WHERE stu_email='$stuemail'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult && $checkResult->num_rows > 0) {
            $regMsg = "The email address is already in use.";
        } else {
            $sql = "INSERT INTO students (stu_name, stu_email, stu_pass, stu_occ, stu_img) 
                    VALUES ('$stuname', '$stuemail', '$stupass', '$stu_occ', '$stu_img')";
            if ($conn->query($sql) === TRUE) {
                // Redirect to checkout.php upon successful registration
                header("Location: quiz.php");
                exit();
            } else {
                $regMsg = "An error occurred during registration: " . $conn->error;
            }
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
  <!-- External fonts and icons -->
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
  <link rel="shortcut icon" type="image/png" href="../images/logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/loginSignUp.css">
  <title>CodingHour || Login and Registration</title>
  <style>
    /* Basic styling */
    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 90%;
        max-width: 800px;
        margin: 30px auto;
        background: #fff;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
    }
    form {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 10px;
    }
    label span {
        display: block;
        margin-bottom: 5px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .message {
        color: green;
        text-align: center;
    }
    .error {
        color: red;
        text-align: center;
    }
    .btn {
        display: block;
        width: 100%;
        padding: 10px;
        background: #007bff;
        border: none;
        color: #fff;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn:hover {
        background: #0056b3;
    }
    .toggle-link {
        text-align: center;
        display: block;
        margin-top: 10px;
        color: #007bff;
        cursor: pointer;
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav>
    <div class="container nav_container">
      <a class="home_button" href="index.php"><h3>CodingHour</h3></a>
      <ul class="nav_menu">
        <li><a href="#">Login</a></li>
        <li><a href="#">Certification</a></li>
        <li><a href="paymentStatus.php">Payment Status</a></li>
        <li><a href="#">Register</a></li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <!-- Login Form -->
    <div id="loginForm">
      <h2>Login</h2>
      <?php if (!empty($loginMsg)): ?>
        <p class="<?php echo ($loginMsg === "Login successful." ? 'message' : 'error'); ?>">
          <?php echo $loginMsg; ?>
        </p>
      <?php endif; ?>
      <form action="" method="POST" onsubmit="return validateLogin();">
        <label>
          <span>Email</span>
          <input type="email" name="stuLemail" id="loginEmail" placeholder="Enter your email" required>
        </label>
        <label>
          <span>Password</span>
          <input type="password" name="stuLpass" id="loginPass" placeholder="Enter your password" required>
        </label>
        <button type="submit" name="login" class="btn">Login</button>
      </form>
      <p class="toggle-link" onclick="toggleForms('register')">
        Don't have an account? Register now!
      </p>
    </div>

    <!-- Registration Form -->
    <div id="regForm" style="display: none;">
      <h2>Create a New Account</h2>
      <?php if (!empty($regMsg)): ?>
        <p class="<?php echo ($regMsg === "Account created successfully. You can now log in." ? 'message' : 'error'); ?>">
          <?php echo $regMsg; ?>
        </p>
      <?php endif; ?>
      <form action="" method="POST" onsubmit="return validateRegister();">
        <label>
          <span>Full Name</span>
          <input type="text" name="stuname" id="regName" placeholder="Enter your full name" required>
        </label>
        <label>
          <span>Email</span>
          <input type="email" name="stuemail" id="regEmail" placeholder="Enter your email" required>
        </label>
        <label>
          <span>Password</span>
          <input type="password" name="stupass" id="regPass" placeholder="Enter your password" required>
        </label>
        <label>
          <span>Confirm Password</span>
          <input type="password" name="stuCpass" id="regCpass" placeholder="Confirm your password" required>
        </label>
        <label>
          <span>Occupation</span>
          <input type="text" name="stu_occ" id="regOcc" placeholder="Enter your occupation" required>
        </label>
        <button type="submit" name="register" class="btn">Register</button>
      </form>
      <p class="toggle-link" onclick="toggleForms('login')">
        Already have an account? Login here!
      </p>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p style="text-align: center;">
      <a href="#">&copy; CodingHour</a></p>
    </div>
  </footer>

  <!-- JavaScript for toggling forms and client-side validation -->
  <script>
    // Toggle display between login and registration forms
    function toggleForms(formType) {
      if (formType === 'register') {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('regForm').style.display = 'block';
      } else {
        document.getElementById('regForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
      }
    }

    // Simple client-side validation for the login form
    function validateLogin() {
      var email = document.getElementById('loginEmail').value.trim();
      var pass  = document.getElementById('loginPass').value.trim();
      if (email === "" || pass === "") {
        alert("Please fill in both email and password.");
        return false;
      }
      return true;
    }

    // Simple client-side validation for the registration form
    function validateRegister() {
      var name   = document.getElementById('regName').value.trim();
      var email  = document.getElementById('regEmail').value.trim();
      var pass   = document.getElementById('regPass').value.trim();
      var cpass  = document.getElementById('regCpass').value.trim();
      var occ    = document.getElementById('regOcc').value.trim();

      if (name === "" || email === "" || pass === "" || cpass === "" || occ === "") {
        alert("Please fill in all required fields.");
        return false;
      }
      if (pass !== cpass) {
        alert("Passwords do not match.");
        return false;
      }
      return true;
    }
  </script>
</body>
</html>