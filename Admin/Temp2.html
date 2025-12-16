<?php
if(!isset($_SESSION)){
    session_start();
}
include('../dbConnection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Check directory permissions
$folders = ['../lessonVID', '../lessonThumbs', '../lessonAttachments'];
foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        if (!mkdir($folder, 0777, true)) {
            die("Failed to create directory: $folder");
        }
    }
    if (!is_writable($folder)) {
        die("Directory is not writable: $folder");
    }
}

// Redirect if not logged in as admin
if(!isset($_SESSION['is_admin_login'])){
    header("Location: ../index.php");
    exit();
}

$adminEmail = $_SESSION['adminLogEmail'] ?? 'Admin';
$msg = '';

// File upload handler function
function handleFileUpload($field, $target_dir, $allowed_ext, $max_size) {
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error: " . $_FILES[$field]['error']);
        return false;
    }

    $file_name = $_FILES[$field]['name'];
    $file_tmp = $_FILES[$field]['tmp_name'];
    $file_size = $_FILES[$field]['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        error_log("Invalid file extension: " . $file_ext);
        return false;
    }

    if ($file_size > $max_size) {
        error_log("File size exceeds limit: " . $file_size);
        return false;
    }

    $new_name = uniqid('', true) . '.' . $file_ext;
    $destination = rtrim($target_dir, '/') . '/' . $new_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        return $destination;
    } else {
        error_log("Failed to move uploaded file to: " . $destination);
        error_log("Upload details: " . print_r($_FILES[$field], true));
        error_log("Is uploaded file: " . (is_uploaded_file($file_tmp) ? 'Yes' : 'No'));
        error_log("Target directory writable: " . (is_writable($target_dir) ? 'Yes' : 'No'));
        return false;
    }
}

