<?php
session_start();
include('../dbConnection.php');

// التحقق من تسجيل الدخول
if(!isset($_SESSION['is_login'])) {
    header("Location: ../loginSignUp.php");
    exit();
}

// التحقق من وجود معرف الاختبار
if(!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    header("Location: quiz.php");
    exit();
}
$stuLogEmail = $_SESSION['stuLogEmail'];
$quiz_id = (int)$_GET['quiz_id'];
$student_id = $_SESSION['stu_id'];
$sql = "SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$stuName = $row['stu_name'];
$student_id = $row['stu_id'];
// جلب معلومات الاختبار
$stmt = $conn->prepare("SELECT q.*, c.course_name 
                       FROM quizzes q
                       JOIN course c ON q.course_id = c.course_id
                       WHERE q.quiz_id = ? AND q.is_active = TRUE");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();

if(!$quiz) {
    header("Location: quiz.php");
    exit();
}

// التحقق من أن الطالب مسجل في الدورة
$enrollment_stmt = $conn->prepare("SELECT 1 FROM courseorder 
                                  WHERE course_id = ? AND stu_email = ?");
$enrollment_stmt->bind_param("is", $quiz['course_id'], $_SESSION['stuLogEmail']);
$enrollment_stmt->execute();
if($enrollment_stmt->get_result()->num_rows === 0) {
    header("Location: quiz.php");
    exit();
}

// جلب عدد الأسئلة
$question_count_stmt = $conn->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
$question_count_stmt->bind_param("i", $quiz_id);
$question_count_stmt->execute();
$question_count = $question_count_stmt->get_result()->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <title><?php echo htmlspecialchars($quiz['quiz_title']); ?> | CodeKids</title>
    <style>
        .quiz-start-container {
            padding: 4rem 0;
            background: var(--color-bg2);
            min-height: 100vh;
        }
        .quiz-info-card {
            background: var(--color-bg1);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.3);
        }
        .quiz-rules {
            margin: 2rem 0;
            padding: 1rem;
            background: rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }
        .quiz-rules ul {
            padding-left: 1.5rem;
        }
        .quiz-rules li {
            margin-bottom: 0.5rem;
        }
        .time-info {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="../index.php"><h3>🎮 CodeKids</h3></a>
            <ul class="nav_menu">
                <li><a href="../Student/studentProfile.php"><i class="uil uil-smile"></i> My Profile</a></li>
                <li><a href="../logout.php"><i class="uil uil-signout"></i> Logout</a></li>
            </ul>
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>

    <section class="quiz-start-container">
        <div class="container">
            <div class="quiz-info-card">
                <h2><?php echo htmlspecialchars($quiz['quiz_title']); 
              
                ?></h2>
                <p>Course: <?php echo htmlspecialchars($quiz['course_name']); ?></p>
                
                <div class="quiz-rules">
                    <h4>Quiz Rules:</h4>
                    <ul>
                        <li>This quiz contains <?php echo $question_count; ?> questions</li>
                        <li>You have <?php echo $quiz['time_limit']; ?> minutes to complete the quiz</li>
                        <li>You need to score at least <?php echo $quiz['passing_score']; ?>% to pass</li>
                        <li>Once started, the timer cannot be paused</li>
                        <li>Do not refresh the page during the quiz</li>
                    </ul>
                </div>
                
                <p><?php echo htmlspecialchars($quiz['quiz_description']); ?></p>
                
                <div class="time-info">
                    <span><i class="uil uil-clock"></i> Time Limit: <?php echo $quiz['time_limit']; ?> minutes</span>
                    <span><i class="uil uil-award"></i> Passing Score: <?php echo $quiz['passing_score']; ?>%</span>
                </div>
                
                <form action="take_quiz.php" method="POST" style="margin-top: 2rem;">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        Start Quiz Now
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer>
      <div class="container footer_container">
        <div class="footer_1">
          <a class="home_button" href="index.php"><h3>🎮 CodeKids</h3></a>
          <p>Making coding fun and accessible for everyone!  </p>
          <div class="mascot">
            <img src="images/mascot.png" alt="Codey the Robot" class="animate__animated animate__tada animate__infinite">
          </div>
        </div>

      </div>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>