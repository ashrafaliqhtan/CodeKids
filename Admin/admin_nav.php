<?php
// admin/admin_nav.php
if(!isset($_SESSION['is_admin_login'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .admin-nav {
            background: #2e3267;
            padding: 1rem;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .nav-links a {
            color: white;
            margin-left: 1rem;
            text-decoration: none;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-container">
            <h2>CodeKids Admin</h2>
            <div class="nav-links">
                <a href="adminDashboard.php">Dashboard</a>
                <a href="admin_quiz.php">Quizzes</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>