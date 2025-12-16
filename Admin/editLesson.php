<?php
// Start session if not already started
if(!isset($_SESSION)){
    session_start();
}

include('../dbConnection.php');

// Redirect if not logged in as admin
if(!isset($_SESSION['is_admin_login'])){
    header("Location: ../index.php");
    exit();
}

$adminEmail = $_SESSION['adminLogEmail'] ?? 'Admin';
$msg = '';
$row = [];

// Get lesson data if viewing
if(isset($_REQUEST['view'])){
    $lesson_id = (int)$_REQUEST['id'];
    $sql = "SELECT * FROM lesson WHERE lesson_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// Process form submission
if(isset($_REQUEST['lessonUpdateBtn'])){
    // Validate required fields
    if(empty($_REQUEST['lesson_id']) || empty($_REQUEST['lesson_name']) || 
       empty($_REQUEST['lesson_desc']) || empty($_REQUEST['course_id']) || 
       empty($_REQUEST['course_name'])){
        
        $msg = '<div class="alert alert-warning">All fields are required.</div>';
    } else {
        // Sanitize inputs
        $lesson_id = (int)$_REQUEST['lesson_id'];
        $lesson_name = htmlspecialchars($_REQUEST['lesson_name']);
        $lesson_desc = htmlspecialchars($_REQUEST['lesson_desc']);
        $course_id = (int)$_REQUEST['course_id'];
        $course_name = htmlspecialchars($_REQUEST['course_name']);
        
        // Handle file upload if new file provided
        if(!empty($_FILES['lesson_link']['name'])){
            $file_name = $_FILES['lesson_link']['name'];
            $file_tmp = $_FILES['lesson_link']['tmp_name'];
            $file_size = $_FILES['lesson_link']['size'];
            $file_error = $_FILES['lesson_link']['error'];
            
            // Get file extension
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Allowed extensions
            $allowed = ['mp4', 'webm', 'ogg'];
            
            if(in_array($file_ext, $allowed)){
                if($file_error === 0){
                    if($file_size <= 524288000){ // 500MB limit
                        // Create unique filename
                        $file_new_name = uniqid('', true) . '.' . $file_ext;
                        $file_destination = '../lessonVId/' . $file_new_name;
                        
                        if(move_uploaded_file($file_tmp, $file_destination)){
                            $lesson_link = $file_destination;
                        } else {
                            $msg = '<div class="alert alert-danger">There was an error uploading your file.</div>';
                        }
                    } else {
                        $msg = '<div class="alert alert-danger">File size is too large (max 500MB).</div>';
                    }
                } else {
                    $msg = '<div class="alert alert-danger">There was an error with your upload.</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">Invalid file type. Only MP4, WebM, and OGG files are allowed.</div>';
            }
        } else {
            // Keep existing video if no new file uploaded
            $lesson_link = $row['lesson_link'] ?? '';
        }
        
        if(empty($msg)){
            // Use prepared statement to prevent SQL injection
            $sql = "UPDATE lesson SET 
                    lesson_name = ?, 
                    lesson_desc = ?, 
                    course_id = ?, 
                    course_name = ?, 
                    lesson_link = ?
                    WHERE lesson_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssissi", $lesson_name, $lesson_desc, $course_id, $course_name, $lesson_link, $lesson_id);
            
            if($stmt->execute()){
                $msg = '<div class="alert alert-success">Lesson updated successfully!</div>';
                // Refresh the data
                $sql = "SELECT * FROM lesson WHERE lesson_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $lesson_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
            } else {
                $msg = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
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
    <title>CodeKids | Update Lesson</title>
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
        .course-add {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all var(--transition-speed) ease;
        }
        
        .course-add h1 {
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
        
        .form-control[readonly] {
            background-color: #f8f9fa;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-control-file {
            display: block;
            width: 100%;
            padding: 10px 0;
        }
        
        /* Video Preview */
        .video-preview {
            margin: 15px 0;
            max-width: 100%;
        }
        
        .video-preview video {
            max-width: 100%;
            border-radius: 5px;
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
            
            .course-add {
                margin-left: 70px;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .course-add {
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
                    <a href="lesson.php" class="link-active">
                        <i class="fas fa-book-open"></i>
                        <span class="sidebar-item">Lessons</span>
                    </a>
                </li>
                <li>
                    <a href="students.php">
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
        <div class="course-add">
            <h1>Update Lesson</h1>
            
            <div class="form-container">
                <!-- Display Messages -->
                <?php if(!empty($msg)) echo $msg; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-display" for="lesson_id">Lesson ID</label>
                        <input
                            type="text"
                            class="form-control"
                            id="lesson_id"
                            name="lesson_id"
                            value="<?php echo isset($row['lesson_id']) ? htmlspecialchars($row['lesson_id']) : ''; ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="course_id">Course ID</label>
                        <input
                            type="text"
                            class="form-control"
                            id="course_id"
                            name="course_id"
                            value="<?php echo isset($row['course_id']) ? htmlspecialchars($row['course_id']) : ''; ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="course_name">Course Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="course_name"
                            name="course_name"
                            value="<?php echo isset($row['course_name']) ? htmlspecialchars($row['course_name']) : ''; ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="lesson_name">Lesson Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="lesson_name"
                            name="lesson_name"
                            value="<?php echo isset($row['lesson_name']) ? htmlspecialchars($row['lesson_name']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-display" for="lesson_desc">Lesson Description</label>
                        <textarea
                            class="form-control"
                            id="lesson_desc"
                            name="lesson_desc"
                            required
                        ><?php echo isset($row['lesson_desc']) ? htmlspecialchars($row['lesson_desc']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Current Video Preview -->
                    <?php if(isset($row['lesson_link']) && !empty($row['lesson_link'])): ?>
                    <div class="form-group">
                        <label class="form-display">Current Video</label>
                        <div class="video-preview">
                            <video controls src="<?php echo htmlspecialchars($row['lesson_link']); ?>"></video>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-display" for="lesson_link">Update Video File (Optional)</label>
                        <input
                            type="file"
                            class="form-control-file"
                            id="lesson_link"
                            name="lesson_link"
                            accept="video/mp4,video/webm,video/ogg"
                        >
                        <small class="text-muted">Accepted formats: MP4, WebM, OGG (Max 500MB)</small>
                        <div class="video-preview" id="newVideoPreview" style="display:none;">
                            <video controls></video>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button
                            type="submit"
                            class="btn btn-submit"
                            id="lessonUpdateBtn"
                            name="lessonUpdateBtn"
                        >
                            <i class="fas fa-save"></i> Update Lesson
                        </button>
                        <a href="lesson.php" class="btn btn-close">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview new video before upload
        document.getElementById('lesson_link').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('newVideoPreview');
            const video = preview.querySelector('video');
            
            if(file) {
                const fileURL = URL.createObjectURL(file);
                video.src = fileURL;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('lesson_link');
            const maxSize = 524288000; // 500MB
            const file = fileInput.files[0];
            
            if(file && file.size > maxSize) {
                e.preventDefault();
                alert('File size exceeds 500MB limit. Please choose a smaller file.');
            }
        });
    </script>
</body>
</html>