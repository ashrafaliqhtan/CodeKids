<?php
// Start secure session with strict settings
session_start([
    'cookie_lifetime' => 86400,      // 1 day
    'cookie_secure'   => true,       // Requires HTTPS
    'cookie_httponly' => true,       // Prevent JavaScript access
    'use_strict_mode' => true        // Prevent session fixation
]);

// Include database connection with error handling
require_once('./dbConnection.php');
if (!$conn) {
    die("Database connection failed");
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate and sanitize course ID
$course_id = filter_input(INPUT_GET, 'course_id', FILTER_VALIDATE_INT);
if (!$course_id || $course_id <= 0) {
    header("Location: courses.php");
    exit();
}

// Fetch course details using prepared statement
$stmt = $conn->prepare("SELECT * FROM course WHERE course_id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows !== 1) {
    header("Location: courses.php");
    exit();
}

$course = $course_result->fetch_assoc();

// Fetch lessons using prepared statement
$lesson_stmt = $conn->prepare("SELECT * FROM lesson WHERE course_id = ? ORDER BY lesson_id");
if (!$lesson_stmt) {
    die("Database error: " . $conn->error);
}

$lesson_stmt->bind_param("i", $course_id);
$lesson_stmt->execute();
$lesson_result = $lesson_stmt->get_result();

// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?> course details - Learn coding with fun projects">
    <meta name="robots" content="index, follow">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="shortcut icon" type="image/png" href="images/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/courseDetails.css" />
    <title><?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?> | CodeKids</title>
    <style>
    
    
    
        :root {
            --color-primary: #6c63ff;
            --color-success: #00bf8e;
            --color-warning: #f7c94b;
            --color-danger: #f75842;
            --color-danger-variant: rgba(247, 88, 66, 0.4);
            --color-white: #fff;
            --color-light: rgba(255, 255, 255, 0.7);
            --color-black: #000;
            --color-bg: #1f2641;
            --color-bg1: #2e3267;
            --color-bg2: #424890;
        }

        .course-container {
            background: var(--color-bg2);
            padding: 3rem;
            border-radius: 1rem;
            margin: 2rem auto;
            max-width: 1200px;
            box-shadow: 0 2rem 3rem rgba(0, 0, 0, 0.3);
        }
        
        .lesson-list {
            list-style-type: none;
            padding: 0;
        }
        
        .lesson-list li {
            background: var(--color-bg1);
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .lesson-list li:hover {
            transform: translateX(10px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
        }
        
        .lesson-number {
            font-weight: bold;
            color: var(--color-warning);
            margin-right: 1rem;
            min-width: 120px;
        }
        
        #course-img {
            width: 100%;
            border-radius: 1rem;
            margin-bottom: 2rem;
            border: 3px solid var(--color-primary);
            height: auto;
            max-height: 400px;
            object-fit: cover;
        }
        
        .buy-now-btn {
            background: var(--color-success);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .buy-now-btn:hover {
            background: var(--color-warning);
            transform: scale(1.05);
        }
        
        .price-container {
            margin: 1rem 0;
        }
        
        .original-price {
            text-decoration: line-through;
            color: var(--color-light);
            margin-right: 1rem;
        }
        
        .discounted-price {
            font-size: 1.5rem;
            color: var(--color-warning);
            font-weight: bold;
        }
        
        .lessons-header {
            margin-top: 3rem;
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .course-container {
                padding: 1.5rem;
                margin: 1rem auto;
            }
            
            .buy-now-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="index.php" aria-label="Home"><h3>🎮 CodeKids</h3></a>
            <ul class="nav_menu">
                <?php if(isset($_SESSION['is_login'])): ?>
                    <li><a href="Student/studentProfile.php"><i class="uil uil-smile"></i> Profile</a></li>
                    <li><a href="Notes/notes.php"><i class="uil uil-notebooks"></i> Notes</a></li>
                    <li><a href="logout.php"><i class="uil uil-signout"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="loginSignUp.php"><i class="uil uil-rocket"></i> Start Learning</a></li>
                    <li><a href="loginSignUp.php"><i class="uil uil-user-plus"></i> Join Now</a></li>
                <?php endif; ?>
                <li><a href="Quiz/quiz.php"><i class="uil uil-question-circle"></i> Quiz</a></li>
                <li><a href="contact.php"><i class="uil uil-envelope"></i> Contact</a></li>
            </ul>
            <button id="open-menu-btn" aria-label="Open menu"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn" aria-label="Close menu"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>

    <main class="course-container">
        <img src="<?php echo htmlspecialchars(str_replace('..', '.', $course['course_img']), ENT_QUOTES, 'UTF-8'); ?>" 
             alt="<?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?>" 
             id="course-img" 
             loading="lazy" />

        <h1 id="course-title"><?php echo htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="course-description">
            <?php echo htmlspecialchars($course['course_desc'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <div class="course-meta">
            <p class="course-duration">
                <i class="uil uil-clock"></i> 
                <?php echo htmlspecialchars($course['course_duration'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <div class="price-container">
                <span class="original-price">Rs.<?php echo htmlspecialchars($course['course_original_price'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="discounted-price">Rs.<?php echo htmlspecialchars($course['course_price'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>

        <form action="checkout.php" method="POST" class="enroll-form">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="buy-now-btn" name="buy" aria-label="Enroll in this course">
                <i class="uil uil-shopping-cart-alt"></i> Start Learning Journey!
            </button>
        </form>

        <h2 class="lessons-header"><i class="uil uil-book-open"></i> Course Adventures</h2>
        <ul class="lesson-list">
            <?php if($lesson_result->num_rows > 0): ?>
                <?php $lesson_num = 1; ?>
                <?php while($lesson = $lesson_result->fetch_assoc()): ?>
                    <li class="lesson">
                        <span class="lesson-number">Adventure <?php echo $lesson_num++; ?>:</span>
                        <?php echo htmlspecialchars($lesson['lesson_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="lesson">Coming soon! 🚀</li>
            <?php endif; ?>
        </ul>
    </main>

    <footer>
        <div class="container footer_container">
            <div class="footer_1">
                <a class="home_button" href="index.php" aria-label="Home"><h3>🎮 CodeKids</h3></a>
                <p>Making coding fun and accessible for everyone!  </p>
                <div class="mascot">
                    <img src="images/mascot.png" alt="Codey the Robot" loading="lazy">
                </div>
            </div>
            <div class="footer_2">
                <h4>Learning Paths</h4>
                <ul class="permalinks">
                    <li><a href="courses.php#beginner">Beginner Level</a></li>
                    <li><a href="courses.php#intermediate">Intermediate Level</a></li>
                    <li><a href="courses.php#advanced">Advanced Level</a></li>
                </ul>
            </div>
            <div class="footer_3">
                <h4>Support</h4>
                <ul class="privacy">
                    <li><a href="contact.php">Help Center</a></li>
                    <li><a href="terms.php">Safety Guidelines</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>
            <div class="footer_4">
                <h4>Connect With Us</h4>
                <p>✉️ hello@codekids.com</p>
                <p>📞 +1 (234) 567-8900</p>
                <ul class="footer_socials">
                    <li><a href="#" aria-label="YouTube"><i class="uil uil-youtube"></i></a></li>
                    <li><a href="#" aria-label="Instagram"><i class="uil uil-instagram-alt"></i></a></li>
                    <li><a href="#" aria-label="GitHub"><i class="uil uil-github"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer_copyright">
            <small>© <?php echo date('Y'); ?> CodeKids - Where Fun Meets Code! 🚀</small>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script>
        // Mobile menu toggle functionality
        const navMenu = document.querySelector('.nav_menu');
        const openMenuBtn = document.querySelector('#open-menu-btn');
        const closeMenuBtn = document.querySelector('#close-menu-btn');

        // Function to close mobile menu
        const closeNav = () => {
            navMenu.style.display = 'none';
            closeMenuBtn.style.display = 'none';
            openMenuBtn.style.display = 'inline-block';
            document.body.style.overflow = 'auto';
        }

        // Open menu handler
        openMenuBtn.addEventListener('click', () => {
            navMenu.style.display = 'flex';
            closeMenuBtn.style.display = 'inline-block';
            openMenuBtn.style.display = 'none';
            document.body.style.overflow = 'hidden';
        });

        // Close menu handler
        closeMenuBtn.addEventListener('click', closeNav);

        // Close menu when clicking on a nav item (for mobile)
        if(window.innerWidth < 1024) {
            document.querySelectorAll('.nav_menu li a').forEach(navItem => {
                navItem.addEventListener('click', closeNav);
            });
        }

        // Responsive adjustments
        window.addEventListener('resize', () => {
            if(window.innerWidth >= 1024) {
                navMenu.style.display = 'flex';
                closeMenuBtn.style.display = 'none';
                openMenuBtn.style.display = 'none';
                document.body.style.overflow = 'auto';
            } else {
                navMenu.style.display = 'none';
                openMenuBtn.style.display = 'inline-block';
            }
        });

        // Initialize menu state
        if(window.innerWidth >= 1024) {
            navMenu.style.display = 'flex';
        } else {
            navMenu.style.display = 'none';
        }
    </script>
</body>
</html>
<?php
// Close database connections
$stmt->close();
$lesson_stmt->close();
$conn->close();
?>