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

// Fetch courses for dropdown
$courses_stmt = $conn->prepare("SELECT course_id, course_name FROM course ORDER BY course_name");
$courses_stmt->execute();
$courses = $courses_stmt->get_result();

// Get quiz ID from URL
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
if(!$quiz_id) {
    $_SESSION['error'] = "Invalid quiz ID";
    header("Location: admin_quizzes.php");
    exit();
}

// Fetch quiz data
$quiz_stmt = $conn->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
$quiz_stmt->bind_param("i", $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();

if(!$quiz) {
    $_SESSION['error'] = "Quiz not found";
    header("Location: admin_quizzes.php");
    exit();
}

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
            // Update quiz
            $update_stmt = $conn->prepare("UPDATE quizzes 
                                         SET quiz_title = ?, 
                                             quiz_description = ?, 
                                             course_id = ?, 
                                             passing_score = ?, 
                                             time_limit = ?, 
                                             is_active = ?,
                                             updated_at = NOW()
                                         WHERE quiz_id = ?");
            $update_stmt->bind_param("ssiiiii", $quiz_title, $quiz_description, $course_id, 
                                   $passing_score, $time_limit, $is_active, $quiz_id);
            $update_stmt->execute();
            
            $_SESSION['message'] = "Quiz updated successfully!";
            header("Location: admin_quizzes.php");
            exit();
        } catch(Exception $e) {
            $_SESSION['error'] = "Error updating quiz: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz | CodeKids Admin</title>
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
            margin: 0;
            padding: 0;
            line-height: 1.6;
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
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .form-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-bg1);
        }

        .form-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--color-white);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-light);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--color-bg1);
            background: var(--color-bg);
            color: var(--color-white);
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
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
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-primary:hover {
            background: #5a52e0;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--color-danger);
            color: var(--color-white);
        }

        .btn-secondary:hover {
            background: #e04a3a;
            transform: translateY(-2px);
        }

        .btn-manage {
            background: var(--color-success);
            color: var(--color-white);
        }

        .btn-manage:hover {
            background: #00a67c;
            transform: translateY(-2px);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .success {
            background: rgba(0,191,142,0.2);
            color: var(--color-success);
            border-left: 4px solid var(--color-success);
        }

        .error {
            background: rgba(247,88,66,0.2);
            color: var(--color-danger);
            border-left: 4px solid var(--color-danger);
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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

        <div class="form-container">
            <div class="form-header">
                <h1>Edit Quiz: <?php echo htmlspecialchars($quiz['quiz_title']); ?></h1>
            </div>
            
            <form method="POST" action="edit_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="quiz_title">Quiz Title *</label>
                    <input type="text" id="quiz_title" name="quiz_title" 
                           value="<?php echo htmlspecialchars($quiz['quiz_title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="quiz_description">Description</label>
                    <textarea id="quiz_description" name="quiz_description"><?php 
                        echo htmlspecialchars($quiz['quiz_description']); 
                    ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="course_id">Course *</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select a course</option>
                        <?php while($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $course['course_id']; ?>" 
                                <?php echo $course['course_id'] == $quiz['course_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="passing_score">Passing Score (%) *</label>
                    <input type="number" id="passing_score" name="passing_score" 
                           min="1" max="100" value="<?php echo $quiz['passing_score']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="time_limit">Time Limit (minutes) *</label>
                    <input type="number" id="time_limit" name="time_limit" 
                           min="1" value="<?php echo $quiz['time_limit']; ?>" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" 
                               <?php echo $quiz['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active">Active (visible to students)</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <div>
                        <a href="admin_quizzes.php" class="btn btn-secondary">
                            <i class="uil uil-arrow-left"></i> Back to Quizzes
                        </a>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>" 
                           class="btn btn-manage">
                            <i class="uil uil-question-circle"></i> Manage Questions
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="uil uil-save"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>