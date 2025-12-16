<?php
// Start session (same as your other pages)
if(!isset($_SESSION)){
  session_start();
}
include_once('../dbConnection.php');

if(isset($_SESSION['is_login'])){
  $stuEmail=$_SESSION['stuLogEmail'];
}else{
  echo "<script>location.href='../index.php' </script>";
}

if(isset($_SESSION['is_login'])){
    $stuLogEmail=$_SESSION['stuLogEmail'];
  }
  if(isset($stuLogEmail)){
    $sql="SELECT * FROM students WHERE stu_email = '$stuLogEmail'";
    $result=$conn->query($sql);
    $row=$result->fetch_assoc();
    $stu_img=$row['stu_img'];
    $stuId=$row['stu_id'];
    $stuName=$row['stu_name'];
    $stuOcc=$row['stu_occ'];
  }

$stuLogEmail = $_SESSION['stuLogEmail'];
$user_id = (int)$row['stu_id'];

// Function to get lesson by ID
function getLessonById($conn, $lesson_id) {
    $stmt = $conn->prepare("SELECT * FROM lesson WHERE lesson_id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to get course by ID
function getCourseById($conn, $course_id) {
    $stmt = $conn->prepare("SELECT * FROM course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to get lessons by course
function getLessonsByCourse($conn, $course_id) {
    $stmt = $conn->prepare("SELECT * FROM lesson WHERE course_id = ? ORDER BY lesson_order ASC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $lessons = [];
    while($row = $result->fetch_assoc()) {
        $lessons[] = $row;
    }
    return $lessons;
}

// Function to get game ID for lesson
function getLessonGameId($conn, $lesson_id) {
    $stmt = $conn->prepare("SELECT game_id FROM lesson_games WHERE lesson_id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0) ? $result->fetch_assoc()['game_id'] : null;
}

// Function to check if game is completed
function hasCompletedGame($conn, $user_id, $lesson_id) {
    $stmt = $conn->prepare("SELECT ugp.* FROM user_game_progress ugp 
                           JOIN lesson_games lg ON ugp.game_id = lg.game_id 
                           WHERE ugp.stu_id = ? AND lg.lesson_id = ?");
    $stmt->bind_param("ii", $user_id, $lesson_id);
    $stmt->execute();
    return ($stmt->get_result()->num_rows > 0);
}

// Get current lesson details
$current_lesson = [];
if (isset($_GET['lesson_id'])) {
    $lesson_id = (int)$_GET['lesson_id'];
    $current_lesson = getLessonById($conn, $lesson_id);
    
    if ($current_lesson && !empty($current_lesson['attachments'])) {
        $current_lesson['attachments'] = json_decode($current_lesson['attachments'], true);
    }
}

// Get course details
$course_id = isset($_GET['course_id']) 
    ? (int)$_GET['course_id'] 
    : (isset($current_lesson['course_id']) ? (int)$current_lesson['course_id'] : null);
$course = $course_id ? getCourseById($conn, $course_id) : [];

// Get all lessons for this course
$lessons = $course_id ? getLessonsByCourse($conn, $course_id) : [];

// Find current lesson index for navigation
$current_lesson_index = null;
if (!empty($current_lesson) && !empty($lessons)) {
    foreach ($lessons as $index => $lesson) {
        if ($lesson['lesson_id'] == $current_lesson['lesson_id']) {
            $current_lesson_index = $index;
            break;
        }
    }
}

// Check if user can access next lesson
$can_access_next = false;
if ($current_lesson_index !== null && $current_lesson_index < count($lessons) - 1) {
    $next_lesson_id = $lessons[$current_lesson_index + 1]['lesson_id'];
    $can_access_next = hasCompletedGame($conn, $user_id, $current_lesson['lesson_id']);
}

// Set secure headers (same as your other pages)
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; frame-src 'self'; connect-src 'self'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($current_lesson['lesson_name']) ? htmlspecialchars($current_lesson['lesson_name']) : 'Lesson'; ?> | CodeKids</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="../images/logo.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
    <!-- Video.js -->
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
    
    <!-- Highlight.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    

  <style>
        :root {
            --color-primary: #6C63FF;
            --color-primary-light: #8E85FF;
            --color-secondary: #FF6584;
            --color-accent: #FFC107;
            --color-dark: #2D3748;
            --color-light: #F7FAFC;
            --color-bg1: #FFFFFF;
            --color-bg2: #F5F7FF;
            --transition: all 0.3s ease;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --border-radius: 12px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--color-bg2);
            color: var(--color-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .nav {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav h2 {
            font-family: 'Fredoka One', cursive;
            color: var(--color-primary);
            margin: 0;
            font-size: 1.5rem;
        }
        
        .nav h2 a {
            color: inherit;
            text-decoration: none;
        }
        
        .nav h2 a:hover {
            color: var(--color-primary-light);
        }
        
        .myCourse {
            background: var(--color-primary);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .myCourse:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        #courseName {
            max-width: 1200px;
            margin: 2rem auto 0;
            padding: 0 2rem;
        }
        
        #courseName h2 {
            color: var(--color-primary);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .course-progress {
            background: var(--color-bg1);
            height: 8px;
            border-radius: 4px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--color-primary);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .player-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .video-js {
          margin: 0 0 0.5rem;
            padding: 1.5rem;
            width: 100%;
            height: 640px;
            background: #000;
        }
        
        .video-meta {
            padding: 1.5rem;
            background: white;
        }
        
        .video-meta h1 {
            margin: 0 0 0.5rem;
           color: var(--color-primary);
            
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem; 
            font-size: 3.0rem;
              background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            position: sticky;
            top: 80px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .video-meta .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--color-dark);
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .lesson {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            position: sticky;
            top: 80px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .lesson h2 {
            color: var(--color-primary);
            font-size: 1.5rem;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        #playlist {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        #playlist li {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--color-bg2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        #playlist li:hover {
            background: var(--color-bg2);
            transform: translateX(5px);
        }
        
        #playlist li.active {
            background: rgba(108, 99, 255, 0.1);
            border-left: 3px solid var(--color-primary);
            color: var(--color-primary);
            font-weight: 500;
        }
        
        #playlist li.completed {
            color: var(--color-dark);
            position: relative;
        }
        
        #playlist li.completed::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--color-primary);
            margin-left: auto;
        }
        
        .lesson-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .lesson-content-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .lesson-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            margin: 1rem 0;
            box-shadow: var(--shadow-sm);
        }
        
        .content-section {
            margin-bottom: 2rem;
        }
        
        .content-section h2 {
            color: var(--color-primary);
            font-size: 1.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--color-bg2);
        }
        
        /* Collapsible content styles */
        .collapsible-content {
            position: relative;
            max-height: 300px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .collapsible-content.collapsed {
            max-height: 300px;
        }
        
        .collapsible-content.expanded {
            max-height: none;
        }
        
        .collapsible-content.collapsed::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
        }
        
        .toggle-content-btn {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            cursor: pointer;
            display: block;
            margin: 1rem auto 0;
            transition: var(--transition);
        }
        
        .toggle-content-btn:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        /* TinyMCE content styles */
        .lesson-html-content {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: var(--color-dark);
        }
        
        .lesson-html-content h1,
        .lesson-html-content h2,
        .lesson-html-content h3,
        .lesson-html-content h4,
        .lesson-html-content h5,
        .lesson-html-content h6 {
            color: var(--color-primary);
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .lesson-html-content p {
            margin-bottom: 1rem;
        }
        
        .lesson-html-content ul,
        .lesson-html-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .lesson-html-content ul {
            list-style-type: disc;
        }
        
        .lesson-html-content ol {
            list-style-type: decimal;
        }
        
        .lesson-html-content blockquote {
            border-left: 4px solid var(--color-primary);
            padding-left: 1rem;
            margin-left: 0;
            color: var(--color-dark);
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .lesson-html-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .lesson-html-content table th,
        .lesson-html-content table td {
            border: 1px solid var(--color-bg2);
            padding: 0.5rem;
        }
        
        .lesson-html-content table th {
            background-color: var(--color-bg2);
        }
        
        .lesson-html-content a {
            color: var(--color-primary);
            text-decoration: none;
        }
        
        .lesson-html-content a:hover {
            text-decoration: underline;
        }
        
        .lesson-html-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            margin: 1rem 0;
            box-shadow: var(--shadow-sm);
        }
        
        .attachments {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--color-bg2);
        }
        
        .attachments h3 {
            color: var(--color-primary);
            font-size: 1.3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .attachment-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .attachment-card {
            background: var(--color-bg1);
            border-radius: var(--border-radius);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: var(--color-dark);
            transition: var(--transition);
            border: 1px solid var(--color-bg2);
        }
        
        .attachment-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--color-primary-light);
        }
        
        .attachment-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(108, 99, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary);
            font-size: 1.25rem;
        }
        
        .attachment-info {
            flex: 1;
        }
        
        .attachment-info h4 {
            margin: 0 0 0.25rem;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .attachment-info p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--color-dark);
            opacity: 0.7;
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .nav-button {
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .prev-lesson {
            background: var(--color-bg1);
            color: var(--color-dark);
            border: 1px solid var(--color-bg2);
        }
        
        .prev-lesson:hover {
            background: var(--color-bg2);
        }
        
        .next-lesson {
            background: var(--color-primary);
            color: white;
        }
        
        .next-lesson:hover {
            background: var(--color-primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        /* Code blocks styling */
        pre {
            background: #282c34;
            border-radius: var(--border-radius);
            padding: 1rem;
            overflow-x: auto;
            margin: 1.5rem 0;
            box-shadow: var(--shadow-sm);
        }
        
        code {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 0 1.5rem;
            }
            
            .lesson {
                position: static;
                max-height: none;
            }
            
            #courseName, .lesson-content {
                padding: 0 1.5rem;
            }
            
            .attachment-list {
                grid-template-columns: 1fr;
            }
        }

        
       .game-button {
            background-color: var(--color-warning);
            color: var(--color-dark);
        }
        
        #playlist {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0 0;
        }
        
        #playlist li {
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        #playlist li:hover {
            background-color: var(--color-bg1);
        }
        
        #playlist li.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        #playlist li.completed {
            position: relative;
        }
        
        #playlist li.completed:after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--color-success);
            margin-left: auto;
        }
        
        #playlist li .fa-play-circle {
            margin-right: 0.8rem;
            color: inherit;
        }
        
        /* Game Modal Styles */
        .game-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1000;
        }
        
        .game-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            height: 80vh;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s;
        }
        
        
        
    </style>          
