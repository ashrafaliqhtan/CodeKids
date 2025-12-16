<?php
if(!isset($_SESSION)){
  session_start();
}
include('../dbConnection.php');
if(isset($_SESSION['is_admin_login'])){
  $adminEmail = $_SESSION['adminLogEmail'];
} else {
  echo "<script>location.href='index.php';</script>";
}

// Get stats data
$sql = "SELECT * FROM course";
$result = $conn->query($sql);
$totalcourse = $result->num_rows;

$sql = "SELECT * FROM students";
$result = $conn->query($sql);
$totalstu = $result->num_rows;

$sql = "SELECT * FROM courseorder";
$result = $conn->query($sql);
$totalsold = $result->num_rows;

// Get recent orders for the chart
$sql = "SELECT order_date, COUNT(*) as count FROM courseorder GROUP BY order_date ORDER BY order_date DESC LIMIT 7";
$orderData = $conn->query($sql);
$chartLabels = [];
$chartData = [];

while($row = $orderData->fetch_assoc()) {
    array_unshift($chartLabels, $row['order_date']);
    array_unshift($chartData, $row['count']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeKids | Admin Dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #560bad;
            
            --sidebar-width: 280px;
            --header-height: 70px;
            --card-radius: 12px;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
            overflow-x: hidden;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: white;
            height: 100vh;
            position: fixed;
            transition: all var(--transition-speed) ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header img {
            width: 40px;
            margin-right: 10px;
        }
        
        .sidebar-head {
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
        }
        
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 0;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-item.active {
            background: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        .sidebar-item.logout {
            color: #ff6b6b;
        }
        
        .sidebar-item.logout:hover {
            background: rgba(255, 107, 107, 0.1);
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all var(--transition-speed) ease;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .header h1 {
            font-size: 1.5rem;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
        }
        
        .stat-card.courses::before {
            background: var(--accent-color);
        }
        
        .stat-card.students::before {
            background: var(--success-color);
        }
        
        .stat-card.sales::before {
            background: var(--warning-color);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .stat-card .action {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .stat-card .action:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Charts Section */
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 992px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .chart-card h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        /* Recent Orders */
        .recent-orders {
            background: white;
            border-radius: var(--card-radius);
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .recent-orders h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 500;
            color: #495057;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            font-size: 0.9rem;
            color: #495057;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: #f1f1f1;
        }
        
        .action-btn.delete {
            color: #ef4444;
        }
        
        .action-btn.edit {
            color: #3b82f6;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header span, .sidebar-item span {
                display: none;
            }
            
            .sidebar-header {
                justify-content: center;
                padding: 20px 0;
            }
            
            .sidebar-item {
                justify-content: center;
                padding: 15px 0;
            }
            
            .sidebar-item i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 10px;
            }
        }
        
        
        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            background: white;
            border-radius: var(--card-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .header h1 {
            font-size: 1.5rem;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .header-menu {
            display: flex;
            align-items: center;
        }
        
        .header-menu-item {
            margin-left: 20px;
            position: relative;
            cursor: pointer;
        }
        
        .header-menu-item i {
            font-size: 1.2rem;
            color: var(--dark-color);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--warning-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            margin-left: 20px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1);
            z-index: 1;
            border-radius: var(--card-radius);
            overflow: hidden;
        }
        
        .dropdown-content a {
            color: var(--dark-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        
        .user-dropdown:hover .dropdown-content {
            display: block;
        }
                
        
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="./pic/logo.png" alt="CodeKids Logo">
                <span class="sidebar-head">CodeKids</span>
            </div>
            
            <div class="sidebar-menu">
                <a href="adminDashboard.php" class="sidebar-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="courses.php" class="sidebar-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Courses</span>
                </a>
                
                <a href="lesson.php" class="sidebar-item">
                    <i class="fas fa-book-open"></i>
                    <span>Lessons</span>
                </a>
                
                
                <a href="addgame.php" class="sidebar-item">
                    <i class="fas fa-book-open"></i>
                    <span>Add game</span>
                </a>
                
                <a href="students.php" class="sidebar-item">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                
                <a href="sellReport.php" class="sidebar-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sell Report</span>
                </a>
                
                <a href="paymentStatus.php" class="sidebar-item">
                    <i class="fas fa-table"></i>
                    <span>Payment</span>
                </a>
                
                <a href="admin_quiz.php" class="sidebar-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Quizzes</span>
                </a>
                
                <a href="feedback.php" class="sidebar-item">
                    <i class="fas fa-comment-alt"></i>
                    <span>Feedback</span>
                </a>
                
                <a href="adminChangePassword.php" class="sidebar-item">
                    <i class="fas fa-key"></i>
                    <span>Password</span>
                </a>
                
                <a href="../logout.php" class="sidebar-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="header-menu">
                    <div class="header-menu-item">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="header-menu-item">
                        <i class="fas fa-envelope"></i>
                        <span class="notification-badge">5</span>
                    </div>
<div class="user-dropdown">
    <div class="user-info">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($adminEmail ?? 'Admin'); ?>&background=random" alt="Admin">
        <span><?php echo $adminEmail ? explode('@', $adminEmail)[0] : 'Admin'; ?></span>
        <i class="fas fa-chevron-down" style="margin-left: 5px; font-size: 0.8rem;"></i>
    </div>
    <div class="dropdown-content">
        <a href="#"><i class="fas fa-user"></i> Profile</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card courses">
                    <h3>Total Courses</h3>
                    <div class="value"><?php echo $totalcourse; ?></div>
                    <a href="courses.php" class="action">View Courses</a>
                </div>
                
                <div class="stat-card students">
                    <h3>Registered Students</h3>
                    <div class="value"><?php echo $totalstu; ?></div>
                    <a href="students.php" class="action">View Students</a>
                </div>
                
                <div class="stat-card sales">
                    <h3>Courses Sold</h3>
                    <div class="value"><?php echo $totalsold; ?></div>
                    <a href="sellReport.php" class="action">View Sales</a>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <h2>Recent Sales</h2>
                    <canvas id="salesChart" height="250"></canvas>
                </div>
                
                <div class="chart-card">
                    <h2>Sales Distribution</h2>
                    <canvas id="doughnutChart" height="250"></canvas>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="recent-orders">
                <h2>Recent Course Orders</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Course</th>
                                <th>Student Email</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Modified query to remove payment_status if it doesn't exist
                            $sql = "SELECT co.order_id, c.course_name, co.stu_email, co.order_date, co.amount 
                                    FROM courseorder co
                                    JOIN course c ON co.course_id = c.course_id
                                    ORDER BY co.order_date DESC LIMIT 7";
                            $result = $conn->query($sql);
                            
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>#' . $row["order_id"] . '</td>';
                                    echo '<td>' . htmlspecialchars($row["course_name"]) . '</td>';
                                    echo '<td>' . $row["stu_email"] . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($row["order_date"])) . '</td>';
                                    echo '<td>$' . number_format($row["amount"], 2) . '</td>';
                                    
                                    echo '<td>
                                            <form action="" method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="' . $row["order_id"] . '">
                                                <button type="submit" class="action-btn delete" name="delete" value="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="text-align:center;">No orders found</td></tr>';
                            }
                            
                            if(isset($_REQUEST['delete'])) {
                                $sql = "DELETE FROM courseorder WHERE order_id = {$_REQUEST['id']}";
                                if($conn->query($sql)) {
                                    echo '<script>window.location.href = window.location.href;</script>';
                                } else {
                                    echo '<script>alert("Unable to delete record");</script>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Courses Sold',
                    data: <?php echo json_encode($chartData); ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        
        // Doughnut Chart (example data - you would replace with real data)
        const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
        const doughnutChart = new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Beginner Courses', 'Intermediate Courses', 'Advanced Courses'],
                datasets: [{
                    data: [45, 30, 25],
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(73, 144, 239, 0.8)',
                        'rgba(76, 201, 240, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>