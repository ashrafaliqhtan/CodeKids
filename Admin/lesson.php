<?php
if(!isset($_SESSION)){
  session_start();
}
include('../dbConnection.php');
if(isset($_SESSION['is_admin_login'])){
  $adminEmail = $_SESSION['adminLogEmail'] ?? 'Admin';
}else{
  echo "<script>location.href='../index.php';</script>";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CodeKids | Admin Lessons</title>
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
        .course-list {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all var(--transition-speed) ease;
        }
        
        /* Search Form */
        .status {
            background: white;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .status-subheader {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .status_form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .status-orderID input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 250px;
            font-size: 0.9rem;
        }
        
        .btn-View {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-View:hover {
            background: var(--secondary-color);
        }
        
        /* Course Details */
        .course-detail {
            font-size: 1.2rem;
            margin: 20px 0;
            color: var(--dark-color);
        }
        
        .course_det {
            font-weight: 500;
            color: var(--secondary-color);
            margin-right: 5px;
        }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 500;
            color: #495057;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            font-size: 0.9rem;
            color: #495057;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Action Buttons */
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-size: 1rem;
            margin-right: 5px;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .edit-btn {
            color: var(--primary-color);
        }
        
        .delete-btn {
            color: var(--danger-color);
        }
        
        /* Add Lesson Button */
        .add-lesson-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
            z-index: 10;
        }
        
        .add-lesson-btn:hover {
            transform: scale(1.1);
            background: var(--secondary-color);
            color: white;
        }
        
        .add-lesson-btn i {
            font-size: 1.5rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
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
            
            .course-list {
                margin-left: 70px;
            }
            
            .status_form {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .status-orderID input {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .course-list {
                padding: 15px;
            }
            
            th, td {
                padding: 10px 5px;
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
        <div class="course-list">
            <div class="status">
                <form method="GET">
                    <div class="status_form">
                        <label for="checkid" class="status-subheader">Enter Course ID:</label>
                        <div class="status-orderID">
                            <input
                                type="text"
                                name="checkid"
                                placeholder="Type the Course ID"
                                value="<?php echo isset($_REQUEST['checkid']) ? htmlspecialchars($_REQUEST['checkid']) : ''; ?>"
                            />
                        </div>
                        <div>
                            <input type="submit" class="btn-View" value="Search" />
                        </div>
                    </div>
                </form>
            </div>
            
            <?php 
            if(isset($_REQUEST['checkid'])) {
                $courseId = $_REQUEST['checkid'];
                $sql = "SELECT * FROM course WHERE course_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $courseId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $_SESSION['course_id'] = $row['course_id'];
                    $_SESSION['course_name'] = $row['course_name'];
                    ?>
                    
                    <h1 class="course-detail">
                        <span class="course_det">Course ID:</span> <?php echo htmlspecialchars($row['course_id']); ?>
                        <span class="course_det">Course Name:</span> <?php echo htmlspecialchars($row['course_name']); ?>
                    </h1>
                    
                    <?php
                    $sql = "SELECT * FROM lesson WHERE course_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $courseId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if($result->num_rows > 0) {
                        echo '<div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Lesson ID</th>
                                            <th>Lesson Name</th>
                                            <th>Lesson Link</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>
                                    <td>'.htmlspecialchars($row['lesson_id']).'</td>
                                    <td>'.htmlspecialchars($row['lesson_name']).'</td>
                                    <td><a href="'.htmlspecialchars($row['lesson_link']).'" target="_blank">View Lesson</a></td>
                                    <td>
                                        <form action="editLesson.php" method="POST" style="display:inline">
                                            <input type="hidden" name="id" value="'.$row["lesson_id"].'">
                                            <button type="submit" class="action-btn edit-btn" name="view" value="view">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </form>
                                        <form action="" method="POST" style="display:inline">
                                            <input type="hidden" name="id" value="'.$row["lesson_id"].'">
                                            <button type="submit" class="action-btn delete-btn" name="delete" value="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>';
                        }
                        
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="empty-state">
                                <i class="fas fa-book-open fa-3x" style="color: #dee2e6; margin-bottom: 15px;"></i>
                                <h3>No Lessons Found</h3>
                                <p>Add your first lesson for this course</p>
                              </div>';
                    }
                } else {
                    echo '<div class="empty-state">
                            <i class="fas fa-exclamation-circle fa-3x" style="color: #dee2e6; margin-bottom: 15px;"></i>
                            <h3>Course Not Found</h3>
                            <p>Please enter a valid Course ID</p>
                          </div>';
                }
                
                // Handle lesson deletion
                if(isset($_REQUEST['delete'])) {
                    $sql = "DELETE FROM lesson WHERE lesson_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $_REQUEST['id']);
                    if($stmt->execute()) {
                        echo '<script>window.location.href = "lesson.php?checkid='.$courseId.'";</script>';
                    } else {
                        echo '<script>alert("Unable to delete lesson");</script>';
                    }
                }
            } else {
                echo '<div class="empty-state">
                        <i class="fas fa-search fa-3x" style="color: #dee2e6; margin-bottom: 15px;"></i>
                        <h3>Search for Lessons</h3>
                        <p>Enter a Course ID to view its lessons</p>
                      </div>';
            }
            ?>
        </div>
        
        <?php if(isset($_SESSION['course_id'])): ?>
            <a class="add-lesson-btn" href="addLesson.php">
                <i class="fas fa-plus"></i>
            </a>
        <?php endif; ?>
    </div>
</body>
</html>