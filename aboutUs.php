<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous"> -->
    <link
      rel="stylesheet"
      href="https://unicons.iconscout.com/release/v4.0.0/css/line.css"
    />
    <link rel="shortcut icon" type="image/png" href="images/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" />
    <!-- SwiperJS -->
    <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"
/>
<link rel="stylesheet" href="css/aboutUs.css">
<style>
  nav {
     background: var(--color-bg1);
      }
      .nav_menu a {
        color: white;
      }
      nav button {
        color: white;
      }
</style>
    <title>CodeKids | Let's Crack the Code</title>
  </head>
  <body>

    <!-- ==========================================================NavBar================================================================= -->
  
    <nav>
      <div class="container nav_container">
        <a class="home_button" href="index.php"><h3>CodeKids</h3></a>
        <ul class="nav_menu">
          <li><a href="loginSignUp.php">Login</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="paymentStatus.php">Payment Status</a></li>
          <li><a href="loginSignUp.php">Register</a></li>
        </ul>
        <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
        <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
      </div>
    </nav>
  
    <!-- =========================================================End of NavBar========================================================= -->
  
   <!-- =======================================================Achievement Section====================================================== -->

    <section class="about_achievements">
      <div class="container about_achievements-container">
        <div class="about__achievements-left">
          <img src="images/aboutImages/achievements.svg" />
        </div>
        <div class="about__achievements-right">
          <h1>Acheivements</h1>
          <p>
            We are proud to present our achievement in developing the distributed e-learning system CodeKids for children, which is part of the graduation project for the Bachelor’s degree in the Department of Computer Science at Jazan University. The system was developed using PHP, MySQL, HTML, CSS, and JAVASCRIPT. Our system includes essential features such as simplified and entertaining educational lessons, practice tests, discussions, and PDF integration, which revolutionizes the way children learn. By implementing interactive practice tests, children can assess their understanding of various topics. The structured notes feature allows access to and retrieval of study materials efficiently.
          </p>
          <div class="achievements_cards">
            <article class="achievement_card">
              <span class="achievement_icon">
                <i class="bi bi-camera-video"></i>
              </span>
              <h3>10+</h3>
              <p>Courses</p>
            </article>

            <article class="achievement_card">
              <span class="achievement_icon">
                <i class="bi bi-people"></i>
              </span>
              <h3>10+</h3>
              <p>Students</p>
            </article>

            <article class="achievement_card">
              <span class="achievement_icon">
                <i class="bi bi-award"></i>
              </span>
              <h3>5+</h3>
              <p>Teachers</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="team">
      <h2>Meet Our Team</h2>
      <div class="container team__container">
        <article class="team__member">
          <div class="team__member-image">
            <img src="images/aboutImages/tm.jpg   " />
          </div>
          <div class="team__member-info">
            <h4>Taif </h4>
            <p>Tutor</p>
          </div>
          <div class="team__member-socials">
            <a href="">
              <i class="uil uil-instagram"></i
            ></a>
            <a href=""><i class="uil uil-github"></i></a>
            <a href=""><i class="uil uil-linkedin"></i></a>
          </div>
        </article>

        <article class="team__member">
          <div class="team__member-image">
            <img src="images/aboutImages/taif.jpg   " />
          </div>
          <div class="team__member-info">
            <h4>Binay Singh</h4>
            <p>Tutor</p>
          </div>
          <div class="team__member-socials">
            <a href="">
              <i class="uil uil-instagram"></i
            ></a>
            <a href=""><i class="uil uil-github"></i></a>
            <a href=""><i class="uil uil-linkedin"></i></a>
          </div>
        </article>
        <article class="team__member">
          <div class="team__member-image">
            <img src="images/aboutImages/t2.jpg   " />
          </div>
          <div class="team__member-info">
            <h4>Ishwar Singh Bhandari</h4>
            <p>Tutor</p>
          </div>
          <div class="team__member-socials">
            <a href="">
              <i class="uil uil-instagram"></i
            ></a>
            <a href=""><i class="uil uil-github"></i></a>
            <a href=""><i class="uil uil-linkedin"></i></a>
          </div>
        </article>
        
      </div>
    </section>

    <!-- ============================================================Achiements Section======================================================== -->
  
    <!-- =======================================================================Footer========================================================= -->
    
    <footer>
  <div class="container footer_container">
    <div class="footer_1">
      <a class="home_button" href="index.html"><h3>CodeKids</h3></a>
      <p>"Welcome to CodeKids, your one-stop destination for all things coding and technology."</p>
    </div>

     <div class="footer_2">
      <h4>Permalinks</h4>
      <ul class="permalinks">
        <li><a href="index.php">Home</a></li>
        <li><a href="aboutUs.php">About</a></li>
        <li><a href="courses.php">Courses</a></li>
        <li><a href="contact.php">Contact</a></li>
      </ul>
     </div>

     <div class="footer_3">
      <h4>Primacy</h4>
      <ul class="privacy">
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms and Conditions</a></li>
        <li><a href="#">Refund Policy</a></li>
      </ul>
     </div>

     <div class="footer_4">
      <h4>Contact Us</h4>
      <div>
        <p>+966 5123 456 789 </p>
        <p>Remote location</p>
      </div>
      <ul class="footer_socials">
        <li>
          <a href="#"><i class="uil uil-facebook-f"></i></a>
        </li>
        <li>
          <a href="#"><i class="uil uil-instagram-alt"></i></a>
        </li>
        <li>
          <a href="#"><i class="uil uil-twitter"></i></a>
        </li>
        <li> 
          <a href="#"><i class="uil uil-linkedin-alt"></i></a>
        </li>
      </ul>
     </div>
  </div>
  <div class="footer_copyright">
    <small>Copyright &copy; CodeKids || <a href="#adminPopup">Admin_Login</a></small>
   </div>
</footer>
<script src="js/main.js"></script>
  </body>
</html>
