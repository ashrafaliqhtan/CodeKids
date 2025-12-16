<?php
session_start();
include('../dbConnection.php');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Security: Admin login check ---
if (empty($_SESSION['is_admin_login']) || !$_SESSION['is_admin_login']) {
    header('Location: ../index.php');
    exit;
}

// --- CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Validate quiz_id ---
$quiz_id = filter_input(INPUT_GET, 'quiz_id', FILTER_VALIDATE_INT);
if (!$quiz_id) {
    header('Location: admin_quizzes.php');
    exit;
}

// --- Fetch quiz info ---
$quiz_stmt = $conn->prepare('SELECT quiz_title FROM quizzes WHERE quiz_id = ?');
$quiz_stmt->bind_param('i', $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();
if (!$quiz) {
    header('Location: admin_quizzes.php');
    exit;
}

// --- Initialize messages ---
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}
if (!isset($_SESSION['message'])) {
    $_SESSION['message'] = '';
}

// --- Handle form POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_question'])) {
    // CSRF check
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        // Sanitize inputs
        $question_text = trim($_POST['question_text'] ?? '');
        $question_type = $_POST['question_type'] ?? '';
        $points = filter_var($_POST['points'] ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($question_text === '' || !in_array($question_type, ['multiple_choice','true_false','short_answer']) || !$points) {
            $_SESSION['error'] = 'Please fill all required fields with valid data.';
        } else {
            try {
                $conn->begin_transaction();

                // Insert question
                $insert_q = $conn->prepare(
                    'INSERT INTO quiz_questions (quiz_id, question_text, question_type, points)
                     VALUES (?, ?, ?, ?)'
                );
                $insert_q->bind_param('issi', $quiz_id, $question_text, $question_type, $points);
                if (!$insert_q->execute()) {
                    throw new Exception($insert_q->error);
                }
                $question_id = $conn->insert_id;
                $insert_q->close();

                // Handle options
                if ($question_type === 'multiple_choice') {
                    $options = $_POST['options'] ?? [];
                    $correct_index = filter_var($_POST['correct_option'] ?? -1, FILTER_VALIDATE_INT);

                    if (count($options) < 2 || $correct_index < 0 || $correct_index >= count($options)) {
                        throw new Exception('Multiple choice requires at least 2 options and a valid correct answer.');
                    }

                    $opt_stmt = $conn->prepare(
                        'INSERT INTO quiz_options (question_id, option_text, is_correct)
                         VALUES (?, ?, ?)'
                    );
                    foreach ($options as $idx => $opt_text) {
                        $opt_text = trim($opt_text);
                        if ($opt_text === '') {
                            continue;
                        }
                        $is_correct = ($idx === $correct_index) ? 1 : 0;
                        $opt_stmt->bind_param('isi', $question_id, $opt_text, $is_correct);
                        if (!$opt_stmt->execute()) {
                            throw new Exception($opt_stmt->error);
                        }
                    }
                    $opt_stmt->close();

                } elseif ($question_type === 'true_false') {
                    $tf = $_POST['true_false_answer'] ?? '';
                    if (!in_array($tf, ['true','false'])) {
                        throw new Exception('Invalid True/False selection.');
                    }
                    $opt_stmt = $conn->prepare(
                        'INSERT INTO quiz_options (question_id, option_text, is_correct)
                         VALUES (?, ?, ?)'
                    );
                    // True
                    $opt_stmt->bind_param('isi', $question_id, $trueText = 'True', $is_true = ($tf==='true')?1:0);
                    $opt_stmt->execute();
                    // False
                    $opt_stmt->bind_param('isi', $question_id, $falseText = 'False', $is_false = ($tf==='false')?1:0);
                    $opt_stmt->execute();
                    $opt_stmt->close();
                }
                // short_answer has no options

                $conn->commit();
                $_SESSION['message'] = 'Question added successfully!';
                header("Location: manage_questions.php?quiz_id={$quiz_id}");
                exit;

            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = 'Error adding question: ' . $e->getMessage();
                // fall through to redisplay form with error
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Question | CodeKids Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-danger-light: #ff6b6b;
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
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.2);
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
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .question-type-section {
            display: none;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }

        .active-section {
            display: block;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .option-item input[type="text"] {
            flex: 1;
        }

        .option-item input[type="radio"] {
            width: auto;
        }

        .add-option {
            margin-top: 0.5rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
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
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--color-primary);
            color: var(--color-white);
        }

        .btn-primary:hover {
            background: #5a52d9;
        }

        .btn-secondary {
            background: var(--color-danger);
            color: var(--color-white);
        }

        .btn-secondary:hover {
            background: #d64a38;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-danger {
            background: var(--color-danger-light);
            color: var(--color-white);
            padding: 0.5rem;
            border-radius: 0.3rem;
        }

        .btn-danger:hover {
            background: #e05555;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            border-left: 4px solid;
        }

        .error {
            background: rgba(247,88,66,0.2);
            color: var(--color-danger);
            border-left-color: var(--color-danger);
        }

        .success {
            background: rgba(0,191,142,0.2);
            color: var(--color-success);
            border-left-color: var(--color-success);
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include('admin_nav.php'); ?>
    <div class="admin-container">
        <?php if ($_SESSION['error']): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php $_SESSION['error']=''; ?>
        <?php endif; ?>
        <?php if ($_SESSION['message']): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php $_SESSION['message']=''; ?>
        <?php endif; ?>

        <div class="form-container">
            <div class="header-container">
                <h1>Add New Question</h1>
                <a href="manage_questions.php?quiz_id=<?= $quiz_id ?>" class="btn btn-secondary">
                    <i class="uil uil-arrow-left"></i> Back
                </a>
            </div>
            <p><strong>Quiz:</strong> <?= htmlspecialchars($quiz['quiz_title']) ?></p>

            <form id="questionForm" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label>Question Text *</label>
                    <textarea name="question_text" required><?= htmlspecialchars($_POST['question_text'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Question Type *</label>
                    <select name="question_type" required onchange="toggleSections(this.value)">
                        <option value="">-- Select --</option>
                        <?php
                        $types = ['multiple_choice'=>'Multiple Choice','true_false'=>'True/False','short_answer'=>'Short Answer'];
                        foreach ($types as $val=>$label): ?>
                            <option value="<?= $val ?>"
                                <?= (($_POST['question_type'] ?? '') === $val)?'selected':'' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Points *</label>
                    <input type="number" name="points" min="1" value="<?= intval($_POST['points'] ?? 1) ?>" required>
                </div>

                <!-- Multiple Choice -->
                <div id="multiple_choice" class="question-type-section">
                    <h3>Options</h3>
                    <div id="options_container">
                        <?php
                        $opts = $_POST['options'] ?? ['', ''];
                        $correct = intval($_POST['correct_option'] ?? 0);
                        foreach ($opts as $i=>$opt): ?>
                            <div class="option-item">
                                <input type="radio" name="correct_option" value="<?= $i ?>"
                                    <?= ($i=== $correct)?'checked':'' ?> required>
                                <input type="text" name="options[]" value="<?= htmlspecialchars($opt) ?>" required>
                                <?php if ($i >= 2): ?>
                                    <button type="button" onclick="removeOption(this)">×</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addOption()">+ Add Option</button>
                </div>

                <!-- True/False -->
                <div id="true_false" class="question-type-section">
                    <h3>True / False</h3>
                    <label><input type="radio" name="true_false_answer" value="true"
                        <?= ($_POST['true_false_answer']==='true')?'checked':'' ?> required> True</label>
                    <label><input type="radio" name="true_false_answer" value="false"
                        <?= ($_POST['true_false_answer']==='false')?'checked':'' ?> required> False</label>
                </div>

                <!-- Short Answer -->
                <div id="short_answer" class="question-type-section">
                    <h3>Short Answer</h3>
                    <p>No additional options needed. Instructors grade manually.</p>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit_question" class="btn btn-primary">
                        <i class="uil uil-save"></i> Save Question
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
// Show/hide sections
function toggleSections(type) {
    ['multiple_choice','true_false','short_answer'].forEach(id=>{
        document.getElementById(id).style.display = (id===type)?'block':'none';
    });
}
document.addEventListener('DOMContentLoaded', ()=>{
    toggleSections('<?= $_POST['question_type'] ?? '' ?>');
});

// Dynamic option fields
function addOption() {
    const container = document.getElementById('options_container');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'option-item';
    div.innerHTML = `
        <input type="radio" name="correct_option" value="${idx}" required>
        <input type="text" name="options[]" required>
        <button type="button" onclick="removeOption(this)">×</button>
    `;
    container.appendChild(div);
}
function removeOption(btn) {
    btn.parentElement.remove();
    // re-index radios
    Array.from(document.querySelectorAll('#options_container .option-item')).forEach((div,i)=>{
        div.querySelector('input[type=radio]').value = i;
    });
}
</script>
</body>
</html>
