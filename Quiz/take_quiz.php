<?php
session_start();
include('../dbConnection.php');

// التحقق من تسجيل الدخول
if(!isset($_SESSION['is_login'])) {
    header("Location: ../loginSignUp.php");
    exit();
}

// التحقق من أن الطالب أرسل نموذج بدء الاختبار
if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quiz_id'])) {
    header("Location: quiz.php");
    exit();
}

$quiz_id = (int)$_POST['quiz_id'];
$student_id = $_SESSION['stu_id'];
$stuLogEmail = $_SESSION['stuLogEmail'];
$sql = "SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$stuName = $row['stu_name'];
$student_id = $row['stu_id'];

// جلب معلومات الاختبار
$quiz_stmt = $conn->prepare("SELECT q.*, c.course_name 
                           FROM quizzes q
                           JOIN course c ON q.course_id = c.course_id
                           WHERE q.quiz_id = ? AND q.is_active = TRUE");
$quiz_stmt->bind_param("i", $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();

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

// جلب أسئلة الاختبار
$questions_stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_id");
$questions_stmt->bind_param("i", $quiz_id);
$questions_stmt->execute();
$questions = $questions_stmt->get_result();

// تسجيل بدء الاختبار في الجلسة
$_SESSION['quiz_start_time'] = time();
$_SESSION['current_quiz'] = [
    'quiz_id' => $quiz_id,
    'time_limit' => $quiz['time_limit'] * 60, // تحويل إلى ثواني
    'questions' => []
];

// تخزين معرفات الأسئلة في الجلسة للإجابة عليها
while($question = $questions->fetch_assoc()) {
    $_SESSION['current_quiz']['questions'][$question['question_id']] = [
        'answered' => false,
        'is_correct' => false,
        'points_earned' => 0
    ];
}
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
        .quiz-container {
            padding: 2rem 0;
            background: var(--color-bg2);
            min-height: 100vh;
        }
        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .quiz-timer {
            background: var(--color-danger);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: bold;
        }
        .question-card {
            background: var(--color-bg1);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .question-text {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .options-list {
            list-style-type: none;
            padding: 0;
        }
        .option-item {
            margin-bottom: 0.8rem;
        }
        .option-label {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            background: rgba(255,255,255,0.1);
            cursor: pointer;
            transition: var(--transition);
        }
        .option-label:hover {
            background: rgba(255,255,255,0.2);
        }
        .option-input {
            margin-right: 1rem;
        }
        .quiz-progress {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-bg1);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -0.5rem 1rem rgba(0,0,0,0.2);
        }
        .progress-buttons {
            display: flex;
            gap: 1rem;
        }
        .question-nav {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg2);
            border: none;
            cursor: pointer;
        }
        .nav-btn.answered {
            background: var(--color-success);
            color: white;
        }
        .nav-btn.current {
            border: 2px solid var(--color-primary);
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="../index.php"><h3>🎮 CodeKids</h3></a>
            <div class="quiz-timer" id="quizTimer">
                Time Left: <?php echo gmdate("H:i:s", $quiz['time_limit'] * 60); ?>
            </div>
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>

    <section class="quiz-container">
        <div class="container">
          <br><br><br>
            <div class="quiz-header">
                <h2><?php echo htmlspecialchars($quiz['quiz_title']);
                
                
                
                ?></h2>
                <div>Question <span id="currentQuestion">1</span> of <?php echo $questions->num_rows; ?></div>
            </div>
            
            <form id="quizForm" action="submit_quiz.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                
                <?php
                // إعادة تعيين مؤشر الأسئلة
                $questions->data_seek(0);
                $question_num = 0;
                
                while($question = $questions->fetch_assoc()):
                    $question_num++;
                    // جلب خيارات السؤال
                    $options_stmt = $conn->prepare("SELECT * FROM quiz_options WHERE question_id = ?");
                    $options_stmt->bind_param("i", $question['question_id']);
                    $options_stmt->execute();
                    $options = $options_stmt->get_result();
                ?>
                <div class="question-card" id="question-<?php echo $question_num; ?>" <?php echo $question_num > 1 ? 'style="display:none;"' : ''; ?>>
                    <div class="question-text">
                        <?php echo $question_num.'. '.htmlspecialchars($question['question_text']); ?>
                        <small>(<?php echo $question['points']; ?> points)</small>
                    </div>
                    
                    <ul class="options-list">
                        <?php if($question['question_type'] === 'multiple_choice'): ?>
                            <?php while($option = $options->fetch_assoc()): ?>
                                <li class="option-item">
                                    <label class="option-label">
                                        <input type="radio" class="option-input" 
                                               name="question_<?php echo $question['question_id']; ?>" 
                                               value="<?php echo $option['option_id']; ?>">
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </label>
                                </li>
                            <?php endwhile; ?>
                        <?php elseif($question['question_type'] === 'true_false'): ?>
                            <li class="option-item">
                                <label class="option-label">
                                    <input type="radio" class="option-input" 
                                           name="question_<?php echo $question['question_id']; ?>" 
                                           value="true">
                                    True
                                </label>
                            </li>
                            <li class="option-item">
                                <label class="option-label">
                                    <input type="radio" class="option-input" 
                                           name="question_<?php echo $question['question_id']; ?>" 
                                           value="false">
                                    False
                                </label>
                            </li>
                        <?php else: ?>
                            <li class="option-item">
                                <label class="option-label">
                                    <textarea name="question_<?php echo $question['question_id']; ?>" 
                                              rows="3" style="width: 100%;"></textarea>
                                </label>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endwhile; ?>
            </form>
            
            <div class="quiz-progress">
                <div class="question-nav">
                    <?php for($i = 1; $i <= $question_num; $i++): ?>
                        <button type="button" class="nav-btn <?php echo $i === 1 ? 'current' : ''; ?>" 
                                onclick="showQuestion(<?php echo $i; ?>)">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                </div>
                
                <div class="progress-buttons">
                    <button type="button" class="btn btn-secondary" onclick="prevQuestion()">
                        <i class="uil uil-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextQuestion()">
                        Next <i class="uil uil-arrow-right"></i>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmSubmit()">
                        Submit Quiz
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        let currentQuestion = 1;
        const totalQuestions = <?php echo $question_num; ?>;
        let timeLeft = <?php echo $quiz['time_limit'] * 60; ?>;
        let timerInterval;
        
        // بدء المؤقت
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                
                if(timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Time is up! Your quiz will be submitted automatically.');
                    document.getElementById('quizForm').submit();
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            
            document.getElementById('quizTimer').textContent = 
                `Time Left: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        function showQuestion(questionNum) {
            if(questionNum < 1 || questionNum > totalQuestions) return;
            
            // إخفاء السؤال الحالي
            document.getElementById(`question-${currentQuestion}`).style.display = 'none';
            document.querySelector(`.nav-btn:nth-child(${currentQuestion})`).classList.remove('current');
            
            // إظهار السؤال الجديد
            currentQuestion = questionNum;
            document.getElementById(`question-${currentQuestion}`).style.display = 'block';
            document.querySelector(`.nav-btn:nth-child(${currentQuestion})`).classList.add('current');
            document.getElementById('currentQuestion').textContent = currentQuestion;
        }
        
        function nextQuestion() {
            if(currentQuestion < totalQuestions) {
                showQuestion(currentQuestion + 1);
            }
        }
        
        function prevQuestion() {
            if(currentQuestion > 1) {
                showQuestion(currentQuestion - 1);
            }
        }
        
        function confirmSubmit() {
            if(confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
                document.getElementById('quizForm').submit();
            }
        }
        
        // تحديث أزرار التنقل للإجابات
        function updateNavButtons() {
            const formData = new FormData(document.getElementById('quizForm'));
            
            for(let i = 1; i <= totalQuestions; i++) {
                const navBtn = document.querySelector(`.nav-btn:nth-child(${i})`);
                const questionId = document.querySelector(`#question-${i} input, #question-${i} textarea`)?.name.split('_')[1];
                
                if(formData.has(`question_${questionId}`)) {
                    navBtn.classList.add('answered');
                } else {
                    navBtn.classList.remove('answered');
                }
            }
        }
        
        // استمع لتغييرات النموذج
        document.getElementById('quizForm').addEventListener('change', updateNavButtons);
        
        // بدء المؤقت عند تحميل الصفحة
        window.addEventListener('load', startTimer);
        
        // منع تحديث الصفحة
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = 'Are you sure you want to leave? Your quiz progress will be lost.';
        });
    </script>
</body>
</html>