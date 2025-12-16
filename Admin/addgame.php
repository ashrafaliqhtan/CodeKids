<?php
if(!isset($_SESSION)){
    session_start();
}
include('../dbConnection.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not admin
if(!isset($_SESSION['is_admin_login'])){
    header("Location: ../index.php");
    exit();
}

$adminEmail = $_SESSION['adminLogEmail'] ?? 'Admin';
$msg = '';

// Step handling
$step = 1;
if(isset($_GET['course_id']) && !isset($_GET['lesson_id'])) {
    $step = 2;
    $course_id = (int)$_GET['course_id'];
} 
elseif(isset($_GET['lesson_id'])) {
    $step = 3;
    $lesson_id = (int)$_GET['lesson_id'];
}

// Get courses list
$courses = [];
$sql = "SELECT course_id, course_name FROM course ORDER BY course_name";
$result = $conn->query($sql);
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $courses[] = $row;
    }
}

// Get lessons for selected course
if($step >= 2) {
    $course_id = (int)$_GET['course_id'];
    $sql = "SELECT lesson_id, lesson_name FROM lesson WHERE course_id = ? ORDER BY lesson_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lessons = [];
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $lessons[] = $row;
        }
    }
    
    // Get course name
    $sql = "SELECT course_name FROM course WHERE course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
}

// Get lesson details if step 3
if($step == 3) {
    $lesson_id = (int)$_GET['lesson_id'];
    $sql = "SELECT l.lesson_id, l.lesson_name, c.course_id, c.course_name 
            FROM lesson l JOIN course c ON l.course_id = c.course_id 
            WHERE l.lesson_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
}

