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

// Fetch courses
$courses_stmt = $conn->prepare("SELECT course_id, course_name FROM course ORDER BY course_name");
$courses_stmt->execute();
$courses = $courses_stmt->get_result();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
    
    // Validate and sanitize inputs
    $quiz_title = filter_input(INPUT_POST, 'quiz_title', FILTER_SANITIZE_STRING);
    $quiz_description = filter_input(INPUT_POST, 'quiz_description', FILTER_SANITIZE_STRING);
    $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
    $passing_score = filter_input(INPUT_POST, 'passing_score', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 100]
    ]);
    $time_limit = filter_input(INPUT_POST, 'time_limit', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate all inputs
    if(!$quiz_title || !$course_id || !$passing_score || !$time_limit) {
        $_SESSION['error'] = "Please fill all required fields with valid data";
    } else {
        try {
            // Insert new quiz
            $insert_stmt = $conn->prepare("INSERT INTO quizzes 
                                         (quiz_title, quiz_description, course_id, passing_score, time_limit, is_active) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssiiii", $quiz_title, $quiz_description, $course_id, $passing_score, $time_limit, $is_active);
            $insert_stmt->execute();
            
            $quiz_id = $conn->insert_id;
            $_SESSION['message'] = "Quiz added successfully! Add questions now.";
            header("Location: manage_questions.php?quiz_id=$quiz_id");
            exit();
        } catch(Exception $e) {
            $_SESSION['error'] = "Error adding quiz: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Quiz | CodeKids Admin</title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--color-bg2);
            border-radius: 1rem;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border-radius: 0.5rem;
            border: none;
            background: var(--color-bg1);
            color: var(--color-white);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 500;
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

        <div class="form-container">
            <h1>Add New Quiz</h1>
            
            <form method="POST" action="add_quiz.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="quiz_title">Quiz Title *</label>
                    <input type="text" id="quiz_title" name="quiz_title" required>
                </div>
                
                <div class="form-group">
                    <label for="quiz_description">Description</label>
                    <textarea id="quiz_description" name="quiz_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="course_id">Course *</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select a course</option>
                        <?php while($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="passing_score">Passing Score (%) *</label>
                    <input type="number" id="passing_score" name="passing_score" 
                           min="1" max="100" value="70" required>
                </div>
                
                <div class="form-group">
                    <label for="time_limit">Time Limit (minutes) *</label>
                    <input type="number" id="time_limit" name="time_limit" 
                           min="1" value="30" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        <label for="is_active">Active (visible to students)</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="admin_quizzes.php" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Save & Add Questions
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>