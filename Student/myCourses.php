<?php
if(!isset($_SESSION)){
  session_start();
}
include_once('../dbConnection.php');

if(isset($_SESSION['is_login'])){
  $stuEmail=$_SESSION['stuLogEmail'];
}else{
  echo "<script>location.href='../index.php' </script>";
}

if(isset($_SESSION['is_login'])){
    $stuLogEmail=$_SESSION['stuLogEmail'];
  }
  if(isset($stuLogEmail)){
    $sql="SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
    $result=$conn->query($sql);
    $row=$result->fetch_assoc();
    $stu_img=$row['stu_img'];
    $stuId=$row['stu_id'];
    $stuName=$row['stu_name'];
    $stuOcc=$row['stu_occ'];
  }
  
// Check if viewing a specific course
$course_detail_view = isset($_GET['course_id']);
$current_course_id = $course_detail_view ? $_GET['course_id'] : null;

// بعد جزء تعريف $current_course_id
$course_detail_view = isset($_GET['course_id']);
$current_course_id = $course_detail_view ? $_GET['course_id'] : null;

// إضافة هذه الاستعلامات إذا كان عرض تفاصيل الدورة
if($course_detail_view) {
    // استعلام الكويزات العادية (غير الاختبارات)
    $quiz_sql = "SELECT q.*, 
                (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS question_count,
                (SELECT MAX(score) FROM quiz_results qr WHERE qr.quiz_id = q.quiz_id AND qr.student_id = $stuId) AS best_score
                FROM quizzes q 
                WHERE q.course_id = $current_course_id 
                AND q.is_active = TRUE
                AND q.quiz_title NOT LIKE '%Midterm Exam%'
                AND q.quiz_title NOT LIKE '%Final Exam%'";
    
    // استعلام الاختبارات النصفية والنهائية
    $exams_sql = "SELECT q.*, 
                 (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS question_count,
                 (SELECT MAX(score) FROM quiz_results qr WHERE qr.quiz_id = q.quiz_id AND qr.student_id = $stuId) AS best_score
                 FROM quizzes q 
                 WHERE q.course_id = $current_course_id 
                 AND q.is_active = TRUE
                 AND (q.quiz_title LIKE '%Midterm Exam%' OR q.quiz_title LIKE '%Final Exam%')
                 ORDER BY 
                   CASE 
                     WHEN q.quiz_title LIKE '%Midterm Exam 1%' THEN 1
                     WHEN q.quiz_title LIKE '%Midterm Exam 2%' THEN 2
                     WHEN q.quiz_title LIKE '%Final Exam%' THEN 3
                     ELSE 4
                   END";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeKids - My Learning</title>
    <meta name="description" content="CodeKids - Your learning dashboard">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="../images/logo.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --color-primary: #6C63FF;
            --color-primary-light: #8E85FF;
            --color-secondary: #FF6584;
            --color-accent: #FFC107;
            --color-success: #4CAF50;
            --color-warning: #FF9800;
            --color-danger: #F44336;
            --color-dark: #2D3748;
            --color-light: #F7FAFC;
            --color-bg1: #FFFFFF;
            --color-bg2: #F5F7FF;
            --transition: all 0.3s ease;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --border-radius: 12px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--color-bg2);
            color: var(--color-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        /* Navigation */
        nav {
            background: white;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }
        
        .home_button h3 {
            font-family: 'Fredoka One', cursive;
            color: var(--color-primary);
            font-size: 1.5rem;
            margin: 0;
        }
        
        .home_button h3:hover {
            transform: scale(1.05);
        }
        
        .nav_menu {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav_menu a {
            color: var(--color-dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav_menu a:hover, .nav_menu a.active {
            background: var(--color-primary-light);
            color: white;
        }
        
        .nav_menu a i {
            font-size: 1.1rem;
        }
        
        /* Learning Container */
        .learning-container {
            padding: 3rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .learning-container h2 {
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .learning-container p.subtitle {
            font-size: 1.1rem;
            color: var(--color-dark);
            margin-bottom: 2rem;
            opacity: 0.8;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-button i {
            margin-right: 0.5rem;
        }
        
        .course-title {
            font-size: 1.5rem;
            color: var(--color-primary);
            margin: 2.5rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-primary-light);
            display: inline-block;
        }
        
        /* Courses Grid */
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .course-card {
            background: var(--color-bg1);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--color-primary);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .course-card h4 {
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            color: var(--color-primary);
        }
        
        .course-card p.description {
            color: var(--color-dark);
            opacity: 0.8;
            margin-bottom: 1rem;
            flex-grow: 1;
        }
        
        .course-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }
        
        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .course-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-dark);
            opacity: 0.8;
        }
        
        .course-meta i {
            color: var(--color-primary);
        }
        
        .price-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0.5rem 0;
        }
        
        .original-price {
            text-decoration: line-through;
            color: var(--color-danger);
        }
        
        .current-price {
            font-weight: 600;
            color: var(--color-success);
        }
        
        /* Course Detail View */
        .course-detail-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .course-detail-header {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .course-detail-img {
            width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius);
        }
        
        .course-detail-info {
            flex: 1;
        }
        
        .course-detail-title {
            font-size: 1.8rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }
        
        .course-detail-author {
            font-size: 1.1rem;
            color: var(--color-dark);
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .course-detail-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            background: var(--color-bg2);
            padding: 1rem;
            border-radius: var(--border-radius);
            text-align: center;
            min-width: 100px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--color-dark);
            opacity: 0.8;
        }
        
        /* Lessons and Quizzes Sections */
        .content-section {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: var(--color-primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-primary-light);
        }
        
        .content-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .content-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        
        .content-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }
        
        .content-item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .content-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .content-title {
            font-weight: 500;
            color: var(--color-dark);
        }
        
        .content-duration {
            font-size: 0.9rem;
            color: var(--color-dark);
            opacity: 0.7;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--color-light);
            color: var(--color-primary);
            border: 1px solid var(--color-primary);
        }
        
        .btn-secondary:hover {
            background: var(--color-primary-light);
            color: white;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--color-primary-light);
            margin-bottom: 1rem;
        }
        
        /* Footer */
        footer {
            background: var(--color-dark);
            color: white;
            padding: 3rem 0 2rem;
            margin-top: 3rem;
        }
        
        .footer_container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .footer_1 {
            display: flex;
            flex-direction: column;
        }
        
        .footer_1 h3 {
            font-family: 'Fredoka One', cursive;
            color: white;
            margin-bottom: 1rem;
        }
        
        .footer_1 p {
            opacity: 0.8;
            margin-bottom: 1.5rem;
        }
        
        .mascot img {
            max-width: 120px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }
        
        /* Mobile menu */
        #open-menu-btn, #close-menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: var(--color-primary);
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav_menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: 100vh;
                background: white;
                flex-direction: column;
                padding: 4rem 2rem;
                box-shadow: -5px 0 15px rgba(0,0,0,0.1);
                transition: var(--transition);
                z-index: 999;
            }
            
            .nav_menu.active {
                right: 0;
            }
            
            #open-menu-btn, #close-menu-btn {
                display: block;
            }
            
            #close-menu-btn {
                position: absolute;
                top: 1.5rem;
                right: 1.5rem;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .course-detail-header {
                flex-direction: column;
            }
            
            .course-detail-img {
                width: 100%;
            }
            
            .container {
                padding: 0 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="../index.php"><h3>🎮 CodeKids</h3></a>
            <ul class="nav_menu">
                <li><a href="studentProfile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="myCourses.php" class="active"><i class="fas fa-book-open"></i> My Learning</a></li>
                <li><a href="../Quiz/quiz.php"><i class="fas fa-question-circle"></i> Quizzes</a></li>
                <li><a href="stuFeedback.php"><i class="fas fa-comment-alt"></i> Feedback</a></li>
                <li><a href="stuChangePassword.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <button id="open-menu-btn"><i class="fas fa-bars"></i></button>
            <button id="close-menu-btn"><i class="fas fa-times"></i></button>
        </div>
    </nav>

    <section class="learning-container">
        <div class="container">
            <h2>My Learning</h2>
            <p class="subtitle">Continue your learning journey</p>
            
            <?php if($course_detail_view): ?>
                <!-- Course Detail View -->
                <?php 
                $course_sql = "SELECT c.*, 
                               (SELECT COUNT(*) FROM lesson WHERE course_id = c.course_id) as lesson_count,
                               (SELECT COUNT(*) FROM quizzes WHERE course_id = c.course_id AND is_active = TRUE) as quiz_count
                               FROM course c 
                               WHERE c.course_id = $current_course_id";
                $course_result = $conn->query($course_sql);
                $course = $course_result->fetch_assoc();
                
                if($course): ?>
                    <a href="myCourses.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to All Courses
                    </a>
                    
                    <div class="course-detail-container">
                        <div class="course-detail-header">
                            <img src="<?php echo $course['course_img']; ?>" class="course-detail-img" alt="<?php echo $course['course_name']; ?>">
                            <div class="course-detail-info">
                                <h3 class="course-detail-title"><?php echo $course['course_name']; ?></h3>
                                <p class="course-detail-author">By <?php echo $course['course_author']; ?></p>
                                <p><?php echo $course['course_desc']; ?></p>
                                
                                <div class="course-detail-stats">
                                    <div class="stat-box">
                                        <div class="stat-number"><?php echo $course['lesson_count']; ?></div>
                                        <div class="stat-label">Lessons</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-number"><?php echo $course['quiz_count']; ?></div>
                                        <div class="stat-label">Quizzes</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-number"><?php echo $course['course_duration']; ?></div>
                                        <div class="stat-label">Hours</div>
                                    </div>
                                </div>
                                
                                <a href="watchcourse.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Start Learning
                                </a>
                            </div>
                        </div>
                        
<!-- Lessons Section -->
<div class="content-section">
    <h4 class="section-title">Lessons</h4>
    <ul class="content-list">
        <?php
        // Fixed query - removed ORDER BY lesson_order
        $lesson_sql = "SELECT * FROM lesson WHERE course_id = $current_course_id";
        $lesson_result = $conn->query($lesson_sql);
        
        if($lesson_result->num_rows > 0):
            while($lesson = $lesson_result->fetch_assoc()): ?>
                <li class="content-item">
                    <div class="content-item-info">
                        <div class="content-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div>
                            <div class="content-title"><?php echo $lesson['lesson_name']; ?></div>
                            <div class="content-duration"><?php echo $lesson['lesson_duration']; ?> min</div>
                        </div>
                    </div>
                    <a href="watchcourse.php?course_id=<?php echo $course['course_id']; ?>&lesson_id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-secondary">
                        Watch
                    </a>
                </li>
            <?php endwhile;
        else: ?>
            <li class="content-item">
                <div class="content-item-info">
                    <div class="content-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <div class="content-title">No lessons available yet</div>
                    </div>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</div>
                        
                        <!-- Quizzes Section -->
<!-- قسم الكويزات العادية -->
<!-- قسم الكويزات العادية -->
<div class="content-section">
    <h4 class="section-title">Quizzes</h4>
    <ul class="content-list">
        <?php
        if($course_detail_view) {
            $quiz_result = $conn->query($quiz_sql);
            
            if($quiz_result->num_rows > 0):
                while($quiz = $quiz_result->fetch_assoc()): 
                    $status_class = $quiz['best_score'] !== null ? 'status-completed' : 'status-not-attempted';
                    $status_text = $quiz['best_score'] !== null ? 'Completed (Best: '.$quiz['best_score'].'%)' : 'Not Attempted';
                ?>
                    <li class="content-item">
                        <div class="content-item-info">
                            <div class="content-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div>
                                <div class="content-title"><?php echo $quiz['quiz_title']; ?></div>
                                <div class="content-duration"><?php echo $quiz['time_limit']; ?> min • <?php echo $quiz['question_count']; ?> questions</div>
                                <span class="quiz-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </div>
                        </div>
                        <a href="../Quiz/start_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-secondary">
                            <?php echo $quiz['best_score'] !== null ? 'Retake Quiz' : 'Start Quiz'; ?>
                        </a>
                    </li>
                <?php endwhile;
            else: ?>
                <li class="content-item">
                    <div class="content-item-info">
                        <div class="content-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <div class="content-title">No quizzes available yet</div>
                        </div>
                    </div>
                </li>
            <?php endif;
        } ?>
    </ul>
</div>

<!-- قسم الاختبارات (النصفية والنهائية) -->
<div class="content-section">
    <h4 class="section-title">Exams</h4>
    <ul class="content-list">
        <?php
        if($course_detail_view) {
            $exams_result = $conn->query($exams_sql);
            
            if($exams_result->num_rows > 0):
                while($exam = $exams_result->fetch_assoc()): 
                    $status_class = $exam['best_score'] !== null ? 'status-completed' : 'status-not-attempted';
                    $status_text = $exam['best_score'] !== null ? 'Completed (Score: '.$exam['best_score'].'%)' : 'Not Attempted';
                    
                    // تحديد أيقونة ونوع الاختبار
                    if(strpos($exam['quiz_title'], 'Midterm Exam 1') !== false) {
                        $icon = 'fas fa-clipboard-check';
                        $exam_type = 'Midterm 1';
                        $bg_color = 'var(--color-primary-light)';
                        $exam_link = "../Quiz/start_quiz.php?quiz_id=".$exam['quiz_id'];
                    } elseif(strpos($exam['quiz_title'], 'Midterm Exam 2') !== false) {
                        $icon = 'fas fa-clipboard-check';
                        $exam_type = 'Midterm 2';
                        $bg_color = 'var(--color-primary)';
                        $exam_link = "../Quiz/start_quiz.php?quiz_id=".$exam['quiz_id'];
                    } else {
                        $icon = 'fas fa-star';
                        $exam_type = 'Final';
                        $bg_color = 'var(--color-accent)';
                        $exam_link = "finalExam.php?course_id=$current_course_id";
                    }
                ?>
                    <li class="content-item">
                        <div class="content-item-info">
                            <div class="content-icon" style="background: <?php echo $bg_color; ?>">
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            <div>
                                <div class="content-title"><?php echo $exam['quiz_title']; ?></div>
                                <div class="content-duration"><?php echo $exam['time_limit']; ?> min • <?php echo $exam['question_count']; ?> questions</div>
                                <div>
                                    <span class="exam-type-badge"><?php echo $exam_type; ?></span>
                                    <span class="quiz-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $exam_link; ?>" class="btn btn-secondary">
                            <?php echo $exam['best_score'] !== null ? 'View Results' : 'Take Exam'; ?>
                        </a>
                    </li>
                <?php endwhile;
            else: ?>
                <li class="content-item">
                    <div class="content-item-info">
                        <div class="content-icon" style="background: var(--color-light);">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <div class="content-title">No exams available yet</div>
                        </div>
                    </div>
                </li>
            <?php endif;
        } ?>
    </ul>
</div>
                        
                        
                        
                    </div>
                <?php else: ?>
                    <div class="empty-state animate__animated animate__fadeIn">
                        <i class="fas fa-exclamation-circle"></i>
                        <h3>Course Not Found</h3>
                        <p>The requested course could not be found.</p>
                        <a href="myCourses.php" class="btn btn-primary">Back to My Courses</a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- All Courses View -->
                <div class="courses-grid">
                    <?php 
                    if(isset($stuLogEmail)){
                        $sql = "SELECT co.order_id, c.course_id, c.course_name, c.course_duration, c.course_desc, c.course_img, c.course_author, c.course_original_price, c.course_price,
                               (SELECT COUNT(*) FROM lesson WHERE course_id = c.course_id) as lesson_count,
                               (SELECT COUNT(*) FROM quizzes WHERE course_id = c.course_id AND is_active = TRUE) as quiz_count
                               FROM courseorder AS co 
                               JOIN course AS c ON c.course_id = co.course_id 
                               WHERE co.stu_email = '$stuLogEmail'";
                        $result = $conn->query($sql);
                        
                        if($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) { ?>
                                <div class="course-card animate__animated animate__fadeInUp">
                                    <img src="<?php echo $row['course_img']; ?>" class="course-img" alt="<?php echo $row['course_name']; ?>">
                                    <h4><?php echo $row['course_name']; ?></h4>
                                    <p class="description"><?php echo $row['course_desc']; ?></p>
                                    
                                    <div class="course-meta">
                                        <span><i class="fas fa-play-circle"></i> <?php echo $row['lesson_count']; ?> lessons</span>
                                        <span><i class="fas fa-question-circle"></i> <?php echo $row['quiz_count']; ?> quizzes</span>
                                        <span><i class="fas fa-clock"></i> <?php echo $row['course_duration']; ?> hours</span>
                                    </div>
                                    
                                    <div class="price-container">
                                        <span class="original-price">₹<?php echo $row['course_original_price']; ?></span>
                                        <span class="current-price">₹<?php echo $row['course_price']; ?></span>
                                    </div>
                                    
                                    <a href="myCourses.php?course_id=<?php echo $row['course_id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </a>
                                </div>
                            <?php }
                        } else {
                            echo '<div class="empty-state animate__animated animate__fadeIn">
                                    <i class="fas fa-book-open"></i>
                                    <h3>No Enrolled Courses</h3>
                                    <p>You are not enrolled in any courses yet.</p>
                                    <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
                                  </div>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container footer_container">
            <div class="footer_1">
                <a class="home_button" href="../index.php"><h3>🎮 CodeKids</h3></a>
                <p>Making coding fun and accessible for kids of all ages!</p>
                <div class="mascot">
                    <img src="../images/mascot.png" alt="Codey the Robot" class="animate__animated animate__tada animate__infinite">
                </div>
            </div>
            <div class="footer_2">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="../courses.php">Courses</a></li>
                    <li><a href="../Quiz/quiz.php">Quizzes</a></li>
                    <li><a href="../Notes/notes.php">Notes</a></li>
                    <li><a href="../contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer_3">
                <h4>Connect With Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> CodeKids. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const openMenuBtn = document.getElementById('open-menu-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const navMenu = document.querySelector('.nav_menu');
        
        openMenuBtn.addEventListener('click', () => {
            navMenu.classList.add('active');
        });
        
        closeMenuBtn.addEventListener('click', () => {
            navMenu.classList.remove('active');
        });
        
        // Close menu when clicking on a link
        document.querySelectorAll('.nav_menu a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
        
        // Add animation to course cards as they come into view
        const courseCards = document.querySelectorAll('.course-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.classList.add('animate__fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        courseCards.forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>