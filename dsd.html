<?php
session_start();
include('codekids/dbConnection.php');

// Handle form toggling
$show_signup = isset($_GET['show_signup']) ? true : false;

// Handle Student Login
if(isset($_POST['stuLogEmail']) && isset($_POST['stuLogPass'])) {
    $stuLogEmail = $_POST['stuLogEmail'];
    $stuLogPass = $_POST['stuLogPass'];
    
    $sql = "SELECT stu_email, stu_pass FROM students WHERE stu_email='".$stuLogEmail."'";
    $result = $conn->query($sql);
    
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if(password_verify($stuLogPass, $row['stu_pass'])) {
            $_SESSION['is_login'] = true;
            $_SESSION['stuLogEmail'] = $stuLogEmail;
            header('Location: index.php');
            exit();
        } else {
            $login_error = "Invalid Email or Password";
        }
    } else {
        $login_error = "Invalid Email or Password";
    }
}

// Handle Student Signup
if(isset($_POST['stuname']) && isset($_POST['stuemail']) && isset($_POST['stupass'])) {
    $stuname = $_POST['stuname'];
    $stuemail = $_POST['stuemail'];
    $stupass = password_hash($_POST['stupass'], PASSWORD_BCRYPT);
    
    // Check if email exists
    $check_sql = "SELECT stu_email FROM students WHERE stu_email='".$stuemail."'";
    $check_result = $conn->query($check_sql);
    
    if($check_result->num_rows > 0) {
        $signup_error = "Email already registered!";
        $show_signup = true;
    } else {
        $insert_sql = "INSERT INTO students(stu_name, stu_email, stu_pass) VALUES('$stuname', '$stuemail', '$stupass')";
        if($conn->query($insert_sql)) {
            $signup_success = "Registration successful! Please login.";
            $show_signup = false;
        } else {
            $signup_error = "Registration failed: " . $conn->error;
            $show_signup = true;
        }
    }
}





?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="shortcut icon" type="codekids/image/png" href="images/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <link rel="stylesheet" href="codekids/css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="codekids/css/exploreStyle.css">
    <link rel="stylesheet" href="codekids/css/exstyle.css">
    <title>codekids | Let's Crack the Code</title>
</head>
<body>
    <nav>
        <div class="container nav_container">
            <a class="home_button" href="index.php"><h3>🎮 codekids</h3></a>
            <ul class="nav_menu">
                <li><a href="codekids/courses.php"><i class="uil uil-rocket"></i> Start Learning</a></li>
                <li><a href="codekids/Games/index.html"><i class="uil uil-info-circle"></i> Games</a></li>
                <?php if(isset($_SESSION['is_login'])): ?>
                    <li><a href="codekids/Student/studentProfile.php"><i class="uil uil-smile"></i> My Profile</a></li>
                    <li><a href="codekids/logout.php"><i class="uil uil-signout"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php?show_signup=true#explore"><i class="uil uil-user-plus"></i> Join Now</a></li>
                    <li><a href="codekids/loginSignUp.php"><i class="uil uil-signin"></i> Login</a></li>
                <?php endif; ?>
                <li><a href="codekids/aboutUs.php"><i class="uil uil-info-circle"></i> About Us</a></li>
                <?php if(isset($_SESSION['is_login'])): ?>
                    <li><a href="codekids/Discussion_Forum/discussionForum.php"><i class="uil uil-comments-alt"></i></a></li>
                <?php endif; ?>
            </ul>
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>

    <header>
        <div class="header_content">
            <div class="header_text-box">
                <h1 class="heading-primary">
                    <span class="heading-primary--main">Let's Learn Coding!</span>
                    <span class="heading-primary--sub">Where Fun Meets Technology 🚀</span>
                </h1>
                <div class="header-characters">
                    <img src="codekids/images/robot.png" alt="Friendly Robot">
                    <img src="codekids/images/dino.png" alt="Coding Dinosaur">
                </div>
                <?php if(!isset($_SESSION['is_login'])): ?>
                    <a href="loginSignUp.php" class="btn btn-primary">Start Adventure!</a>
                <?php else: ?>
                    <a href="codekids/Student/studentProfile.php" class="btn btn-primary">Continue Learning!</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

