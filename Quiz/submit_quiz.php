<?php
session_start();
include('../dbConnection.php');

// 1. Ensure student is logged in
if (empty($_SESSION['is_login'])) {
    header('Location: ../loginSignUp.php');
    exit;
}

// 2. Ensure quiz in session and POST
if (empty($_SESSION['current_quiz']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit;
}

$quiz_id     = (int)$_POST['quiz_id'];
$student_id  = (int)$_SESSION['stu_id'];
$session_quiz = $_SESSION['current_quiz'];

$stuLogEmail = $_SESSION['stuLogEmail'];
$sql = "SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$stuName = $row['stu_name'];
$student_id = (int)$row['stu_id'];





// 3. Validate quiz_id
if ($quiz_id !== (int)$session_quiz['quiz_id']) {
    header('Location: quiz.php');
    exit;
}

// 4. Compute time taken
$time_taken = time() - $_SESSION['quiz_start_time'];

// 5. Fetch quiz meta (e.g. passing_score)
$q = $conn->prepare('SELECT passing_score FROM quizzes WHERE quiz_id = ?');
$q->bind_param('i', $quiz_id);
$q->execute();
$quiz = $q->get_result()->fetch_assoc();
if (!$quiz) {
    header('Location: quiz.php');
    exit;
}

// 6. Fetch all questions
$q2 = $conn->prepare('SELECT question_id, question_type, points FROM quiz_questions WHERE quiz_id = ?');
$q2->bind_param('i', $quiz_id);
$q2->execute();
$questions = $q2->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    die('No questions found for this quiz.');
}

// 7. Calculate max possible points
$max_points = array_sum(array_column($questions, 'points'));

// 8. Begin transaction
$conn->begin_transaction();

try {
    // 9. Insert into quiz_results (score & passed will be updated later)
    $insRes = $conn->prepare(
        'INSERT INTO quiz_results
         (quiz_id, student_id, score, total_score, passed, time_taken)
         VALUES (?, ?, 0, ?, 0, ?)'
    );
    $insRes->bind_param('iiii', $quiz_id, $student_id, $max_points, $time_taken);
    $insRes->execute();
    $result_id = $conn->insert_id;

    // 10. Prepare statements for answer inserts and MCQ lookup
    $insAns = $conn->prepare(
        'INSERT INTO student_answers
         (result_id, question_id, option_id, answer_text, is_correct, points_earned)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $getCorrectOption = $conn->prepare(
        'SELECT option_id FROM quiz_options
         WHERE question_id = ? AND is_correct = 1'
    );

    $total_points_earned = 0;

    // 11. Loop through each question
    foreach ($questions as $q) {
        $qid    = $q['question_id'];
        $ptype  = $q['question_type'];
        $pts    = (int)$q['points'];
        $opt_id = null;
        $ans_txt = '';
        $is_corr = 0;
        $earned = 0;

        if ($ptype === 'multiple_choice') {
            // get correct option
            $getCorrectOption->bind_param('i', $qid);
            $getCorrectOption->execute();
            $corr = $getCorrectOption->get_result()->fetch_assoc();
            $correct_id = $corr['option_id'] ?? null;

            // student answer
            $opt_id = isset($_POST["question_$qid"]) ? (int)$_POST["question_$qid"] : null;
            $is_corr = ($opt_id === $correct_id) ? 1 : 0;
            $earned = $is_corr ? $pts : 0;
            $ans_txt = '';  // not used for MCQ

        } elseif ($ptype === 'true_false') {
            $submitted = $_POST["question_$qid"] ?? '';
            // map 'true'/'false' to option_id?
            // assume option_ids for TF: fetch both then match text
            $opt_q = $conn->prepare(
                'SELECT option_id, option_text FROM quiz_options WHERE question_id = ?'
            );
            $opt_q->bind_param('i', $qid);
            $opt_q->execute();
            $opts = $opt_q->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($opts as $o) {
                if (strcasecmp($o['option_text'], $submitted) === 0) {
                    $opt_id = $o['option_id'];
                    break;
                }
            }
            // find which was marked correct
            foreach ($opts as $o) {
                if ($o['is_correct'] ?? 0) {
                    $correct_id = $o['option_id'];
                    break;
                }
            }
            $is_corr = ($opt_id === $correct_id) ? 1 : 0;
            $earned = $is_corr ? $pts : 0;
            $ans_txt = '';

        } elseif ($ptype === 'short_answer') {
            $text = trim($_POST["question_$qid"] ?? '');
            $ans_txt = $text;
            // manual grading—always mark incorrect and 0 for now
            $is_corr = 0;
            $earned = 0;
        }

        $total_points_earned += $earned;

        // insert answer
        $insAns->bind_param(
            'iiisii',
            $result_id,
            $qid,
            $opt_id,
            $ans_txt,
            $is_corr,
            $earned
        );
        $insAns->execute();
    }

    // 12. Compute final percentage score and pass/fail
    $percent = ($max_points > 0)
        ? round(($total_points_earned / $max_points) * 100)
        : 0;
    $passed = ($percent >= (int)$quiz['passing_score']) ? 1 : 0;

    // 13. Update quiz_results with actual score & pass flag
    $upd = $conn->prepare(
        'UPDATE quiz_results
         SET score = ?, passed = ?
         WHERE result_id = ?'
    );
    $upd->bind_param('iii', $percent, $passed, $result_id);
    $upd->execute();

    $conn->commit();

    // 14. Clean up session and redirect
    unset($_SESSION['current_quiz'], $_SESSION['quiz_start_time']);
    header("Location: quiz_result.php?result_id={$result_id}");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die('An error occurred: ' . $e->getMessage());
}
