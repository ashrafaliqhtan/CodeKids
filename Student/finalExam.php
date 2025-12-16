<?php
if(!isset($_SESSION)){
    session_start();
}
include_once('../dbConnection.php');

// التحقق من تسجيل الدخول
if(!isset($_SESSION['is_login'])){
    echo "<script>location.href='../index.php' </script>";
    exit();
}

$stuLogEmail = $_SESSION['stuLogEmail'];
$stuId = $_SESSION['stu_id'];



// الحصول على معلومات الطالب
$sql = "SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$stuName = $row['stu_name'];
$stuId = $row['stu_id'];

// الحصول على معلومات الدورة إذا كان هناك معرف دورة في URL
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if(!$course_id) {
    echo "<script>location.href='myCourses.php' </script>";
    exit();
}

// التحقق مما إذا كان الطالب مسجل في الدورة
$enrollment_check = "SELECT * FROM courseorder WHERE stu_email = '$stuLogEmail' AND course_id = $course_id";
$enrollment_result = $conn->query($enrollment_check);

if($enrollment_result->num_rows == 0) {
    echo "<script>alert('You are not enrolled in this course.'); location.href='myCourses.php' </script>";
    exit();
}

// الحصول على معلومات الدورة
$course_sql = "SELECT * FROM course WHERE course_id = $course_id";
$course_result = $conn->query($course_sql);
$course = $course_result->fetch_assoc();

// التحقق مما إذا كان هناك اختبار نهائي للدورة
$final_exam_sql = "SELECT * FROM quizzes WHERE course_id = $course_id AND quiz_title LIKE '%Final Exam%' LIMIT 1";
$final_exam_result = $conn->query($final_exam_sql);

if($final_exam_result->num_rows == 0) {
    echo "<script>alert('No final exam available for this course yet.'); location.href='myCourses.php?course_id=$course_id' </script>";
    exit();
}

$final_exam = $final_exam_result->fetch_assoc();
$quiz_id = $final_exam['quiz_id'];

// التحقق مما إذا كان الطالب قد أكمل الاختبار بالفعل


$exam_result_sql = "SELECT * FROM quiz_results WHERE quiz_id = $quiz_id AND student_id = $stuId";
$exam_result_result = $conn->query($exam_result_sql);
$has_completed_exam = $exam_result_result->num_rows > 0;
$exam_result = $has_completed_exam ? $exam_result_result->fetch_assoc() : null;

// الحصول على نتائج الكويزات الأخرى في هذه الدورة
$quizzes_sql = "SELECT q.quiz_id, q.quiz_title, q.passing_score, 
                (SELECT score FROM quiz_results qr WHERE qr.quiz_id = q.quiz_id AND qr.student_id = $stuId ORDER BY score DESC LIMIT 1) as best_score
                FROM quizzes q 
                WHERE q.course_id = $course_id AND q.quiz_id != $quiz_id AND q.is_active = TRUE";
$quizzes_result = $conn->query($quizzes_sql);
$quizzes = [];
$total_quizzes = 0;
$passed_quizzes = 0;
$average_quiz_score = 0;
$total_score = 0;

while($quiz = $quizzes_result->fetch_assoc()) {
    $quizzes[] = $quiz;
    $total_quizzes++;
    if($quiz['best_score'] !== null) {
        $total_score += $quiz['best_score'];
        if($quiz['best_score'] >= $quiz['passing_score']) {
            $passed_quizzes++;
        }
    }
}

$average_quiz_score = $total_quizzes > 0 ? round($total_score / $total_quizzes) : 0;

