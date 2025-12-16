<?php
session_start();
include('./dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    header("Location: loginSignUp.php");
    exit();
}

$student_id = $_SESSION['stu_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CodeKids - Interactive coding quizzes for young learners">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="images/logo.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css">
    
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
        
        /* Quiz Container */
        .quiz-container {
            padding: 3rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .quiz-container h2 {
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .quiz-container p.subtitle {
            font-size: 1.1rem;
            color: var(--color-dark);
            margin-bottom: 2rem;
            opacity: 0.8;
        }
        
        .course-title {
            font-size: 1.5rem;
            color: var(--color-primary);
            margin: 2.5rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-primary-light);
            display: inline-block;
        }
        
        /* Quiz Grid */
        .quizzes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .quiz-card {
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
        
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .quiz-card h4 {
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            color: var(--color-primary);
        }
        
        .quiz-card p.description {
            color: var(--color-dark);
            opacity: 0.8;
            margin-bottom: 1rem;
            flex-grow: 1;
        }
        
        .quiz-status {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .status-completed {
            background: var(--color-success);
            color: white;
        }
        
        .status-not-attempted {
            background: var(--color-warning);
            color: white;
        }
        
        .quiz-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .quiz-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--color-dark);
            opacity: 0.8;
        }
        
        .quiz-meta i {
            color: var(--color-primary);
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
            align-self: flex-start;
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
        
        /* Progress indicator */
        .progress-container {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--color-success);
            border-radius: 4px;
            transition: width 0.5s ease;
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
            
            .quizzes-grid {
                grid-template-columns: 1fr;
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
                <li><a href="../student/studentProfile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <li><a href="../Notes/notes.php"><i class="fas fa-book"></i> Notes</a></li>
                <li><a href="quiz.php" class="active"><i class="fas fa-question-circle"></i> Quizzes</a></li>
                <li><a href="../contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
            <button id="open-menu-btn"><i class="fas fa-bars"></i></button>
            <button id="close-menu-btn"><i class="fas fa-times"></i></button>
        </div>
    </nav>

    <section class="quiz-container">
        <div class="container">
            <h2>Interactive Quizzes</h2>
            <p class="subtitle">Test your knowledge and track your progress</p>
            
            <div class="quizzes-grid">
                <?php
                // Get enrolled courses for student
                $stmt = $conn->prepare("SELECT c.course_id, c.course_name 
                                      FROM courseorder co 
                                      JOIN course c ON co.course_id = c.course_id 
                                      WHERE co.stu_email = ?");
                $stmt->bind_param("s", $_SESSION['stuLogEmail']);
                $stmt->execute();
                $courses = $stmt->get_result();

                if($courses->num_rows > 0) {
                    while($course = $courses->fetch_assoc()) {
                        // Get quizzes for each course
                        $quiz_stmt = $conn->prepare("SELECT q.*, 
                                                   (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS question_count,
                                                   (SELECT MAX(score) FROM quiz_results qr WHERE qr.quiz_id = q.quiz_id AND qr.student_id = ?) AS best_score
                                                   FROM quizzes q 
                                                   WHERE q.course_id = ? AND q.is_active = TRUE");
                        $quiz_stmt->bind_param("ii", $student_id, $course['course_id']);
                        $quiz_stmt->execute();
                        $quizzes = $quiz_stmt->get_result();

                        if($quizzes->num_rows > 0) {
                            echo '<h3 class="course-title animate__animated animate__fadeIn">'.$course['course_name'].'</h3>';
                            
                            while($quiz = $quizzes->fetch_assoc()) {
                                $status_class = $quiz['best_score'] !== null ? 'status-completed' : 'status-not-attempted';
                                $status_text = $quiz['best_score'] !== null ? 'Completed (Best: '.$quiz['best_score'].'%)' : 'Not Attempted';
                                $progress_width = $quiz['best_score'] !== null ? $quiz['best_score'] : 0;
                                
                                echo '<div class="quiz-card animate__animated animate__fadeInUp">
                                        <h4>'.$quiz['quiz_title'].'</h4>
                                        <p class="description">'.$quiz['quiz_description'].'</p>
                                        <span class="quiz-status '.$status_class.'">'.$status_text.'</span>';
                                
                                if($quiz['best_score'] !== null) {
                                    echo '<div class="progress-container">
                                            <div class="progress-bar" style="width: '.$progress_width.'%"></div>
                                          </div>';
                                }
                                
                                echo '<div class="quiz-meta">
                                        <span><i class="fas fa-clock"></i> '.$quiz['time_limit'].' mins</span>
                                        <span><i class="fas fa-question"></i> '.$quiz['question_count'].' questions</span>
                                        <span><i class="fas fa-trophy"></i> Pass: '.$quiz['passing_score'].'%</span>
                                      </div>
                                      <a href="start_quiz.php?quiz_id='.$quiz['quiz_id'].'" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Start Quiz
                                      </a>
                                    </div>';
                            }
                        } else {
                            echo '<div class="empty-state animate__animated animate__fadeIn">
                                    <i class="fas fa-question-circle"></i>
                                    <h3>No quizzes available yet</h3>
                                    <p>There are no quizzes for '.$course['course_name'].' at this time.</p>
                                  </div>';
                        }
                    }
                } else {
                    echo '<div class="empty-state animate__animated animate__fadeIn">
                            <i class="fas fa-book-open"></i>
                            <h3>No Enrolled Courses</h3>
                            <p>You are not enrolled in any courses yet.</p>
                            <a href="./courses.php" class="btn btn-primary">Browse Courses</a>
                          </div>';
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
      <div class="container footer_container">
        <div class="footer_1">
          <a class="home_button" href="index.php"><h3>🎮 CodeKids</h3></a>
          <p>Making coding fun and accessible for kids of all ages! </p>
          <div class="mascot">
            <img src="images/mascot.png" alt="Codey the Robot" class="animate__animated animate__tada animate__infinite">
          </div>
        </div>
        <div class="footer_2">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="courses.php">Courses</a></li>
            <li><a href="quiz.php">Quizzes</a></li>
            <li><a href="Notes/notes.php">Notes</a></li>
            <li><a href="contact.php">Contact Us</a></li>
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
        
        // Add animation to quiz cards as they come into view
        const quizCards = document.querySelectorAll('.quiz-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    entry.target.classList.add('animate__fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        quizCards.forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>