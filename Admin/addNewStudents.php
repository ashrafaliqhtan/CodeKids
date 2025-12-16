<?php
if(!isset($_SESSION)){
    session_start();
}
include('../dbConnection.php');

// Redirect if not admin
if(!isset($_SESSION['is_admin_login'])){
    header("Location: ../index.php");
    exit();
}

$adminEmail = $_SESSION['adminLogEmail'] ?? 'Admin';
$msg = '';

// Process form submission
if(isset($_REQUEST['courseSubmitBtn'])){
    // Validate required fields
    if(empty($_REQUEST['stu_name']) || empty($_REQUEST['stu_email']) || 
       empty($_REQUEST['stu_pass']) || empty($_REQUEST['stu_occ']) ||
       empty($_FILES['stu_img']['name'])){
        
        $msg = '<div class="alert alert-warning">All fields are required, including the student image.</div>';
    } else {
        // Sanitize inputs
        $stu_name = htmlspecialchars($_REQUEST['stu_name']);
        $stu_email = filter_var($_REQUEST['stu_email'], FILTER_SANITIZE_EMAIL);
        $stu_pass = password_hash($_REQUEST['stu_pass'], PASSWORD_DEFAULT);
        $stu_occ = htmlspecialchars($_REQUEST['stu_occ']);
        
        // Handle file upload
        $file_name = $_FILES['stu_img']['name'];
        $file_tmp = $_FILES['stu_img']['tmp_name'];
        $file_size = $_FILES['stu_img']['size'];
        $file_error = $_FILES['stu_img']['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if(in_array($file_ext, $allowed)){
            if($file_error === 0){
                if($file_size <= 10097152){ // 2MB limit
                    // Create unique filename
                    $file_new_name = uniqid('', true) . '.' . $file_ext;
                    $file_destination = '../images/studentsImg/' . $file_new_name;
                    
                    if(move_uploaded_file($file_tmp, $file_destination)){
                        // Use prepared statement to prevent SQL injection
                        $sql = "INSERT INTO students (stu_name, stu_email, stu_pass, stu_occ, stu_img) 
                                VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssss", $stu_name, $stu_email, $stu_pass, $stu_occ, $file_destination);
                        
                        if($stmt->execute()){
                            $msg = '<div class="alert alert-success">Student added successfully!</div>';
                            // Clear form fields
                            $_POST = array();
                        } else {
                            $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
                        }
                    } else {
                        $msg = '<div class="alert alert-danger">There was an error uploading your image.</div>';
                    }
                } else {
                    $msg = '<div class="alert alert-danger">Image size is too large (max 10MB).</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">There was an error with your upload.</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Invalid file type. Only JPG, JPEG, PNG, and GIF images are allowed.</div>';
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
    <title>CodeKids | Add New Student</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --danger-color: #f72585;
            --success-color: #4cc9f0;
            
            --sidebar-width: 280px;
            --header-height: 70px;
            --card-radius: 12px;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        nav {
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: white;
            height: 100vh;
            position: fixed;
            transition: all var(--transition-speed) ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        nav ul {
            list-style: none;
        }
        
        nav ul li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 0;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        nav ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        nav ul li a.link-active {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        nav ul li a.logout {
            color: #ff6b6b;
        }
        
        nav ul li a.logout:hover {
            background: rgba(255, 107, 107, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo img {
            width: 40px;
            margin-right: 10px;
        }
        
        .sidebar-head {
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .sidebar-item {
            margin-left: 10px;
        }
        
        /* Main Content Styles */
        .student-add {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all var(--transition-speed) ease;
        }
        
        .student-add h1 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        /* Form Styles */
        .form-container {
            background: white;
            border-radius: var(--card-radius);
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-display {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        /* Image Preview */
        .image-preview {
            margin: 15px 0;
            display: none;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        /* Alert Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            flex: 1;
        }
        
        .btn-submit:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-close {
            background-color: #e5e7eb;
            color: var(--dark-color);
            flex: 1;
        }
        
        .btn-close:hover {
            background-color: #d1d5db;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            nav {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-head, .sidebar-item {
                display: none;
            }
            
            .logo {
                justify-content: center;
                padding: 20px 0;
            }
            
            nav ul li a {
                justify-content: center;
                padding: 15px 0;
            }
            
            nav ul li a i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .student-add {
                margin-left: 70px;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .student-add {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav>
            <ul>
                <li>
                    <a href="#" class="logo">
                        <img src="./pic/logo.png" alt="CodeKids Logo">
                        <span class="sidebar-head">CodeKids</span>
                    </a>
                </li>
                <li>
                    <a href="adminDashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="sidebar-item">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="courses.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="sidebar-item">Courses</span>
                    </a>
                </li>
                <li>
                    <a href="lesson.php">
                        <i class="fas fa-book-open"></i>
                        <span class="sidebar-item">Lessons</span>
                    </a>
                </li>
                <li>
                    <a href="students.php" class="link-active">
                        <i class="fas fa-users"></i>
                        <span class="sidebar-item">Students</span>
                    </a>
                </li>
                <li>
                    <a href="sellReport.php">
                        <i class="fas fa-chart-bar"></i>
                        <span class="sidebar-item">Sell Report</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-table"></i>
                        <span class="sidebar-item">Payment</span>
                    </a>
                </li>
                <li>
                    <a href="feedback.php">
                        <i class="fas fa-comment-alt"></i>
                        <span class="sidebar-item">Feedback</span>
                    </a>
                </li>
                <li>
                    <a href="adminChangePassword.php">
                        <i class="fas fa-key"></i>
                        <span class="sidebar-item">Password</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php" class="logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="sidebar-item">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="student-add">
            <h1>Add New Student</h1>
            
            <div class="form-container">
                <!-- Display Messages -->
                <?php if(!empty($msg)) echo $msg; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-display" for="stu_name">Student Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="stu_name"
                            name="stu_name"
                            value="<?php echo isset($_POST['stu_name']) ? htmlspecialchars($_POST['stu_name']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="stu_email">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="stu_email"
                            name="stu_email"
                            value="<?php echo isset($_POST['stu_email']) ? htmlspecialchars($_POST['stu_email']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="stu_pass">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            id="stu_pass"
                            name="stu_pass"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="stu_occ">Occupation</label>
                        <input
                            type="text"
                            class="form-control"
                            id="stu_occ"
                            name="stu_occ"
                            value="<?php echo isset($_POST['stu_occ']) ? htmlspecialchars($_POST['stu_occ']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="stu_img">Student Image</label>
                        <input
                            type="file"
                            class="form-control-file"
                            id="stu_img"
                            name="stu_img"
                            accept="image/*"
                            required
                        >
                        <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max 2MB)</small>
                        <div class="image-preview" id="imagePreview">
                            <img src="" alt="Image Preview">
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button
                            type="submit"
                            class="btn btn-submit"
                            id="courseSubmitBtn"
                            name="courseSubmitBtn"
                        >
                            <i class="fas fa-user-plus"></i> Add Student
                        </button>
                        <a href="students.php" class="btn btn-close">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Image preview before upload
        document.getElementById('stu_img').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const img = preview.querySelector('img');
            
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('stu_img');
            const maxSize = 10097152; // 2MB
            const file = fileInput.files[0];
            
            if(file && file.size > maxSize) {
                e.preventDefault();
                alert('Image size exceeds 10MB limit. Please choose a smaller image.');
            }
        });
    </script>
</body>
</html>