<?php
// Secure session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'use_strict_mode' => true
    ]);
}

require_once __DIR__ . '/../dbConnection.php';

// Enhanced security check
if (!isset($_SESSION['is_admin_login']) || !$_SESSION['is_admin_login']) {
    header('Location: ../index.php');
    exit();
}

$adminEmail = $_SESSION['adminLogEmail'] ?? '';

// Modern date validation function
function validateDateInput($date) {
    if (empty($date)) {
        return false;
    }
    
    // Check date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    
    // Create date object and verify
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report | CodeKids Admin</title>
    
    <!-- Modern CSS Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="./pic/logo.png" type="image/png">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #f43f5e;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        [data-theme="dark"] {
            --primary: #818cf8;
            --dark: #f8fafc;
            --light: #1e293b;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            transition: all 0.3s ease;
        }
        
        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        
        /* Modern Sidebar */
        .sidebar {
            background: var(--primary);
            color: white;
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: var(--card-shadow);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .sidebar-logo img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }
        
        .sidebar-menu {
            padding: 0 1rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            color: white;
            transition: all 0.2s ease;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            transform: translateX(4px);
        }
        
        .sidebar-menu a i {
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            padding: 2rem;
        }
        
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .theme-toggle {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            background: var(--primary-hover);
            transform: rotate(30deg);
        }
        
        /* Report Card */
        .report-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .date-range-form {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .date-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .date-input-group label {
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-search:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        
        .results-table th {
            background: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
        }
        
        .results-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .results-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-badge.paid {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .status-badge.failed {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .total-row {
            font-weight: 700;
            background: rgba(99, 102, 241, 0.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #0ea371;
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                height: auto;
                position: relative;
            }
            
            .date-range-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Modern Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="#" class="sidebar-logo">
                    <img src="./pic/logo.png" alt="CodeKids Logo">
                    <span>CodeKids</span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <a href="adminDashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="courses.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Courses</span>
                </a>
                <a href="lesson.php">
                    <i class="fas fa-book-open"></i>
                    <span>Lessons</span>
                </a>
                <a href="students.php">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a href="sellReport.php" class="active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Sell Report</span>
                </a>
                <a href="#">
                    <i class="fas fa-table"></i>
                    <span>Payment</span>
                </a>
                <a href="feedback.php">
                    <i class="fas fa-comment-alt"></i>
                    <span>Feedback</span>
                </a>
                <a href="adminChangePassword.php">
                    <i class="fas fa-key"></i>
                    <span>Password</span>
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header-bar">
                <h1 class="page-title">Sales Report</h1>
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            
            <div class="report-card">
                <form method="POST" class="date-range-form">
                    <div class="date-input-group">
                        <label for="startdate">From Date</label>
                        <input type="date" id="startdate" name="startdate" required>
                    </div>
                    
                    <div class="date-input-group">
                        <label for="enddate">To Date</label>
                        <input type="date" id="enddate" name="enddate" required>
                    </div>
                    
                    <button type="submit" name="searchsubmit" class="btn-search">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </form>
                
                <?php
                if (isset($_REQUEST['searchsubmit'])) {
                    // Modern input validation
                    $startdate = $_REQUEST['startdate'] ?? '';
                    $enddate = $_REQUEST['enddate'] ?? '';
                    
                    // Validate dates
                    if (!validateDateInput($startdate) || !validateDateInput($enddate)) {
                        echo '<div class="empty-state">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Invalid Date Format</h3>
                                <p>Please enter valid dates in YYYY-MM-DD format.</p>
                              </div>';
                    } elseif ($startdate > $enddate) {
                        echo '<div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>Date Range Error</h3>
                                <p>Start date cannot be after end date.</p>
                              </div>';
                    } else {
                        // Prepare SQL statement with proper parameter binding
                        $sql = "SELECT * FROM courseorder WHERE order_date BETWEEN ? AND ?";
                        $stmt = $conn->prepare($sql);
                        
                        if ($stmt) {
                            $stmt->bind_param("ss", $startdate, $enddate);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $totalAmount = 0;
                                echo '<div class="table-responsive">
                                        <table class="results-table">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Course ID</th>
                                                    <th>Student Email</th>
                                                    <th>Status</th>
                                                    <th>Order Date</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                                
                                while ($row = $result->fetch_assoc()) {
                                    $totalAmount += $row['amount'];
                                    $statusClass = strtolower($row['status']);
                                    echo '<tr>
                                            <td>' . htmlspecialchars($row["order_id"]) . '</td>
                                            <td>' . htmlspecialchars($row["course_id"]) . '</td>
                                            <td>' . htmlspecialchars($row["stu_email"]) . '</td>
                                            <td><span class="status-badge ' . $statusClass . '">' . 
                                                htmlspecialchars($row["status"]) . '</span></td>
                                            <td>' . htmlspecialchars($row["order_date"]) . '</td>
                                            <td>$' . number_format($row["amount"], 2) . '</td>
                                          </tr>';
                                }
                                
                                echo '<tr class="total-row">
                                        <td colspan="5" class="text-right">Total</td>
                                        <td>$' . number_format($totalAmount, 2) . '</td>
                                      </tr>
                                      </tbody>
                                      </table>
                                      </div>
                                      
                                      <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="window.print()">
                                            <i class="fas fa-print"></i> Print Report
                                        </button>
                                        <button class="btn btn-success" onclick="exportToExcel()">
                                            <i class="fas fa-file-excel"></i> Export to Excel
                                        </button>
                                      </div>';
                            } else {
                                echo '<div class="empty-state">
                                        <i class="fas fa-chart-pie"></i>
                                        <h3>No Data Found</h3>
                                        <p>No sales records found for the selected date range.</p>
                                      </div>';
                            }
                            
                            $stmt->close();
                        } else {
                            echo '<div class="empty-state">
                                    <i class="fas fa-database"></i>
                                    <h3>Database Error</h3>
                                    <p>Could not prepare the database query.</p>
                                  </div>';
                        }
                    }
                } else {
                    // Default empty state when page first loads
                    echo '<div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>Generate Sales Report</h3>
                            <p>Select a date range to view sales data</p>
                          </div>';
                }
                ?>
            </div>
        </main>
    </div>
    
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or use preferred color scheme
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            const icon = themeToggle.querySelector('i');
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        
        // Set default dates (last 30 days)
        document.addEventListener('DOMContentLoaded', function() {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            // Format dates as YYYY-MM-DD
            const formatDate = (date) => date.toISOString().split('T')[0];
            
            document.getElementById('enddate').value = formatDate(endDate);
            document.getElementById('startdate').value = formatDate(startDate);
        });
        
        // Export to Excel function
        function exportToExcel() {
            // Simple implementation - in a real app you would use a library like SheetJS
            let csv = 'Order ID,Course ID,Student Email,Status,Order Date,Amount\n';
            
            document.querySelectorAll('.results-table tr:not(.total-row)').forEach(row => {
                if (row.cells.length === 6) {
                    const cells = Array.from(row.cells).map(cell => {
                        // Handle status badge if present
                        const badge = cell.querySelector('.status-badge');
                        return badge ? badge.textContent : cell.textContent;
                    });
                    csv += cells.join(',') + '\n';
                }
            });
            
            // Create download link
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `sales-report-${new Date().toISOString().split('T')[0]}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>