</head>
<body>
    <div class="nav">
        <h2><a href="../index.php">CodeKids</a></h2>
        <div>
            <a class="myCourse" href="./myCourses.php">
                <i class="fas fa-arrow-left"></i> My Courses
            </a>
        </div>
    </div>
    
    <div id="courseName">
        <?php if(!empty($course)): ?>
            <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
            <div class="course-progress">
                <div class="progress-bar" style="width: <?php echo rand(30, 90); ?>%"></div>
            </div>
        <?php endif; ?>
    </div>
   
    <div class="container">
        <div class="main-content">
            <?php if(!empty($current_lesson)): ?>
                <!-- Video Player Section -->
                <div class="player-container">
                    <?php if(!empty($current_lesson['lesson_link'])): ?>
                        <video
                            id="lesson-video"
                            class="video-js vjs-big-play-centered"
                            controls
                            preload="auto"
                            data-setup='{"controls": true, "autoplay": false, "preload": "auto"}'
                        >
                            <source src="<?php echo htmlspecialchars($current_lesson['lesson_link']); ?>" type="video/mp4">
                            <p class="vjs-no-js">
                                To view this video please enable JavaScript, and consider upgrading to a
                                web browser that <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                            </p>
                        </video>
                    <?php else: ?>
                        <div style="padding: 2rem; text-align: center;">
                            <i class="fas fa-video-slash" style="font-size: 3rem; color: var(--color-bg2); margin-bottom: 1rem;"></i>
                            <h3>No video available for this lesson</h3>
                        </div>
                    <?php endif; ?>
                    
                    <div class="video-meta">
                        <h1><?php echo htmlspecialchars($current_lesson['lesson_name']); ?></h1>
                        
                        <?php if(!empty($current_lesson['lesson_duration'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span>
                                    <?php 
                                    $duration = $current_lesson['lesson_duration'];
                                    echo floor($duration / 60) . 'm ' . ($duration % 60) . 's';
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($current_lesson['lesson_desc'])): ?>
                            <div class="meta-item">
                                <i class="fas fa-info-circle"></i>
                                <span><?php echo htmlspecialchars($current_lesson['lesson_desc']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Lesson Content Section -->
                <div class="lesson-content">
                    <div class="lesson-content-container">
                        <?php if(!empty($current_lesson['lesson_content'])): ?>
                            <div class="content-section">
                                <h2><i class="fas fa-book-open"></i> Lesson Content</h2>
                                <div class="collapsible-content collapsed" id="lessonContent">
                                    <div class="lesson-html-content">
                                        <?php echo $current_lesson['lesson_content']; ?>
                                    </div>
                                </div>
                                <button class="toggle-content-btn" onclick="toggleContent()">
                                    <span id="toggleText">Show More</span> <i class="fas fa-chevron-down" id="toggleIcon"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($current_lesson['lesson_thumbnail'])): ?>
                            <div class="content-section">
                                <h2><i class="fas fa-image"></i> Lesson Thumbnail</h2>
                                <img src="<?php echo htmlspecialchars($current_lesson['lesson_thumbnail']); ?>" alt="Lesson thumbnail" style="max-width: 100%; border-radius: var(--border-radius);">
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($current_lesson['attachments'])): ?>
                            <div class="attachments">
                                <h3><i class="fas fa-paperclip"></i> Lesson Attachments</h3>
                                <div class="attachment-list">
                                    <?php foreach($current_lesson['attachments'] as $attachment): 
                                        $icon = 'fa-file';
                                        $ext = pathinfo($attachment['name'], PATHINFO_EXTENSION);
                                        
                                        // Set appropriate icon based on file type
                                        switch(strtolower($ext)) {
                                            case 'pdf': $icon = 'fa-file-pdf'; break;
                                            case 'doc':
                                            case 'docx': $icon = 'fa-file-word'; break;
                                            case 'ppt':
                                            case 'pptx': $icon = 'fa-file-powerpoint'; break;
                                            case 'xls':
                                            case 'xlsx': $icon = 'fa-file-excel'; break;
                                            case 'zip':
                                            case 'rar': $icon = 'fa-file-archive'; break;
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif': $icon = 'fa-file-image'; break;
                                            case 'mp4':
                                            case 'webm':
                                            case 'ogg': $icon = 'fa-file-video'; break;
                                            case 'mp3':
                                            case 'wav': $icon = 'fa-file-audio'; break;
                                        }
                                    ?>
                                        <a href="<?php echo htmlspecialchars($attachment['path']); ?>" class="attachment-card" download>
                                            <div class="attachment-icon">
                                                <i class="fas <?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="attachment-info">
                                                <h4><?php echo htmlspecialchars($attachment['name']); ?></h4>
                                                <p><?php echo strtoupper($ext); ?> File</p>
                                            </div>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="navigation-buttons">
                            <?php if($current_lesson_index !== null && $current_lesson_index > 0): ?>
                                <a href="watchCourse.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lessons[$current_lesson_index - 1]['lesson_id']; ?>" class="nav-button prev-lesson">
                                    <i class="fas fa-arrow-left"></i> Previous Lesson
                                </a>
                            <?php else: ?>
                                <span class="nav-button prev-lesson" style="opacity: 0.5; cursor: not-allowed;">
                                    <i class="fas fa-arrow-left"></i> Previous Lesson
                                </span>
                            <?php endif; ?>
                            
                            <!-- Game Button -->
                            <?php 
                            $game_id = getLessonGameId($conn, $current_lesson['lesson_id']);
                            $game_completed = hasCompletedGame($conn, $row['stu_id'], $current_lesson['lesson_id']);
                            ?>
<a href="load_game.php?game_id=<?php echo $game_id; ?>" class="nav-button game-button" target="_blank">
    <i class="fas fa-gamepad"></i> <?php echo $game_completed ? 'Game Completed' : 'Play Game'; ?>
</a>
                            
                            <?php if($current_lesson_index !== null && $current_lesson_index < count($lessons) - 1): ?>
                                <?php if($game_completed): ?>
                                    <a href="watchCourse.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lessons[$current_lesson_index + 1]['lesson_id']; ?>" class="nav-button next-lesson">
                                        Next Lesson <i class="fas fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="nav-button next-lesson" style="opacity: 0.5; cursor: not-allowed;" title="Complete the game to unlock next lesson">
                                        Next Lesson <i class="fas fa-arrow-right"></i>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="nav-button next-lesson" style="opacity: 0.5; cursor: not-allowed;">
                                    Next Lesson <i class="fas fa-arrow-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="lesson-content">
                    <div class="lesson-content-container" style="text-align: center; padding: 3rem;">
                        <i class="fas fa-book-open" style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;"></i>
                        <h2>Select a Lesson to Begin</h2>
                        <p>Choose a lesson from the sidebar to start learning</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Lessons Sidebar -->
        <div class="lesson">
            <h2><i class="fas fa-list-ol"></i> Lessons</h2>
            <ol id="playlist">
                <?php if(!empty($lessons)): ?>
   <?php foreach($lessons as $lesson): ?>
    <li class="<?php echo (!empty($current_lesson) && $current_lesson['lesson_id'] == $lesson['lesson_id']) ? 'active' : ''; ?> <?php echo hasCompletedGame($conn, $row['stu_id'], $lesson['lesson_id']) ? 'completed' : ''; ?>"
        data-url="<?php echo htmlspecialchars($lesson['lesson_link']); ?>"
        data-lesson-id="<?php echo $lesson['lesson_id']; ?>">
        <i class="fas fa-play-circle"></i>
        <span><?php echo htmlspecialchars($lesson['lesson_name']); ?></span>
        <?php if(!empty($lesson['lesson_duration'])): ?>
            <span style="margin-left: auto; font-size: 0.8rem; color: var(--color-dark); opacity: 0.7;">
                <?php echo floor($lesson['lesson_duration'] / 60) . 'm'; ?>
            </span>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
                <?php else: ?>
                    <li>No lessons available for this course</li>
                <?php endif; ?>
            </ol>
        </div>
    </div>

    <!-- Game Modal -->
    <div id="gameModal" class="game-modal">
        <div class="game-modal-content">
            <!-- Game content will be loaded here via JavaScript -->
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    
<script>
    $(document).ready(function(){
        // Initialize syntax highlighting
        hljs.highlightAll();
        
        // Initialize video player
        const player = videojs('lesson-video');
        
        // Handle lesson selection from sidebar
        $('#playlist li').click(function(){
            const lessonId = $(this).data('lesson-id');
            if(lessonId) {
                window.location.href = 'watchCourse.php?course_id=<?php echo $course_id; ?>&lesson_id=' + lessonId;
            }
        });
        
        // Keyboard navigation
        $(document).keydown(function(e){
            if(e.key === 'ArrowUp') { // Up arrow
                const current = $('#playlist li.active');
                if(current.prev('li').length) {
                    const prevLessonId = current.prev('li').data('lesson-id');
                    window.location.href = 'watchCourse.php?course_id=<?php echo $course_id; ?>&lesson_id=' + prevLessonId;
                }
                e.preventDefault();
            }
            if(e.key === 'ArrowDown') { // Down arrow
                const current = $('#playlist li.active');
                if(current.next('li').length) {
                    const nextLessonId = current.next('li').data('lesson-id');
                    window.location.href = 'watchCourse.php?course_id=<?php echo $course_id; ?>&lesson_id=' + nextLessonId;
                }
                e.preventDefault();
            }
            if(e.key === ' ') { // Spacebar to play/pause
                if(player) {
                    if(player.paused()) {
                        player.play();
                    } else {
                        player.pause();
                    }
                }
                e.preventDefault();
            }
        });
        
        // Track video progress
        if(player) {
            player.on('timeupdate', function() {
                const percent = (player.currentTime() / player.duration()) * 100;
                // Could send this to server to track progress
            });
            
            player.on('ended', function() {
                // Mark lesson as watched (but not necessarily completed)
                const currentLesson = $('#playlist li.active');
                $.post('markLessonWatched.php', { 
                    lesson_id: currentLesson.data('lesson-id'),
                    course_id: <?php echo $course_id; ?>
                });
            });
        }
        
        // Set active lesson in playlist
        const currentLessonId = <?php echo isset($current_lesson['lesson_id']) ? $current_lesson['lesson_id'] : 'null'; ?>;
        if(currentLessonId) {
            $('#playlist li').removeClass('active');
            $(`#playlist li[data-lesson-id="${currentLessonId}"]`).addClass('active');
            
            // Scroll to active lesson in sidebar
            const activeLesson = $(`#playlist li[data-lesson-id="${currentLessonId}"]`);
            if(activeLesson.length) {
                const sidebar = $('.lesson');
                const sidebarScroll = sidebar.scrollTop();
                const lessonOffset = activeLesson.offset().top - sidebar.offset().top + sidebarScroll;
                
                sidebar.animate({
                    scrollTop: lessonOffset - 100
                }, 500);
            }
        }
    });
    
    // Function to toggle content visibility
    function toggleContent() {
        const content = document.getElementById('lessonContent');
        const toggleText = document.getElementById('toggleText');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (content.classList.contains('collapsed')) {
            content.classList.remove('collapsed');
            content.classList.add('expanded');
            toggleText.textContent = 'Show Less';
            toggleIcon.classList.remove('fa-chevron-down');
            toggleIcon.classList.add('fa-chevron-up');
        } else {
            content.classList.remove('expanded');
            content.classList.add('collapsed');
            toggleText.textContent = 'Show More';
            toggleIcon.classList.remove('fa-chevron-up');
            toggleIcon.classList.add('fa-chevron-down');
            
            // Scroll to the top of the content section
            content.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Auto-expand if content is short
    window.addEventListener('load', function() {
        const content = document.getElementById('lessonContent');
        if (content) {
            const contentHeight = content.scrollHeight;
            if (contentHeight <= 300) {
                content.classList.remove('collapsed');
                content.classList.add('expanded');
                document.getElementById('toggleText').textContent = 'Show Less';
                document.getElementById('toggleIcon').classList.remove('fa-chevron-down');
                document.getElementById('toggleIcon').classList.add('fa-chevron-up');
            }
        }
    });
    
    // Improved Game Modal Functions
    function openGameModal(gameId) {
        if (!gameId) {
            showGameError('No game available for this lesson!');
            return;
        }
        
        const modal = document.getElementById('gameModal');
        const modalContent = modal.querySelector('.game-modal-content');
        modal.style.display = 'block';
        
        // Show loading state
        modalContent.innerHTML = `
            <div class="game-loading">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading game...</p>
                </div>
            </div>
        `;
        
        // Load game content with better error handling
        fetch(`load_game.php?game_id=${gameId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server returned ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Invalid game data');
                }
                
                // Initialize the game with the loaded data
                initializeGameWithData(data);
            })
            .catch(error => {
                console.error('Game load error:', error);
                showGameError(error.message);
            });
    }
    
    function initializeGameWithData(gameData) {
        const modalContent = document.querySelector('.game-modal-content');
        
        try {
            // Create game HTML structure with Arabic support
            modalContent.innerHTML = `
                <div class="game-container">
                    <div class="game-header">
                        <h1 class="game-title">${gameData.lesson.lesson_name} - Game</h1>
                        <button class="close-game" onclick="closeGameModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="game-content">
                        <!-- Game content will be rendered here -->
                        <div id="gameContent"></div>
                    </div>
                </div>
            `;
            
            // Actual game initialization logic here
            startGameLogic(gameData);
            
        } catch (e) {
            console.error('Game init error:', e);
            showGameError('Failed to initialize game');
        }
    }
    
    function startGameLogic(gameData) {
        // Implement your actual game logic here
        const gameContent = document.getElementById('gameContent');
        
        // Example simple game display
        gameContent.innerHTML = `
            <div class="game-intro">
                <h2>${gameData.lesson.lesson_name} Quiz</h2>
                <p>Total questions: ${gameData.total_questions}</p>
                <button class="start-game-btn" onclick="beginGame()">
                    Start Game
                </button>
            </div>
        `;
    }
    
    function showGameError(message) {
        const modal = document.getElementById('gameModal');
        const modalContent = modal.querySelector('.game-modal-content');
        
        modalContent.innerHTML = `
            <div class="game-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Game Loading Error</h3>
                <p>${message}</p>
                <button onclick="closeGameModal()" class="retry-btn">
                    Close
                </button>
            </div>
        `;
    }
    
    function closeGameModal() {
        const modal = document.getElementById('gameModal');
        modal.style.display = 'none';
        
        // Clean up any game resources
        if (typeof cleanupGame === 'function') {
            cleanupGame();
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('gameModal');
        if (event.target === modal) {
            closeGameModal();
        }
    }
</script>

<style>
    /* Add these styles to your existing CSS */
    .game-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        color: white;
        text-align: center;
    }
    
    .game-error {
        padding: 20px;
        text-align: center;
        color: white;
    }
    
    .game-error i {
        font-size: 3rem;
        color: #ff9800;
        margin-bottom: 15px;
    }
    
    .game-error h3 {
        margin: 10px 0;
    }
    
    .retry-btn {
        padding: 10px 20px;
        background: #ff9800;
        color: white;
        border: none;
        border-radius: 5px;
        margin-top: 15px;
        cursor: pointer;
    }
    
    .start-game-btn {
        padding: 12px 25px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.1rem;
        margin-top: 20px;
        cursor: pointer;
    }
</style>
</body>
</html>