<?php
session_start();
include('../dbConnection.php');

// Security check
if(!isset($_SESSION['is_admin_login']) || !$_SESSION['is_admin_login']) {
    header("Location: ../index.php");
    exit();
}

// CSRF token generation
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate quiz ID
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
if(!$quiz_id) {
    header("Location: admin_quiz.php");
    exit();
}

// Get quiz info
$quiz_stmt = $conn->prepare("SELECT q.*, c.course_name 
                           FROM quizzes q
                           JOIN course c ON q.course_id = c.course_id
                           WHERE q.quiz_id = ?");
$quiz_stmt->bind_param("i", $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();

if(!$quiz) {
    header("Location: admin_quiz.php");
    exit();
}

// Handle question deletion
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_question'])) {
    // Validate CSRF token
    if(!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
    
    $question_id = filter_input(INPUT_GET, 'delete_question', FILTER_VALIDATE_INT);
    if($question_id) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete options first
            $delete_options = $conn->prepare("DELETE FROM quiz_options WHERE question_id = ?");
            $delete_options->bind_param("i", $question_id);
            $delete_options->execute();
            
            // Delete question
            $delete_question = $conn->prepare("DELETE FROM quiz_questions WHERE question_id = ?");
            $delete_question->bind_param("i", $question_id);
            $delete_question->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Question deleted successfully";
        } catch(Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error deleting question: " . $e->getMessage();
        }
        
        header("Location: manage_questions.php?quiz_id=$quiz_id");
        exit();
    }
}

// Get questions for this quiz
$questions_stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_id");
$questions_stmt->bind_param("i", $quiz_id);
$questions_stmt->execute();
$questions = $questions_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions | CodeKids Admin</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-white: #fff;
            --color-light: rgba(255,255,255,0.7);
            --color-bg: #1f2641;
            --color-bg1: #2e3267;
            --color-bg2: #424890;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--color-bg1);
            color: var(--color-white);
        }

        .admin-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .quiz-header {
            background: var(--color-bg2);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .questions-list {
            background: var(--color-bg2);
            border-radius: 1rem;
            padding: 2rem;
        }

        .question-item {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--color-bg1);
        }

        .question-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--color-light);
        }

        .options-list {
            list-style-type: none;
            padding: 0;
            margin: 1rem 0;
        }

        .option-item {
            padding: 0.8rem;
            margin-bottom: 0.5rem;
            background: var(--color-bg1);
            border-radius: 0.5rem;
        }

        .option-item.correct {
            border-left: 4px solid var(--color-success);
        }

        .question-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.6rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-secondary {
            background: var(--color-success);
            color: var(--color-white);
        }

        .btn-danger {
            background: var(--color-danger);
            color: var(--color-white);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }

        .error {
            background: rgba(247,88,66,0.2);
            color: var(--color-danger);
        }

        .success {
            background: rgba(0,191,142,0.2);
            color: var(--color-success);
        }

        .no-questions {
            text-align: center;
            padding: 2rem;
            color: var(--color-light);
        }
    </style>
</head>
<body>
    <?php include('admin_nav.php'); ?>

    <div class="admin-container">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <div class="quiz-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1><?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
                <a href="admin_quiz.php" class="btn btn-danger">
                    Back to Quizzes
                </a>
            </div>
            <p>Course: <?php echo htmlspecialchars($quiz['course_name']); ?></p>
            <p>Passing Score: <?php echo $quiz['passing_score']; ?>% | Time Limit: <?php echo $quiz['time_limit']; ?> mins</p>
        </div>

        <div class="page-actions">
            <h2>Questions</h2>
            <a href="add_question.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                Add New Question
            </a>
        </div>

        <div class="questions-list">
            <?php if($questions->num_rows > 0): ?>
                <?php while($question = $questions->fetch_assoc()): 
                    // Get options for this question
                    $options_stmt = $conn->prepare("SELECT * FROM quiz_options WHERE question_id = ?");
                    $options_stmt->bind_param("i", $question['question_id']);
                    $options_stmt->execute();
                    $options = $options_stmt->get_result();
                ?>
                <div class="question-item">
                    <div class="question-meta">
                        <span>Type: <?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?></span>
                        <span><?php echo $question['points']; ?> points</span>
                    </div>
                    
                    <h3><?php echo htmlspecialchars($question['question_text']); ?></h3>
                    
                    <?php if($options->num_rows > 0): ?>
                        <ul class="options-list">
                            <?php while($option = $options->fetch_assoc()): ?>
                                <li class="option-item <?php echo $option['is_correct'] ? 'correct' : ''; ?>">
                                    <?php if($question['question_type'] === 'multiple_choice'): ?>
                                        <input type="radio" <?php echo $option['is_correct'] ? 'checked' : ''; ?> disabled>
                                    <?php elseif($question['question_type'] === 'true_false'): ?>
                                        <input type="radio" <?php echo $option['is_correct'] ? 'checked' : ''; ?> disabled>
                                    <?php endif; ?>
                                    
                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                    
                                    <?php if($option['is_correct']): ?>
                                        <span style="margin-left: auto; color: var(--color-success);">
                                            <i class="uil uil-check-circle"></i> Correct
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <div class="question-actions">
                        <a href="edit_question.php?question_id=<?php echo $question['question_id']; ?>" class="btn btn-primary">
                            <i class="uil uil-edit"></i> Edit
                        </a>
                        <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>&delete_question=<?php echo $question['question_id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this question?');">
                            <i class="uil uil-trash-alt"></i> Delete
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-questions">
                    <p>No questions added yet for this quiz.</p>
                    <a href="add_question.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                        Add First Question
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>