// معالجة تقديم النموذج إذا كان الاختبار لم يكتمل بعد
if(isset($_POST['start_exam']) && !$has_completed_exam) {
    header("Location: ../Quiz/start_quiz.php?quiz_id=$quiz_id");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Exam - <?php echo $course['course_name']; ?> | CodeKids</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: var(--color-primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            font-size: 1.1rem;
            color: var(--color-dark);
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
        
        .exam-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-light);
        }
        
        .exam-title {
            font-size: 1.8rem;
            color: var(--color-primary);
            margin: 0;
        }
        
        .exam-stats {
            display: flex;
            gap: 1rem;
        }
        
        .stat-badge {
            background: var(--color-bg2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .stat-badge.time {
            color: var(--color-warning);
            border: 1px solid var(--color-warning);
        }
        
        .stat-badge.questions {
            color: var(--color-primary);
            border: 1px solid var(--color-primary);
        }
        
        .stat-badge.passing {
            color: var(--color-success);
            border: 1px solid var(--color-success);
        }
        
        .exam-description {
            margin-bottom: 1.5rem;
            line-height: 1.7;
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
        
        .btn-success {
            background: var(--color-success);
            color: white;
        }
        
        .btn-success:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        
        .results-container {
            margin-top: 2rem;
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .result-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .result-card h3 {
            margin-top: 0;
            color: var(--color-primary);
            font-size: 1.3rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-primary-light);
        }
        
        .result-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 1rem 0;
            color: var(--color-dark);
        }
        
        .result-value.passed {
            color: var(--color-success);
        }
        
        .result-value.failed {
            color: var(--color-danger);
        }
        
        .quiz-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .quiz-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--color-light);
        }
        
        .quiz-item:last-child {
            border-bottom: none;
        }
        
        .quiz-name {
            font-weight: 500;
        }
        
        .quiz-score {
            font-weight: 600;
        }
        
        .quiz-score.passed {
            color: var(--color-success);
        }
        
        .quiz-score.failed {
            color: var(--color-danger);
        }
        
        .certificate-container {
            text-align: center;
            margin-top: 2rem;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        .certificate-container h2 {
            color: var(--color-primary);
            margin-bottom: 1rem;
        }
        
        .certificate-image {
            max-width: 100%;
            height: auto;
            border: 1px solid var(--color-light);
            margin: 1rem 0;
        }
        
        .certificate-download {
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .exam-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .exam-stats {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="myCourses.php?course_id=<?php echo $course_id; ?>" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Course
        </a>
        
        <div class="header">
            <h1>Final Exam</h1>
            <p>Test your knowledge of <?php echo $course['course_name']; ?></p>
        </div>
        
        <div class="exam-container animate__animated animate__fadeIn">
            <div class="exam-header">
                <h2 class="exam-title"><?php echo $final_exam['quiz_title']; 
              

                
                
                ?></h2>
                <div class="exam-stats">
                    <span class="stat-badge time"><i class="fas fa-clock"></i> <?php echo $final_exam['time_limit']; ?> min</span>
                    <?php
                    // الحصول على عدد الأسئلة
                    $questions_count_sql = "SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = $quiz_id";
                    $questions_count_result = $conn->query($questions_count_sql);
                    $questions_count = $questions_count_result->fetch_assoc()['count'];
                    ?>
                    <span class="stat-badge questions"><i class="fas fa-question-circle"></i> <?php echo $questions_count; ?> questions</span>
                    <span class="stat-badge passing"><i class="fas fa-trophy"></i> <?php echo $final_exam['passing_score']; ?>% to pass</span>
                </div>
            </div>
            
            <div class="exam-description">
                <?php echo $final_exam['quiz_description'] ? $final_exam['quiz_description'] : 'This is the final exam for the course. You must score at least '.$final_exam['passing_score'].'% to pass.'; ?>
            </div>
            
            <?php if($has_completed_exam): ?>
                <div class="results-container">
                    <h3>Your Exam Results</h3>
                    
                    <div class="results-grid">
                        <div class="result-card">
                            <h3>Final Exam Score</h3>
                            <div class="result-value <?php echo $exam_result['score'] >= $final_exam['passing_score'] ? 'passed' : 'failed'; ?>">
                                <?php echo $exam_result['score']; ?>%
                            </div>
                            <p>
                                <?php if($exam_result['score'] >= $final_exam['passing_score']): ?>
                                    <i class="fas fa-check-circle" style="color: var(--color-success);"></i> Congratulations! You passed the exam.
                                <?php else: ?>
                                    <i class="fas fa-times-circle" style="color: var(--color-danger);"></i> You didn't pass this time. Try again!
                                <?php endif; ?>
                            </p>
                            <p>Time taken: <?php echo gmdate("H:i:s", $exam_result['time_taken']); ?></p>
                        </div>
                        
                        <div class="result-card">
                            <h3>Quizzes Performance</h3>
                            <div class="result-value">
                                <?php echo $average_quiz_score; ?>%
                            </div>
                            <p>
                                Average score across <?php echo $total_quizzes; ?> quizzes
                            </p>
                            <p>
                                <?php echo $passed_quizzes; ?> of <?php echo $total_quizzes; ?> quizzes passed
                            </p>
                        </div>
                    </div>
                    
                    <div class="quiz-list-container">
                        <h3>Your Quiz Results</h3>
                        <ul class="quiz-list">
                            <?php foreach($quizzes as $quiz): ?>
                                <li class="quiz-item">
                                    <span class="quiz-name"><?php echo $quiz['quiz_title']; ?></span>
                                    <span class="quiz-score <?php echo $quiz['best_score'] >= $quiz['passing_score'] ? 'passed' : 'failed'; ?>">
                                        <?php echo $quiz['best_score'] !== null ? $quiz['best_score'].'%' : 'Not taken'; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <?php if($exam_result['score'] >= $final_exam['passing_score']): ?>
                        <div class="certificate-container animate__animated animate__fadeInUp">
                            <h2><i class="fas fa-certificate"></i> Course Completion Certificate</h2>
                            <p>Congratulations, <?php echo $stuName; ?>! You have successfully completed the <?php echo $course['course_name']; ?> course.</p>
                            
                            <!-- صورة الشهادة (يمكن استبدالها بشهادة حقيقية) -->
                            <img src="../images/certificate-placeholder.jpg" alt="Certificate of Completion" class="certificate-image">
                            
                            <div class="certificate-download">
                                <a href="generate_certificate.php?course_id=<?php echo $course_id; ?>&student_id=<?php echo $stuId; ?>" class="btn btn-success">
                                    <i class="fas fa-download"></i> Download Certificate
                                </a>
                                <a href="share_certificate.php?course_id=<?php echo $course_id; ?>&student_id=<?php echo $stuId; ?>" class="btn btn-secondary">
                                    <i class="fas fa-share-alt"></i> Share Certificate
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="exam-actions" style="margin-top: 2rem; text-align: center;">
                            <p>You can retake the exam to try to improve your score.</p>
                            <form method="post">
                                <button type="submit" name="start_exam" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Retake Exam
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="exam-actions" style="margin-top: 2rem; text-align: center;">
                    <p>This exam will test your knowledge of the entire course. Make sure you're ready before starting.</p>
                    <form method="post">
                        <button type="submit" name="start_exam" class="btn btn-primary">
                            <i class="fas fa-play"></i> Start Exam
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // يمكن إضافة أي تفاعلات JavaScript هنا إذا لزم الأمر
        });
    </script>
</body>
</html>