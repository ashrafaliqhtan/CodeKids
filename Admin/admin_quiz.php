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

// Handle quiz deletion
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_quiz'])) {
    // Validate CSRF token
    if(!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
    
    $quiz_id = filter_input(INPUT_GET, 'delete_quiz', FILTER_VALIDATE_INT);
    if($quiz_id) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete options first
            $delete_options = $conn->prepare("DELETE o FROM quiz_options o 
                                            JOIN quiz_questions q ON o.question_id = q.question_id 
                                            WHERE q.quiz_id = ?");
            $delete_options->bind_param("i", $quiz_id);
            $delete_options->execute();
            
            // Delete questions
            $delete_questions = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
            $delete_questions->bind_param("i", $quiz_id);
            $delete_questions->execute();
            
            // Delete results
            $delete_results = $conn->prepare("DELETE FROM quiz_results WHERE quiz_id = ?");
            $delete_results->bind_param("i", $quiz_id);
            $delete_results->execute();
            
            // Finally delete quiz
            $delete_quiz = $conn->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
            $delete_quiz->bind_param("i", $quiz_id);
            $delete_quiz->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Quiz deleted successfully";
        } catch(Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error deleting quiz: " . $e->getMessage();
        }
        
        header("Location: admin_quiz.php");
        exit();
    }
}

// Fetch quizzes with course information
$quizzes_stmt = $conn->prepare("SELECT q.*, c.course_name 
                               FROM quizzes q
                               JOIN course c ON q.course_id = c.course_id
                               ORDER BY q.created_at DESC");
$quizzes_stmt->execute();
$quizzes = $quizzes_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes | CodeKids Admin</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .table-container {
            background: var(--color-bg2);
            border-radius: 1rem;
            padding: 1.5rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-bg1);
        }

        th {
            background: var(--color-primary);
            color: var(--color-white);
        }

        tr:hover {
            background: rgba(255,255,255,0.05);
        }

        .status-active {
            color: var(--color-success);
        }

        .status-inactive {
            color: var(--color-danger);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
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

        .success {
            background: rgba(0,191,142,0.2);
            color: var(--color-success);
        }

        .error {
            background: rgba(247,88,66,0.2);
            color: var(--color-danger);
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('admin_nav.php'); ?>

    <div class="admin-container">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1>Manage Quizzes</h1>
            <a href="add_quiz.php" class="btn btn-primary">
                <i class="uil uil-plus"></i> Add New Quiz
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Course</th>
                        <th>Questions</th>
                        <th>Passing Score</th>
                        <th>Time Limit</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($quiz = $quizzes->fetch_assoc()): 
                        // Get question count
                        $question_count_stmt = $conn->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
                        $question_count_stmt->bind_param("i", $quiz['quiz_id']);
                        $question_count_stmt->execute();
                        $question_count = $question_count_stmt->get_result()->fetch_row()[0];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['quiz_title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['course_name']); ?></td>
                        <td><?php echo $question_count; ?></td>
                        <td><?php echo $quiz['passing_score']; ?>%</td>
                        <td><?php echo $quiz['time_limit']; ?> mins</td>
                        <td class="<?php echo $quiz['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $quiz['is_active'] ? 'Active' : 'Inactive'; ?>
                        </td>
                        <td><?php echo date("M j, Y", strtotime($quiz['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary">
                                    <i class="uil uil-edit"></i> Edit
                                </a>
                                <a href="manage_questions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-secondary">
                                    <i class="uil uil-question-circle"></i> Questions
                                </a>
                                <a href="admin_quiz.php?delete_quiz=<?php echo $quiz['quiz_id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this quiz? All related questions and results will also be deleted.');">
                                    <i class="uil uil-trash-alt"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>