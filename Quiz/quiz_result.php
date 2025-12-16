<?php
session_start();
include('../dbConnection.php');

// 1. تأكد من تسجيل دخول الطالب
if (empty($_SESSION['is_login'])) {
    header("Location: ../loginSignUp.php");
    exit;
}

// 2. تأكد من وجود result_id صالح في GET
if (empty($_GET['result_id']) || !is_numeric($_GET['result_id'])) {
    header("Location: quiz.php");
    exit;
}

$result_id  = (int)$_GET['result_id'];
$student_id = (int)$_SESSION['stu_id'];
$stuLogEmail = $_SESSION['stuLogEmail'];
$sql = "SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$stuName = $row['stu_name'];
$student_id = (int)$row['stu_id'];

print($student_id);
$stmt = $conn->prepare("
    SELECT 
        qr.*, 
        q.quiz_title, 
        q.passing_score, 
        c.course_name 
    FROM quiz_results qr
    JOIN quizzes q   ON qr.quiz_id = q.quiz_id
    JOIN course c    ON q.course_id = c.course_id
    WHERE qr.result_id = ? 
      AND qr.student_id = ?
");
$stmt->bind_param("ii", $result_id, $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    header("Location: quiz.php");
    exit;
}

// 4. جلب إجابات الطالب مع بيانات السؤال
$stmt = $conn->prepare("
    SELECT 
        sa.*, 
        qq.question_text, 
        qq.question_type, 
        qq.points 
    FROM student_answers sa
    JOIN quiz_questions qq ON sa.question_id = qq.question_id
    WHERE sa.result_id = ?
    ORDER BY sa.answer_id
");
$stmt->bind_param("i", $result_id);
$stmt->execute();
$answers = $stmt->get_result();


$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نتائج الاختبار | CodeKids</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        /* (احتفظت بأنماطك الأصلية هنا) */
        .result-container { padding:4rem 0; background:var(--color-bg2); min-height:100vh; }
        .result-summary { background:var(--color-bg1); border-radius:1rem; padding:2rem; margin-bottom:2rem; text-align:center; }
        .result-icon { font-size:5rem; margin-bottom:1rem; }
        .passed { color:var(--color-success); }
        .failed { color:var(--color-danger); }
        .score-circle { width:150px; height:150px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:2.5rem; font-weight:bold; border:10px solid; }
        .passed-circle { border-color:var(--color-success); color:var(--color-success); }
        .failed-circle { border-color:var(--color-danger); color:var(--color-danger); }
        .answers-container { background:var(--color-bg1); border-radius:1rem; padding:2rem; }
        .answer-item { margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1); }
        .answer-item.correct { border-left:4px solid var(--color-success); padding-left:1rem; }
        .answer-item.incorrect { border-left:4px solid var(--color-danger); padding-left:1rem; }
        .answer-status { display:inline-block; padding:0.3rem 0.8rem; border-radius:0.5rem; font-size:0.8rem; font-weight:bold; margin-bottom:0.5rem; }
        .status-correct { background:var(--color-success); color:#fff; }
        .status-incorrect { background:var(--color-danger); color:#fff; }
        .correct-answer { margin-top:0.5rem; font-size:0.9rem; color:var(--color-success); }
    </style>
</head>
<body>
    <?php include('student_nav.php'); ?>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="../index.php"><h3>🎮 CodeKids</h3></a>
            <ul class="nav_menu">
                <li><a href="../Student/studentProfile.php"><i class="uil uil-smile"></i> My Profile</a></li>
                <li><a href="../logout.php"><i class="uil uil-signout"></i> Logout</a></li>
                <li><a href="quiz.php"><i class="uil uil-question-circle"></i> Quizzes</a></li>
            </ul>
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>
    <section class="result-container">
        <div class="container">
            <div class="result-summary">
                <div class="result-icon">
                    <?php if ($result['passed']): 
                    

                    
                    ?>
                        <i class="uil uil-check-circle passed"></i>
                    <?php else: ?>
                        <i class="uil uil-times-circle failed"></i>
                    <?php endif; ?>
                </div>

                <div class="score-circle <?= $result['passed'] ? 'passed-circle' : 'failed-circle' ?>">
                    <?= $result['score'] ?>%
                </div>

                <h2><?= htmlspecialchars($result['quiz_title']) ?></h2>
                <p>المقرر: <?= htmlspecialchars($result['course_name']) ?></p>

                <?php if ($result['passed']): ?>
                    <p class="passed"><i class="uil uil-check"></i> مبروك! لقد نجحت في هذا الاختبار.</p>
                <?php else: ?>
                    <p class="failed"><i class="uil uil-exclamation-triangle"></i> لم تنجح هذه المرة. حاول مرة أخرى!</p>
                <?php endif; ?>

                <div class="result-meta" style="margin-top:1.5rem;">
                    <p>درجة النجاح المطلوبة: <?= $result['passing_score'] ?>%</p>
                    <p>الوقت المستغرق: <?= gmdate("H:i:s", $result['time_taken']) ?></p>
                    <p>تاريخ الإكمال: <?= date("j F Y, g:i a", strtotime($result['completed_at'])) ?></p>
                </div>

                <a href="quiz.php" class="btn btn-primary" style="margin-top:1.5rem;">
                    <i class="uil uil-arrow-left"></i> العودة للاختبارات
                </a>
            </div>

            <div class="answers-container">
                <h3>إجاباتك</h3>
                <p>راجع إجاباتك وتعلم من الأخطاء.</p>

                <?php
                $i = 0;
                while ($ans = $answers->fetch_assoc()):
                    $i++;
                ?>
                <div class="answer-item <?= $ans['is_correct'] ? 'correct' : 'incorrect' ?>">
                    <span class="answer-status <?= $ans['is_correct'] ? 'status-correct' : 'status-incorrect' ?>">
                        <?= $ans['is_correct'] ? 'صحيح' : 'خاطئ' ?>
                        (<?= $ans['points_earned'] ?>/<?= $ans['points'] ?>)
                    </span>

                    <h4>السؤال <?= $i ?></h4>
                    <p><?= htmlspecialchars($ans['question_text']) ?></p>

                    <p><strong>إجابتك:</strong>
                        <?php
                        if ($ans['question_type'] === 'short_answer') {
                            echo nl2br(htmlspecialchars($ans['answer_text']));
                        } else {
                            // for MCQ/TF, answer_text may be empty; fetch option_text
                            if ($ans['option_id']) {
                                $o = $conn->prepare("SELECT option_text FROM quiz_options WHERE option_id = ?");
                                $o->bind_param("i", $ans['option_id']);
                                $o->execute();
                                $opt = $o->get_result()->fetch_assoc();
                                echo htmlspecialchars($opt['option_text']);
                                $o->close();
                            } else {
                                echo '<em>لم تُجب</em>';
                            }
                        }
                        ?>
                    </p>

                    <?php if (!$ans['is_correct'] && $ans['question_type'] !== 'short_answer'): ?>
                        <div class="correct-answer">
                            <strong>الإجابة الصحيحة:</strong>
                            <?php
                            $c = $conn->prepare("
                                SELECT option_text
                                FROM quiz_options
                                WHERE question_id = ? AND is_correct = 1
                            ");
                            $c->bind_param("i", $ans['question_id']);
                            $c->execute();
                            $correct = $c->get_result()->fetch_assoc();
                            echo htmlspecialchars($correct['option_text']);
                            $c->close();
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php include('footer.php'); ?>
    <script src="../js/main.js"></script>
</body>
</html>