<div id="explore" class="overlay" style="<?= (isset($_GET['show_signup']) || isset($_POST['stuname']) || isset($_POST['stuLogEmail'])) ? 'display:block' : '' ?>">
    <div class="wrapper">
        <a href="index.php" class="close">&times;</a>
        <div class="column details">
            <?php if(!$show_signup && !isset($signup_error)): ?>
            <!-- Login Form -->
            <div class="signin">
                <h2 class="stu-form-header"><span class="span-header">Student</span> Sign in</h2>
                <form method="POST" action="index.php#explore">
                    <input type="email" placeholder="Email" name="stuLogEmail" required 
                           value="<?= isset($_POST['stuLogEmail']) ? htmlspecialchars($_POST['stuLogEmail']) : '' ?>"/>
                    <input type="password" placeholder="Password" name="stuLogPass" required />
                    <?php if(isset($login_error)): ?>
                        <small style="color:red; margin-bottom:.3rem"><?= $login_error ?></small>
                    <?php endif; ?>
                    <?php if(isset($signup_success)): ?>
                        <small style="color:green; margin-bottom:.3rem"><?= $signup_success ?></small>
                    <?php endif; ?>
                    <a href="#">Forgot Password</a>
                    <button type="submit" class="form-submit">Log in</button>
                </form>
                <span class="form-span">You Don't have account yet? 
                    <a href="index.php?show_signup=true#explore" class="toggle-form">Create it Now</a>
                </span>
            </div>
            <?php else: ?>
            <!-- Signup Form -->
            <div class="signup">
                <h2 class="stu-form-header"><span class="span-header">Student</span> Sign Up</h2>
                <form method="POST" action="index.php#explore">
                    <input type="text" placeholder="Full Name" name="stuname" required 
                           value="<?= isset($_POST['stuname']) ? htmlspecialchars($_POST['stuname']) : '' ?>"/>
                    <input type="email" placeholder="Email" name="stuemail" required 
                           value="<?= isset($_POST['stuemail']) ? htmlspecialchars($_POST['stuemail']) : '' ?>"/>
                    <input type="password" placeholder="Password" name="stupass" required />
                    <?php if(isset($signup_error)): ?>
                        <small style="color:red; margin-bottom:.3rem"><?= $signup_error ?></small>
                    <?php endif; ?>
                    <button type="submit" class="form-submit">Sign up</button>
                </form>
                <span class="form-span">Already have an Account? 
                    <a href="index.php#explore" class="toggle-form">Sign In</a>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <div class="column content">
            <?php if(!$show_signup && !isset($signup_error)): ?>
            <div class="signin">
                <h1>Welcome Back</h1>
                <p>To keep Connected with us please login with your personal information</p>
            </div>
            <?php else: ?>
            <div class="signup">
                <h1>Hello Friend!</h1>
                <p>Enter your personal details and start journey with us</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <section class="categories">
        <div class="container categories_container">
            <div class="categories_left">
                <h1>Categories</h1>
                <p>
                    "Explore a wide range of subjects including Programming, Blockchain, Graphic Design, Marketing, Finance, Artificial Intelligence, Data Science. Discover new skills and expand your knowledge with our diverse selection of courses."
                </p>
                <a href="courses.php" class="btn">Learn More</a>
            </div>
            <div class="categories_right">
                <article class="category">
                    <span class="category_icon"><i class="uil uil-java-script"></i></span>
                    <h5>Languages</h5>
                    <p>Languages that enables interactive and dynamic features.</p>
                </article>
                <article class="category">
                    <span class="category_icon"><i class="uil uil-bitcoin-circle"></i></span>
                    <h5>Blockchain</h5>
                    <p>Blockchain is a decentralized, distributed digital ledger technology.</p>
                </article>
                <article class="category">
                    <span class="category_icon"><i class="uil uil-palette"></i></span>
                    <h5>Graphic Design</h5>
                    <p>Graphic design is the art and technique of visual communication.</p>
                </article>
                <article class="category">
                    <span class="category_icon"><i class="uil uil-usd-circle"></i></span>
                    <h5>Finance</h5>
                    <p>Finance deals with the management of money and assets.</p>
                </article>
                <article class="category">
                    <span class="category_icon"><i class="uil uil-megaphone"></i></span>
                    <h5>Marketing</h5>
                    <p>Marketing is the process of promoting and selling products or services.</p>
                </article>
                <article class="category">
                    <span class="category_icon"><i class="uil uil-puzzle-piece"></i></span>
                    <h5>Reasoning</h5>
                    <p>Reasoning is thinking through problems and making logical conclusions.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="courses">
        <h2>Trend Courses</h2>
        <div class="container courses_container">
            <article class="course">
                <div class="course_image">
                    <img src="codekids/images/html-course.jpg" class="responsive" />
                </div>
                <div class="course_info">
                    <h4>HTML</h4>
                    <p>Learn HTML</p>
                    <a href="codekids/learning/html/chapter1.php" class="btn btn-secondary">Learning Now</a>
                </div>
            </article>
            <article class="course">
                <div class="course_image">
                    <img src="codekids/images/css-course.jpg" class="responsive" />
                </div>
                <div class="course_info">
                    <h4>CSS</h4>
                    <p>Learn CSS</p>
                    <a href="codekids/learning/css/chapter1.php" class="btn btn-secondary">Learning Now</a>
                </div>
            </article>
        </div>
    </section>

    <section class="courses">
        <h2>Our Popular Courses</h2>
        <div class="container courses_container">
            <?php  
            $sql = "SELECT * FROM course LIMIT 6";
            $result = $conn->query($sql);
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $course_id = $row['course_id'];
                    echo '<article class="course">
                        <div class="course_image">
                            <img src="'.str_replace('..','.',$row['course_img']).'" class="responsive" />
                        </div>
                        <div class="course_info">
                            <h4>'.$row['course_name'].'</h4>
                            <p>'.$row['course_desc'].'</p>
                            <a href="courseDetails.php?course_id='.$course_id.'" class="btn btn-secondary">Learning Now</a>
                        </div>
                    </article>';
                }
            }
            ?>
        </div>
    </section>

    <section class="faqs">
        <h2>🌟 Frequently Asked Adventures</h2>
        <div class="container faqs_container">
            <article class="faq">
                <div class="faq_icon"><i class="uil uil-rocket"></i></div>
                <div class="question_answer">
                    <h4>How do I choose my coding adventure? 🧭</h4>
                    <p>Start with what excites you! Do you love games, robots, or websites? Our friendly robot Codey will help you pick the perfect journey! 🤖</p>
                </div>
            </article>
            <article class="faq">
                <div class="faq_icon"><i class="uil uil-shopping-cart-alt"></i></div>
                <div class="question_answer">
                    <h4>How do I start an adventure? 🛒</h4>
                    <p>Click the "Start Journey" button on any adventure page! Grown-ups can help with payment using magic cards or internet money. 💳✨</p>
                </div>
            </article>
        </div>
    </section>

    <section class="container testimonials_container mySwiper">
        <h2>🎮 Our Coding Champions Say... 🏆</h2>
        <div class="swiper-wrapper">
            <article class="testimonial swiper-slide">
                <div class="avatar">
                    <img src="images/cody-avatar.png" alt="Cody the Coder" loading="lazy" />
                    <div class="emoji-decor">👑</div>
                </div>
                <div class="testimonial_info">
                    <h5>Cody <span class="age">(Age 9)</span></h5>
                    <small>Game Creator</small>
                    <div class="testimonial_body">
                        <p>"I built my first video game in 3 days! Now I'm making a robot dance party! 💃🤖"</p>
                    </div>
                </div>
            </article>
            <article class="testimonial swiper-slide">
                <div class="avatar">
                    <img src="images/pixel-avatar.png" alt="Pixel the Programmer" loading="lazy" />
                    <div class="emoji-decor">🌟</div>
                </div>
                <div class="testimonial_info">
                    <h5>Pixel <span class="age">(Grade 4)</span></h5>
                    <small>Website Wizard</small>
                    <div class="testimonial_body">
                        <p>"I made a dinosaur website that ROARS! My teacher gave me a gold star! 🦖⭐"</p>
                    </div>
                </div>
            </article>
        </div>
        <div class="swiper-pagination"></div>
    </section>

    <footer>
        <div class="container footer_container">
            <div class="footer_1">
                <a class="home_button" href="index.php"><h3>🎮 codekids</h3></a>
                <p>Making coding fun and accessible for everyone! 🌈</p>
                <div class="mascot">
                    <img src="images/mascot.png" alt="Codey the Robot">
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script src="codekids/js/main.js"></script>
    <script>
        // Initialize Swiper
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                600: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            },
            autoplay: {
                delay: 5000,
            },
        });

        // Show overlay when page loads with #explore in URL
        document.addEventListener('DOMContentLoaded', function() {
            if(window.location.hash === '#explore') {
                document.getElementById('explore').style.display = 'block';
            }
        });
    </script>
</body>
</html>