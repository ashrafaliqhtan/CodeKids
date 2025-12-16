<?php
session_start();
include('./dbConnection.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
    <link rel="shortcut icon" type="image/png" href="images/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
    <title>Courses | CodeKids - Learn Coding the Fun Way!</title>
    <style>
      nav {
        background: var(--color-primary);
      }
      .courses {
        background: var(--color-bg2);
        padding: 4rem 0;
      }
      .course {
        background: var(--color-bg1);
        border-radius: 1rem;
        overflow: hidden;
        transition: var(--transition);
      }
      .course:hover {
        transform: translateY(-0.5rem);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.3);
      }
      .course_info h4 {
        color: var(--color-white);
        margin: 1.2rem 0;
      }
      .btn-secondary {
        background: var(--color-success);
      }
    </style>
  </head>
  <body>
    <nav>
      <div class="container nav_container">
        <a class="home_button" href="index.php"><h3>🎮 CodeKids</h3></a>
        <ul class="nav_menu">
          <?php if(isset($_SESSION['is_login'])): ?>
            <li><a href="Student/studentProfile.php"><i class="uil uil-smile"></i> My Profile</a></li>
            <li><a href="logout.php"><i class="uil uil-signout"></i> Logout</a></li>
          <?php else: ?>
            <li><a href="courses.php#explore"><i class="uil uil-rocket"></i> Start Learning</a></li>
            <li><a href="#explore"><i class="uil uil-user-plus"></i> Join Now</a></li>
          <?php endif; ?>
          <li><a href="Notes/notes.php"><i class="uil uil-notebooks"></i> Notes</a></li>
          <li><a href="Quiz/exam.php"><i class="uil uil-question-circle"></i> Quiz</a></li>
          <li><a href="contact.php"><i class="uil uil-envelope"></i> Contact</a></li>
        </ul>
        <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
        <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
      </div>
    </nav>

    <section class="courses">
      <h2>Our Awesome Courses</h2>
      <div class="container courses_container">
        <?php  
        $stmt = $conn->prepare("SELECT * FROM course");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $course_id = (int)$row['course_id'];
            $course_name = htmlspecialchars($row['course_name']);
            $course_desc = htmlspecialchars($row['course_desc']);
            $course_img = str_replace('..', '.', $row['course_img']);
            
            echo '<article class="course">
                    <div class="course_image">
                      <img src="'.$course_img.'" alt="'.$course_name.'" class="responsive" />
                    </div>
                    <div class="course_info">
                      <h4>'.$course_name.'</h4>
                      <p>'.$course_desc.'</p>
                      <a href="courseDetails.php?course_id='.$course_id.'" class="btn btn-secondary">Start Adventure!</a>
                    </div>
                  </article>';
          }
        }
        ?>
      </div>
    </section>

    <footer>
      <div class="container footer_container">
        <div class="footer_1">
          <a class="home_button" href="index.php"><h3>🎮 CodeKids</h3></a>
          <p>Making coding fun and accessible for everyone!  </p>
          <div class="mascot">
            <img src="images/mascot.png" alt="Codey the Robot" class="animate__animated animate__tada animate__infinite">
          </div>
        </div>
        <div class="footer_2">
          <h4>Learning Paths</h4>
          <ul class="permalinks">
            <li><a href="courses.php#beginner">Beginner Level</a></li>
            <li><a href="courses.php#intermediate">Intermediate Level</a></li>
            <li><a href="courses.php#advanced">Advanced Level</a></li>
            <li><a href="Quiz/exam.php">Skill Tests</a></li>
          </ul>
        </div>
        <div class="footer_3">
          <h4>Support</h4>
          <ul class="privacy">
            <li><a href="contact.php">Help Center</a></li>
            <li><a href="aboutUs.php">About Us</a></li>
            <li><a href="terms.php">Safety Guidelines</a></li>
          </ul>
        </div>
        <div class="footer_4">
          <h4>Connect With Us</h4>
          <p>✉️ hello@codekids.com</p>
          <p>📞 +1 (234) 567-8900</p>
          <ul class="footer_socials">
            <li><a href="#"><i class="uil uil-youtube"></i></a></li>
            <li><a href="#"><i class="uil uil-instagram-alt"></i></a></li>
            <li><a href="#"><i class="uil uil-github"></i></a></li>
          </ul>
        </div>
      </div>
      <div class="footer_copyright">
        <small>© 2023 CodeKids - Where Fun Meets Code! 🚀</small>
      </div>
    </footer>

    <script src="js/main.js"></script>
  </body>
</html>