// Process form submission (step 3)
if(isset($_REQUEST['gameSubmitBtn'])){
    // Validate required fields
    if(empty($_REQUEST['lesson_id']) || empty($_REQUEST['lesson_name'])){
        $msg = '<div class="alert alert-warning">Lesson ID and name are required.</div>';
    } else {
        // Sanitize inputs
        $lesson_id = (int)$_REQUEST['lesson_id'];
        $lesson_name = htmlspecialchars($_REQUEST['lesson_name']);
        $passing_score = (int)($_REQUEST['passing_score'] ?? 70);
        
        // Process questions
        $questions = [
            'true_false' => [],
            'multiple_choice' => [],
            'multi_select' => []
        ];
        
        // Process True/False questions
        if(isset($_REQUEST['tf_question'])){
            foreach($_REQUEST['tf_question'] as $index => $question){
                $questions['true_false'][] = [
                    'question' => htmlspecialchars($question),
                    'correctAnswer' => $_REQUEST['tf_correct'][$index] === 'true',
                    'hint1' => htmlspecialchars($_REQUEST['tf_hint1'][$index] ?? ''),
                    'hint2' => htmlspecialchars($_REQUEST['tf_hint2'][$index] ?? '')
                ];
            }
        }
        
        // Process Multiple Choice questions
        if(isset($_REQUEST['mc_question'])){
            foreach($_REQUEST['mc_question'] as $index => $question){
                $questions['multiple_choice'][] = [
                    'question' => htmlspecialchars($question),
                    'options' => array_map('htmlspecialchars', $_REQUEST['mc_options'][$index]),
                    'correctIndex' => (int)$_REQUEST['mc_correct'][$index],
                    'hint1' => htmlspecialchars($_REQUEST['mc_hint1'][$index] ?? ''),
                    'hint2' => htmlspecialchars($_REQUEST['mc_hint2'][$index] ?? '')
                ];
            }
        }
        
        // Process Multi-Select questions
        if(isset($_REQUEST['ms_question'])){
            foreach($_REQUEST['ms_question'] as $index => $question){
                $questions['multi_select'][] = [
                    'question' => htmlspecialchars($question),
                    'options' => array_map('htmlspecialchars', $_REQUEST['ms_options'][$index]),
                    'correctIndices' => array_map('intval', $_REQUEST['ms_correct'][$index] ?? []),
                    'hint1' => htmlspecialchars($_REQUEST['ms_hint1'][$index] ?? ''),
                    'hint2' => htmlspecialchars($_REQUEST['ms_hint2'][$index] ?? '')
                ];
            }
        }
        
        // Validate at least one question exists
        $total_questions = count($questions['true_false']) + 
                          count($questions['multiple_choice']) + 
                          count($questions['multi_select']);
        
        if($total_questions === 0){
            $msg = '<div class="alert alert-danger">At least one question is required.</div>';
        } else {
            // Convert to JSON
            $questions_json = json_encode($questions);
            
            // Insert into database
            $sql = "INSERT INTO lesson_games (lesson_id, questions, passing_score) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $lesson_id, $questions_json, $passing_score);
            
            if($stmt->execute()){
                $msg = '<div class="alert alert-success">Game created successfully!</div>';
                // Clear form if needed
                $_POST = array();
            } else {
                $msg = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $step == 1 ? 'Select Course' : ($step == 2 ? 'Select Lesson' : 'Create Game'); ?> | CodeKids</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  
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

    <style>

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #f9fafb;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4f46e5;
            color: white;
            border: none;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .question-section {
            border: 1px solid #e5e7eb;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }
        .course-card, .lesson-card {
            border: 1px solid #e5e7eb;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        .course-card:hover, .lesson-card:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 1rem;
            position: relative;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .step.active .step-number {
            background-color: #4f46e5;
            color: white;
        }
        .step.completed .step-number {
            background-color: #10b981;
            color: white;
        }
        .step-title {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .step.active .step-title {
            color: #4f46e5;
            font-weight: 500;
        }
        .step.completed .step-title {
            color: #10b981;
        }
        .step-connector {
            position: absolute;
            top: 20px;
            left: -50%;
            width: 100%;
            height: 2px;
            background-color: #e5e7eb;
            z-index: -1;
        }
        .step-connector.active {
            background-color: #4f46e5;
        }
        .step-connector.completed {
            background-color: #10b981;
        }
    </style>
        <style>
      
      /* Base Styles */
:root {
  --primary: #4f46e5;
  --primary-light: #6366f1;
  --primary-dark: #4338ca;
  --secondary: #10b981;
  --danger: #ef4444;
  --warning: #f59e0b;
  --light: #f9fafb;
  --dark: #1f2937;
  --gray: #6b7280;
  --gray-light: #e5e7eb;
}

body {
  font-family: 'Inter', sans-serif;
  background-color: #f8fafc;
  color: var(--dark);
  line-height: 1.6;
}

/* Layout */
.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

/* Cards */
.card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
              0 2px 4px -1px rgba(0, 0, 0, 0.06);
  padding: 2rem;
  margin-bottom: 2rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
              0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Typography */
h1, h2, h3, h4 {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  color: var(--dark);
}

h1 {
  font-size: 2rem;
  margin-bottom: 1.5rem;
}

h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

h3 {
  font-size: 1.25rem;
  margin-bottom: 0.75rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  gap: 0.5rem;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
}

.btn-outline {
  background-color: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-outline:hover {
  background-color: rgba(79, 70, 229, 0.1);
}

.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  background-color: #dc2626;
}

/* Forms */
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--dark);
}

.form-control {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-light);
  border-radius: 0.5rem;
  background-color: white;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

textarea.form-control {
  min-height: 120px;
  resize: vertical;
}

/* Alerts */
.alert {
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  border-left: 4px solid transparent;
}

.alert-success {
  background-color: #ecfdf5;
  border-color: var(--secondary);
  color: #065f46;
}

.alert-danger {
  background-color: #fef2f2;
  border-color: var(--danger);
  color: #b91c1c;
}

/* Question Sections */
.question-section {
  border: 1px solid var(--gray-light);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border-radius: 0.75rem;
  background-color: white;
  transition: all 0.2s;
}

.question-section:hover {
  border-color: var(--primary-light);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Course/Lesson Cards */
.course-card, .lesson-card {
  border: 1px solid var(--gray-light);
  padding: 1.5rem;
  margin-bottom: 1rem;
  border-radius: 0.75rem;
  transition: all 0.2s;
  cursor: pointer;
  background-color: white;
}

.course-card:hover, .lesson-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border-color: var(--primary-light);
}

.course-card h3, .lesson-card h3 {
  color: var(--primary);
  margin-bottom: 0.5rem;
}

/* Step Indicator */
.step-indicator {
  display: flex;
  justify-content: center;
  margin-bottom: 3rem;
  position: relative;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 2rem;
  position: relative;
  z-index: 1;
}

.step-number {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: var(--gray-light);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-bottom: 0.75rem;
  font-size: 1.25rem;
  transition: all 0.3s;
}

.step.active .step-number {
  background-color: var(--primary);
  color: white;
  transform: scale(1.1);
}

.step.completed .step-number {
  background-color: var(--secondary);
  color: white;
}

.step-title {
  font-size: 1rem;
  color: var(--gray);
  font-weight: 500;
  transition: all 0.3s;
}

.step.active .step-title {
  color: var(--primary);
  font-weight: 600;
}

.step.completed .step-title {
  color: var(--secondary);
}

.step-connector {
  position: absolute;
  top: 25px;
  left: 0;
  width: 100%;
  height: 3px;
  background-color: var(--gray-light);
  z-index: 0;
}

.step-connector.active {
  background-color: var(--primary);
}

.step-connector.completed {
  background-color: var(--secondary);
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade {
  animation: fadeIn 0.3s ease-out;
}

/* Responsive Grid */
.grid {
  display: grid;
  gap: 1.5rem;
}

.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-3 { grid-template-columns: repeat(3, 1fr); }

@media (max-width: 768px) {
  .grid-cols-2, .grid-cols-3 {
    grid-template-columns: repeat(1, 1fr);
  }
  
  .step {
    padding: 0 1rem;
  }
}

/* Utility Classes */
.flex {
  display: flex;
}

.items-center {
  align-items: center;
}

.justify-between {
  justify-content: space-between;
}

.gap-4 {
  gap: 1rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.mb-8 {
  margin-bottom: 2rem;
}

.p-4 {
  padding: 1rem;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.shadow-sm {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Custom Checkbox and Radio */
input[type="checkbox"], 
input[type="radio"] {
  width: 20px;
  height: 20px;
  accent-color: var(--primary);
}

/* Option Items */
.option-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.5rem;
  transition: background-color 0.2s;
}

.option-item:hover {
  background-color: rgba(79, 70, 229, 0.05);
}

/* Confetti Effect */
.confetti {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1000;
  opacity: 0;
  transition: opacity 0.5s;
}

.confetti-piece {
  position: absolute;
  width: 10px;
  height: 10px;
  background-color: var(--primary);
  opacity: 0;
}

/* Floating Action Button */
.fab {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.2s;
  z-index: 50;
}

.fab:hover {
  transform: translateY(-3px) scale(1.05);
  background-color: var(--primary-dark);
}

/* Tooltips */
.tooltip {
  position: relative;
}

.tooltip-text {
  visibility: hidden;
  width: 120px;
  background-color: var(--dark);
  color: white;
  text-align: center;
  border-radius: 6px;
  padding: 5px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

/* Transitions */
.transition-all {
  transition: all 0.2s;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  body {
    background-color: #1a202c;
    color: #f7fafc;
  }
  
  .card, .question-section, .course-card, .lesson-card {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #f7fafc;
  }
  
  .form-control {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #f7fafc;
  }
  
  .form-label {
    color: #f7fafc;
  }
}
      
    </style>
    
</head>
<body class="bg-gray-50">
    <!-- Sidebar and header would be same as your lesson.php -->
    
    <div class="main-content">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-gamepad text-indigo-600"></i>
                    <span>Create New Game</span>
                </h1>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'completed' : ''; echo $step == 1 ? ' active' : ''; ?>">
                    <div class="step-number">1</div>
                    <div class="step-title">Select Course</div>
                    <div class="step-connector <?php echo $step > 1 ? 'completed' : ''; ?>"></div>
                </div>
                <div class="step <?php echo $step >= 2 ? 'completed' : ''; echo $step == 2 ? ' active' : ''; ?>">
                    <div class="step-number">2</div>
                    <div class="step-title">Select Lesson</div>
                    <div class="step-connector <?php echo $step > 2 ? 'completed' : ''; ?>"></div>
                </div>
                <div class="step <?php echo $step >= 3 ? 'completed' : ''; echo $step == 3 ? ' active' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-title">Create Game</div>
                </div>
            </div>
            
            <?php if(!empty($msg)): ?>
                <div class="alert <?php 
                    echo strpos($msg, 'success') !== false ? 'alert-success' : 'alert-danger';
                ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <!-- Step 1: Select Course -->
            <?php if($step == 1): ?>
                <div class="card">
                    <h2 class="text-xl font-semibold mb-4">Select a Course</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach($courses as $course): ?>
                            <a href="addGame.php?course_id=<?php echo $course['course_id']; ?>" class="course-card">
                                <div class="font-medium text-lg"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <div class="text-sm text-gray-500 mt-1">Course ID: <?php echo $course['course_id']; ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Step 2: Select Lesson -->
            <?php if($step == 2): ?>
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Select a Lesson from <?php echo htmlspecialchars($course['course_name']); ?></h2>
                        <a href="addGame.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                    
                    <?php if(!empty($lessons)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($lessons as $lesson): ?>
                                <a href="addGame.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson['lesson_id']; ?>" class="lesson-card">
                                    <div class="font-medium"><?php echo htmlspecialchars($lesson['lesson_name']); ?></div>
                                    <div class="text-sm text-gray-500 mt-1">Lesson ID: <?php echo $lesson['lesson_id']; ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No lessons found for this course.</p>
                            <a href="addLesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary mt-4">
                                <i class="fas fa-plus"></i> Add New Lesson
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Step 3: Create Game -->
            <?php if($step == 3): ?>
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Create Game for: <?php echo htmlspecialchars($lesson['course_name']); ?> - <?php echo htmlspecialchars($lesson['lesson_name']); ?></h2>
                        <a href="addGame.php?course_id=<?php echo $lesson['course_id']; ?>" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Lessons
                        </a>
                    </div>
                    
                    <form action="" method="POST" class="grid grid-cols-1 gap-6">
                        <input type="hidden" name="lesson_id" value="<?php echo $lesson['lesson_id']; ?>">
                        
                        <div class="form-group">
                            <label class="form-label" for="lesson_name">Game Title*</label>
                            <input
                                type="text"
                                class="form-control"
                                id="lesson_name"
                                name="lesson_name"
                                value="<?php echo isset($_POST['lesson_name']) ? htmlspecialchars($_POST['lesson_name']) : htmlspecialchars($lesson['lesson_name'] . ' Game'); ?>"
                                required
                                placeholder="Lesson Quiz Game"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="passing_score">Passing Score (%)</label>
                            <input
                                type="number"
                                class="form-control"
                                id="passing_score"
                                name="passing_score"
                                value="<?php echo isset($_POST['passing_score']) ? (int)$_POST['passing_score'] : 70; ?>"
                                min="1"
                                max="100"
                            >
                        </div>
                        
                        <!-- True/False Questions Section -->
                        <div class="form-group">
                            <h3 class="text-xl font-semibold mb-4">True/False Questions</h3>
                            <div id="tfQuestionsContainer">
                                <?php if(isset($_POST['tf_question'])): ?>
                                    <?php foreach($_POST['tf_question'] as $index => $question): ?>
                                        <div class="question-section tf-question">
                                            <div class="form-group">
                                                <label class="form-label">Question</label>
                                                <input type="text" class="form-control" name="tf_question[]" 
                                                       value="<?php echo htmlspecialchars($question); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Correct Answer</label>
                                                <select class="form-control" name="tf_correct[]" required>
                                                    <option value="true" <?php if($_POST['tf_correct'][$index] === 'true') echo 'selected'; ?>>True</option>
                                                    <option value="false" <?php if($_POST['tf_correct'][$index] === 'false') echo 'selected'; ?>>False</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 1</label>
                                                <input type="text" class="form-control" name="tf_hint1[]" 
                                                       value="<?php echo htmlspecialchars($_POST['tf_hint1'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 2</label>
                                                <input type="text" class="form-control" name="tf_hint2[]" 
                                                       value="<?php echo htmlspecialchars($_POST['tf_hint2'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline remove-question" data-type="tf">
                                                <i class="fas fa-trash"></i> Remove Question
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="addTfQuestion" class="btn btn-outline">
                                <i class="fas fa-plus"></i> Add True/False Question
                            </button>
                        </div>
                        
                        <!-- Multiple Choice Questions Section -->
                        <div class="form-group">
                            <h3 class="text-xl font-semibold mb-4">Multiple Choice Questions</h3>
                            <div id="mcQuestionsContainer">
                                <?php if(isset($_POST['mc_question'])): ?>
                                    <?php foreach($_POST['mc_question'] as $index => $question): ?>
                                        <div class="question-section mc-question">
                                            <div class="form-group">
                                                <label class="form-label">Question</label>
                                                <input type="text" class="form-control" name="mc_question[]" 
                                                       value="<?php echo htmlspecialchars($question); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Options (4 required)</label>
                                                <?php for($i = 0; $i < 4; $i++): ?>
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <input type="text" class="form-control" 
                                                               name="mc_options[<?php echo $index; ?>][]" 
                                                               value="<?php echo htmlspecialchars($_POST['mc_options'][$index][$i] ?? ''); ?>" required>
                                                        <input type="radio" name="mc_correct[<?php echo $index; ?>]" 
                                                               value="<?php echo $i; ?>" 
                                                               <?php if((int)$_POST['mc_correct'][$index] === $i) echo 'checked'; ?> required>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 1</label>
                                                <input type="text" class="form-control" name="mc_hint1[]" 
                                                       value="<?php echo htmlspecialchars($_POST['mc_hint1'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 2</label>
                                                <input type="text" class="form-control" name="mc_hint2[]" 
                                                       value="<?php echo htmlspecialchars($_POST['mc_hint2'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline remove-question" data-type="mc">
                                                <i class="fas fa-trash"></i> Remove Question
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="addMcQuestion" class="btn btn-outline">
                                <i class="fas fa-plus"></i> Add Multiple Choice Question
                            </button>
                        </div>
                        
                        <!-- Multi-Select Questions Section -->
                        <div class="form-group">
                            <h3 class="text-xl font-semibold mb-4">Multi-Select Questions</h3>
                            <div id="msQuestionsContainer">
                                <?php if(isset($_POST['ms_question'])): ?>
                                    <?php foreach($_POST['ms_question'] as $index => $question): ?>
                                        <div class="question-section ms-question">
                                            <div class="form-group">
                                                <label class="form-label">Question</label>
                                                <input type="text" class="form-control" name="ms_question[]" 
                                                       value="<?php echo htmlspecialchars($question); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Options (Select all that apply)</label>
                                                <?php for($i = 0; $i < 4; $i++): ?>
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <input type="text" class="form-control" 
                                                               name="ms_options[<?php echo $index; ?>][]" 
                                                               value="<?php echo htmlspecialchars($_POST['ms_options'][$index][$i] ?? ''); ?>" required>
                                                        <input type="checkbox" name="ms_correct[<?php echo $index; ?>][]" 
                                                               value="<?php echo $i; ?>" 
                                                               <?php if(isset($_POST['ms_correct'][$index]) && in_array($i, $_POST['ms_correct'][$index])) echo 'checked'; ?>>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 1</label>
                                                <input type="text" class="form-control" name="ms_hint1[]" 
                                                       value="<?php echo htmlspecialchars($_POST['ms_hint1'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="form-label">Hint 2</label>
                                                <input type="text" class="form-control" name="ms_hint2[]" 
                                                       value="<?php echo htmlspecialchars($_POST['ms_hint2'][$index] ?? ''); ?>">
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline remove-question" data-type="ms">
                                                <i class="fas fa-trash"></i> Remove Question
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" id="addMsQuestion" class="btn btn-outline">
                                <i class="fas fa-plus"></i> Add Multi-Select Question
                            </button>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-group flex justify-end gap-4 pt-4 border-t border-gray-100">
                            <a href="addGame.php?course_id=<?php echo $lesson['course_id']; ?>" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="gameSubmitBtn"
                                name="gameSubmitBtn"
                            >
                                <i class="fas fa-save"></i> Create Game
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Question templates
        const tfTemplate = `
            <div class="question-section tf-question">
                <div class="form-group">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" name="tf_question[]" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-control" name="tf_correct[]" required>
                        <option value="true">True</option>
                        <option value="false">False</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 1</label>
                    <input type="text" class="form-control" name="tf_hint1[]">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 2</label>
                    <input type="text" class="form-control" name="tf_hint2[]">
                </div>
                
                <button type="button" class="btn btn-outline remove-question" data-type="tf">
                    <i class="fas fa-trash"></i> Remove Question
                </button>
            </div>
        `;
        
        const mcTemplate = `
            <div class="question-section mc-question">
                <div class="form-group">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" name="mc_question[]" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Options (4 required)</label>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="mc_options[${document.querySelectorAll('.mc-question').length}][]" required>
                        <input type="radio" name="mc_correct[${document.querySelectorAll('.mc-question').length}]" value="0" required>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="mc_options[${document.querySelectorAll('.mc-question').length}][]" required>
                        <input type="radio" name="mc_correct[${document.querySelectorAll('.mc-question').length}]" value="1">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="mc_options[${document.querySelectorAll('.mc-question').length}][]" required>
                        <input type="radio" name="mc_correct[${document.querySelectorAll('.mc-question').length}]" value="2">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="mc_options[${document.querySelectorAll('.mc-question').length}][]" required>
                        <input type="radio" name="mc_correct[${document.querySelectorAll('.mc-question').length}]" value="3">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 1</label>
                    <input type="text" class="form-control" name="mc_hint1[]">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 2</label>
                    <input type="text" class="form-control" name="mc_hint2[]">
                </div>
                
                <button type="button" class="btn btn-outline remove-question" data-type="mc">
                    <i class="fas fa-trash"></i> Remove Question
                </button>
            </div>
        `;
        
        const msTemplate = `
            <div class="question-section ms-question">
                <div class="form-group">
                    <label class="form-label">Question</label>
                    <input type="text" class="form-control" name="ms_question[]" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Options (Select all that apply)</label>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="ms_options[${document.querySelectorAll('.ms-question').length}][]" required>
                        <input type="checkbox" name="ms_correct[${document.querySelectorAll('.ms-question').length}][]" value="0">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="ms_options[${document.querySelectorAll('.ms-question').length}][]" required>
                        <input type="checkbox" name="ms_correct[${document.querySelectorAll('.ms-question').length}][]" value="1">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="ms_options[${document.querySelectorAll('.ms-question').length}][]" required>
                        <input type="checkbox" name="ms_correct[${document.querySelectorAll('.ms-question').length}][]" value="2">
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" class="form-control" name="ms_options[${document.querySelectorAll('.ms-question').length}][]" required>
                        <input type="checkbox" name="ms_correct[${document.querySelectorAll('.ms-question').length}][]" value="3">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 1</label>
                    <input type="text" class="form-control" name="ms_hint1[]">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hint 2</label>
                    <input type="text" class="form-control" name="ms_hint2[]">
                </div>
                
                <button type="button" class="btn btn-outline remove-question" data-type="ms">
                    <i class="fas fa-trash"></i> Remove Question
                </button>
            </div>
        `;
        
        // Add question event listeners
        document.getElementById('addTfQuestion')?.addEventListener('click', function() {
            const container = document.getElementById('tfQuestionsContainer');
            const div = document.createElement('div');
            div.innerHTML = tfTemplate;
            container.appendChild(div);
        });
        
        document.getElementById('addMcQuestion')?.addEventListener('click', function() {
            const container = document.getElementById('mcQuestionsContainer');
            const div = document.createElement('div');
            div.innerHTML = mcTemplate;
            container.appendChild(div);
        });
        
        document.getElementById('addMsQuestion')?.addEventListener('click', function() {
            const container = document.getElementById('msQuestionsContainer');
            const div = document.createElement('div');
            div.innerHTML = msTemplate;
            container.appendChild(div);
        });
        
        // Remove question event delegation
        document.addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-question')) {
                const questionType = e.target.getAttribute('data-type');
                const questionSection = e.target.closest('.question-section');
                
                if(questionSection) {
                    questionSection.remove();
                    
                    // Reindex remaining questions if needed
                    if(questionType === 'mc' || questionType === 'ms') {
                        const containers = {
                            'mc': document.getElementById('mcQuestionsContainer'),
                            'ms': document.getElementById('msQuestionsContainer')
                        };
                        
                        const questions = containers[questionType].querySelectorAll('.question-section');
                        questions.forEach((question, index) => {
                            // Update all the array indexes in the names
                            const inputs = question.querySelectorAll('input, select');
                            inputs.forEach(input => {
                                const name = input.getAttribute('name');
                                if(name) {
                                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                                }
                            });
                        });
                    }
                }
            }
        });
        
        // Form validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            // Check at least one question exists
            const totalQuestions = document.querySelectorAll('.question-section').length;
            if(totalQuestions === 0) {
                e.preventDefault();
                alert('Please add at least one question');
                return;
            }
            
            // Validate multi-select questions have at least one correct answer
            const msQuestions = document.querySelectorAll('.ms-question');
            for(let i = 0; i < msQuestions.length; i++) {
                const checkedBoxes = msQuestions[i].querySelectorAll('input[type="checkbox"]:checked');
                if(checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert(`Multi-select question ${i+1} must have at least one correct answer`);
                    return;
                }
            }
        });
    </script>
</body>
</html>