// Process form submission
if(isset($_REQUEST['lessonSubmitBtn'])){
    // Validate required fields
    if(empty($_REQUEST['lesson_name']) || empty($_REQUEST['course_id']) || empty($_REQUEST['course_name'])){
        $msg = '<div class="alert alert-warning">Lesson name and course selection are required.</div>';
    } else {
        // Sanitize inputs
        $lesson_name = htmlspecialchars($_REQUEST['lesson_name']);
        $lesson_desc = htmlspecialchars($_REQUEST['lesson_desc'] ?? '');
        $lesson_content = $_REQUEST['lesson_content'] ?? ''; // Don't use htmlspecialchars here
        $course_id = (int)$_REQUEST['course_id'];
        $course_name = htmlspecialchars($_REQUEST['course_name']);
        $lesson_duration = (int)($_REQUEST['lesson_duration'] ?? 0);
        $lesson_order = (int)($_REQUEST['lesson_order'] ?? 0);
        
        // Handle file uploads
        $video_path = '';
        $thumbnail_path = '';
        $attachments = [];
        
        // Process video upload
        if(!empty($_FILES['lesson_link']['name'])){
            $video_path = handleFileUpload('lesson_link', '../lessonVID', ['mp4', 'webm', 'ogg'], 524288000);
            if($video_path === false){
                $msg = '<div class="alert alert-danger">Invalid video file. Only MP4, WebM, and OGG files are allowed (max 500MB).</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Lesson video is required.</div>';
        }
        
        // Process thumbnail upload
        if(empty($msg) && !empty($_FILES['lesson_thumbnail']['name'])){
            $thumbnail_path = handleFileUpload('lesson_thumbnail', '../lessonThumbs', ['jpg', 'jpeg', 'png', 'gif'], 5242880);
            if($thumbnail_path === false){
                $msg = '<div class="alert alert-danger">Invalid thumbnail image. Only JPG, PNG, and GIF files are allowed (max 5MB).</div>';
            }
        }
        
        // Process attachments
        if(empty($msg) && !empty($_FILES['lesson_attachments']['name'][0])){
            $total_attachments = count($_FILES['lesson_attachments']['name']);
            
            for($i = 0; $i < $total_attachments; $i++) {
                if($_FILES['lesson_attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                    $msg = '<div class="alert alert-danger">Error uploading attachment: '.$_FILES['lesson_attachments']['name'][$i].'</div>';
                    continue;
                }
                
                $file_name = $_FILES['lesson_attachments']['name'][$i];
                $file_size = $_FILES['lesson_attachments']['size'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip', 'txt'];
                
                if(!in_array($file_ext, $allowed_ext)) {
                    $msg = '<div class="alert alert-danger">Invalid file type for attachment: '.$file_name.'</div>';
                    continue;
                }
                
                if($file_size > 10485760) {
                    $msg = '<div class="alert alert-danger">Attachment too large: '.$file_name.' (max 10MB)</div>';
                    continue;
                }
                
                $new_name = uniqid('', true) . '.' . $file_ext;
                $destination = '../lessonAttachments/' . $new_name;
                
                if(move_uploaded_file($_FILES['lesson_attachments']['tmp_name'][$i], $destination)) {
                    $attachments[] = [
                        'name' => $file_name,
                        'path' => $destination,
                        'type' => $file_ext
                    ];
                } else {
                    $msg = '<div class="alert alert-danger">Failed to save attachment: '.$file_name.'</div>';
                    error_log("Failed to move attachment: " . print_r($_FILES['lesson_attachments'], true));
                }
            }
        }
        
        // Only proceed if no errors
        if(empty($msg)){
            // Convert attachments array to JSON
            $attachments_json = !empty($attachments) ? json_encode($attachments) : null;
            
            // Use prepared statement to prevent SQL injection
            $sql = "INSERT INTO lesson (lesson_name, lesson_desc, lesson_content, lesson_link, lesson_thumbnail, 
                    attachments, course_id, course_name, lesson_duration, lesson_order) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $msg = '<div class="alert alert-danger">Database error: ' . $conn->error . '</div>';
            } else {
                $stmt->bind_param("ssssssisii", $lesson_name, $lesson_desc, $lesson_content, $video_path, 
                                $thumbnail_path, $attachments_json, $course_id, $course_name, $lesson_duration, $lesson_order);
                
                if($stmt->execute()){
                    $lesson_id = $stmt->insert_id;
                    error_log("Lesson created successfully with ID: " . $lesson_id);
                    $msg = '<div class="alert alert-success">Lesson created successfully! <a href="editLesson.php?id='.$lesson_id.'">Edit lesson</a></div>';
                    // Clear form fields
                    $_POST = array();
                } else {
                    error_log("Database error: " . $stmt->error);
                    $msg = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                }
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
    <title>Create New Lesson | CodeKids</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/ik5uxn1votebrke8wgy3j10c5txgmsgz6li3o3oy54cs1cof/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            --rounded-sm: 0.125rem;
            --rounded: 0.25rem;
            --rounded-md: 0.375rem;
            --rounded-lg: 0.5rem;
            --rounded-xl: 0.75rem;
            --rounded-2xl: 1rem;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.5;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            height: 100vh;
            position: fixed;
            box-shadow: var(--shadow-md);
            transition: all 0.3s;
            z-index: 50;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            margin: 0 0.5rem;
            color: var(--gray);
            border-radius: var(--rounded-lg);
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .sidebar-item:hover {
            background-color: var(--gray-light);
            color: var(--primary);
        }
        
        .sidebar-item.active {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            font-weight: 500;
        }
        
        .sidebar-item i {
            margin-right: 1rem;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-item-text {
            white-space: nowrap;
        }
        
        .sidebar-collapsed .sidebar-item-text {
            display: none;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            transition: all 0.3s;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .sidebar-collapsed + .main-content {
            margin-left: 80px;
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: var(--rounded-xl);
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        /* Form Styles */
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
            border-radius: var(--rounded);
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        /* File Upload */
        .file-upload {
            border: 2px dashed var(--gray-light);
            border-radius: var(--rounded-lg);
            padding: 2rem;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            background: #f8fafc;
        }
        
        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.05);
        }
        
        .file-upload i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .file-preview {
            margin-top: 1rem;
            display: none;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--rounded);
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-light);
            color: var(--dark);
        }
        
        .btn-outline:hover {
            background: var(--gray-light);
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--rounded);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-item-text {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn fixed top-4 left-4 z-50 p-2 rounded-full bg-white shadow-md lg:hidden">
        <i class="fas fa-bars text-gray-600"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-center">

                <span class="sidebar-item-text ml-3 font-bold text-xl">CodeKids</span>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="adminDashboard.php" class="sidebar-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="sidebar-item-text">Dashboard</span>
            </a>
            <a href="courses.php" class="sidebar-item">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="sidebar-item-text">Courses</span>
            </a>
            <a href="lesson.php" class="sidebar-item active">
                <i class="fas fa-book-open"></i>
                <span class="sidebar-item-text">Lessons</span>
            </a>
            <a href="students.php" class="sidebar-item">
                <i class="fas fa-users"></i>
                <span class="sidebar-item-text">Students</span>
            </a>
            <a href="sellReport.php" class="sidebar-item">
                <i class="fas fa-chart-bar"></i>
                <span class="sidebar-item-text">Reports</span>
            </a>
            <a href="feedback.php" class="sidebar-item">
                <i class="fas fa-comment-alt"></i>
                <span class="sidebar-item-text">Feedback</span>
            </a>
            <a href="adminChangePassword.php" class="sidebar-item">
                <i class="fas fa-key"></i>
                <span class="sidebar-item-text">Password</span>
            </a>
            <a href="../logout.php" class="sidebar-item mt-4 text-red-500 hover:text-red-600">
                <i class="fas fa-sign-out-alt"></i>
                <span class="sidebar-item-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-plus-circle text-indigo-600"></i>
                    <span>Create New Lesson</span>
                </h1>
            </div>
            
            <!-- Alert Messages -->
            <?php if(!empty($msg)): ?>
                <div class="alert <?php 
                    echo strpos($msg, 'success') !== false ? 'alert-success' : 
                         (strpos($msg, 'warning') !== false ? 'alert-warning' : 'alert-danger');
                ?> animate-fade">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Course Info -->
                    <div class="form-group">
                        <label class="form-label" for="course_id">Course ID</label>
                        <input
                            type="text"
                            class="form-control"
                            id="course_id"
                            name="course_id"
                            value="<?php echo isset($_SESSION['course_id']) ? htmlspecialchars($_SESSION['course_id']) : ''; ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="course_name">Course Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="course_name"
                            name="course_name"
                            value="<?php echo isset($_SESSION['course_name']) ? htmlspecialchars($_SESSION['course_name']) : ''; ?>"
                            readonly
                        >
                    </div>
                    
                    <!-- Lesson Basics -->
                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="lesson_name">Lesson Title*</label>
                        <input
                            type="text"
                            class="form-control"
                            id="lesson_name"
                            name="lesson_name"
                            value="<?php echo isset($_POST['lesson_name']) ? htmlspecialchars($_POST['lesson_name']) : ''; ?>"
                            required
                            placeholder="Introduction to Programming"
                        >
                    </div>
                    
                    <div class="form-group md:col-span-2">
                        <label class="form-label" for="lesson_desc">Short Description</label>
                        <textarea
                            class="form-control"
                            id="lesson_desc"
                            name="lesson_desc"
                            rows="3"
                            placeholder="Brief summary of what this lesson covers"
                        ><?php echo isset($_POST['lesson_desc']) ? htmlspecialchars($_POST['lesson_desc']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Lesson Media -->
                    <div class="form-group">
                        <label class="form-label">Lesson Video*</label>
                        <label for="lesson_link" class="file-upload">
                            <i class="fas fa-video"></i>
                            <div class="text-sm font-medium">Click to upload video</div>
                            <div class="text-xs text-gray-500">MP4, WebM or OGG (Max 500MB)</div>
                            <input type="file" id="lesson_link" name="lesson_link" accept="video/mp4,video/webm,video/ogg" required class="hidden">
                        </label>
                        <div class="file-preview" id="videoPreview">
                            <video controls class="w-full rounded-lg"></video>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Lesson Thumbnail</label>
                        <label for="lesson_thumbnail" class="file-upload">
                            <i class="fas fa-image"></i>
                            <div class="text-sm font-medium">Click to upload thumbnail</div>
                            <div class="text-xs text-gray-500">JPG, PNG or GIF (Max 5MB)</div>
                            <input type="file" id="lesson_thumbnail" name="lesson_thumbnail" accept="image/*" class="hidden">
                        </label>
                        <div class="file-preview" id="thumbnailPreview">
                            <img src="" alt="Thumbnail preview" class="w-full h-40 object-cover rounded-lg">
                        </div>
                    </div>
                    
                    <!-- Lesson Meta -->
                    <div class="form-group">
                        <label class="form-label" for="lesson_duration">Duration (minutes)</label>
                        <input
                            type="number"
                            class="form-control"
                            id="lesson_duration"
                            name="lesson_duration"
                            value="<?php echo isset($_POST['lesson_duration']) ? (int)$_POST['lesson_duration'] : 10; ?>"
                            min="1"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="lesson_order">Order in Course</label>
                        <input
                            type="number"
                            class="form-control"
                            id="lesson_order"
                            name="lesson_order"
                            value="<?php echo isset($_POST['lesson_order']) ? (int)$_POST['lesson_order'] : 0; ?>"
                            min="0"
                        >
                    </div>
                    
                    <!-- Lesson Content -->
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Lesson Content</label>
                        <textarea
                            class="form-control"
                            id="lesson_content"
                            name="lesson_content"
                            rows="10"
                        ><?php echo isset($_POST['lesson_content']) ? $_POST['lesson_content'] : '<h2>Lesson Content</h2><p>Add your detailed lesson content here...</p>'; ?></textarea>
                    </div>
                    
                    <!-- Attachments -->
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Attachments (PDFs, Slides, etc.)</label>
                        <label for="lesson_attachments" class="file-upload">
                            <i class="fas fa-paperclip"></i>
                            <div class="text-sm font-medium">Click to upload files</div>
                            <div class="text-xs text-gray-500">PDF, DOC, PPT, ZIP (Max 10MB each)</div>
                            <input type="file" id="lesson_attachments" name="lesson_attachments[]" multiple class="hidden">
                        </label>
                        <div id="attachmentsList" class="flex flex-wrap gap-2 mt-3">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-group md:col-span-2 flex justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="lesson.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="lessonSubmitBtn"
                            name="lessonSubmitBtn"
                        >
                            <i class="fas fa-save"></i> Create Lesson
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize TinyMCE editor
        tinymce.init({
            selector: '#lesson_content',
            plugins: 'advlist autolink lists link image charmap preview anchor table code help wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
            height: 400,
            content_style: 'body { font-family: "Inter", sans-serif; font-size: 14px; }',
            skin: 'oxide',
            icons: 'thin'
        });
        
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // File upload previews
        document.getElementById('lesson_link').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('videoPreview');
            const video = preview.querySelector('video');
            
            if(file) {
                const fileURL = URL.createObjectURL(file);
                video.src = fileURL;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
        
        document.getElementById('lesson_thumbnail').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('thumbnailPreview');
            const img = preview.querySelector('img');
            
            if(file) {
                const fileURL = URL.createObjectURL(file);
                img.src = fileURL;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Attachments list
        document.getElementById('lesson_attachments').addEventListener('change', function(e) {
            const files = e.target.files;
            const attachmentsList = document.getElementById('attachmentsList');
            attachmentsList.innerHTML = '';
            
            if(files.length > 0) {
                for(let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileExt = file.name.split('.').pop().toLowerCase();
                    let iconClass = 'fa-file';
                    
                    // Set appropriate icon based on file type
                    if(['pdf'].includes(fileExt)) iconClass = 'fa-file-pdf';
                    else if(['doc', 'docx'].includes(fileExt)) iconClass = 'fa-file-word';
                    else if(['ppt', 'pptx'].includes(fileExt)) iconClass = 'fa-file-powerpoint';
                    else if(['xls', 'xlsx'].includes(fileExt)) iconClass = 'fa-file-excel';
                    else if(['zip', 'rar'].includes(fileExt)) iconClass = 'fa-file-archive';
                    else if(['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) iconClass = 'fa-file-image';
                    
                    const attachmentItem = document.createElement('div');
                    attachmentItem.className = 'flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-full text-sm';
                    attachmentItem.innerHTML = `
                        <i class="fas ${iconClass} text-indigo-600"></i>
                        <span class="truncate max-w-xs">${file.name}</span>
                        <span class="text-xs opacity-70">(${(file.size / 1024 / 1024).toFixed(2)}MB)</span>
                    `;
                    attachmentsList.appendChild(attachmentItem);
                }
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            // Validate video size if present
            const videoInput = document.getElementById('lesson_link');
            if(videoInput.files.length > 0 && videoInput.files[0].size > 524288000) {
                e.preventDefault();
                alert('Video file exceeds 500MB limit. Please choose a smaller file.');
                return;
            }
            
            // Validate thumbnail size if present
            const thumbInput = document.getElementById('lesson_thumbnail');
            if(thumbInput.files.length > 0 && thumbInput.files[0].size > 5242880) {
                e.preventDefault();
                alert('Thumbnail image exceeds 5MB limit. Please choose a smaller file.');
                return;
            }
            
            // Validate attachments
            const attachInput = document.getElementById('lesson_attachments');
            if(attachInput.files.length > 0) {
                for(let i = 0; i < attachInput.files.length; i++) {
                    if(attachInput.files[i].size > 10485760) {
                        e.preventDefault();
                        alert(`Attachment "${attachInput.files[i].name}" exceeds 10MB limit. Please choose smaller files.`);
                        return;
                    }
                }
            }
            
            // Ensure lesson name is provided
            if(document.getElementById('lesson_name').value.trim() === '') {
                e.preventDefault();
                alert('Please provide a lesson title.');
                return;
            }
        });
    </script>
</body>
</html>