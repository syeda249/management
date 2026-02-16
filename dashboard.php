<?php
session_start();
include('db.php');

// 1. Redirect to login if not logged in (Strict Check)
// Check if EITHER user_id (Staff) or student_id (Student) is set
if (!isset($_SESSION['user_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Fix for Line 12: Safely fetch Role and Username
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';

// Check staff username first, then student name
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} elseif (isset($_SESSION['student_name'])) {
    $username = $_SESSION['student_name'];
} else {
    $username = "User";
}

// 3. Fetch counts for the Stats Cards
$student_count_res = $conn->query("SELECT id FROM students");
$student_count = ($student_count_res) ? $student_count_res->num_rows : 0;

$teacher_count_res = $conn->query("SELECT id FROM teachers");
$teacher_count = ($teacher_count_res) ? $teacher_count_res->num_rows : 0;

$class_count = 14; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #2c3e50;
            --secondary-bg: #34495e;
            --accent-color: #3498db;
            --light-text: #ecf0f1;
            --body-bg: #f4f7f6;
            --card-white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            display: flex;
            background-color: var(--body-bg);
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: var(--primary-bg);
            color: var(--light-text);
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            background: #1a252f;
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 1px;
        }

        .sidebar-menu { padding: 10px 0; }

        .menu-item {
            padding: 15px 25px;
            text-decoration: none;
            color: #bdc3c7;
            display: block;
            border-left: 4px solid transparent;
            transition: 0.2s;
            font-size: 14px;
        }

        .menu-item:hover, .active {
            background-color: var(--secondary-bg);
            color: white;
            border-left: 4px solid var(--accent-color);
        }

        .menu-item i { margin-right: 10px; width: 20px; text-align: center; }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card p { margin: 10px 0 0; font-size: 32px; font-weight: bold; color: var(--primary-bg); }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--accent-color);
        }

        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .role-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            transition: 0.3s;
        }
        .logout-btn:hover { background: #c0392b; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">🏫 SCHOOL MANAGER</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active"><i class="fas fa-th-large"></i> Dashboard</a>
            
            <?php if ($user_role == 'super_admin' || $user_role == 'admin'): ?>
                <a href="manage_teachers.php" class="menu-item"><i class="fas fa-chalkboard-teacher"></i> Teachers</a>
                <a href="manage_staff.php" class="menu-item"><i class="fas fa-users-cog"></i> Staff Members</a>
            <?php endif; ?>

            <a href="manage_students.php" class="menu-item"><i class="fas fa-user-graduate"></i> Students</a>
            <a href="attendance.php" class="menu-item"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="attendance_report.php" class="menu-item"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="fees.php" class="menu-item"><i class="fas fa-wallet"></i> Fees</a>
            <a href="setting.php" class="menu-item"><i class="fas fa-user-shield"></i> Settings</a>
            <a href="logout.php" class="menu-item" style="color:#ff7675;"><i class="fas fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                Welcome, <strong><?php echo htmlspecialchars($username); ?></strong> 
                <span class="role-badge"><?php echo htmlspecialchars($user_role); ?></span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Students</h3>
                <p><?php echo $student_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Teachers</h3>
                <p><?php echo $teacher_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Classes</h3>
                <p><?php echo $class_count; ?></p>
            </div>
        </div>

        <div class="welcome-section">
            <h2>Portal Statistics</h2>
            <p>Select an option from the sidebar to manage school operations. Your access is restricted based on your <strong><?php echo htmlspecialchars($user_role); ?></strong> permissions.</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            
            

            <h3>System Status:</h3>
            <ul style="color: #555; line-height: 1.8;">
                <li><strong>Database:</strong> Connected <i class="fas fa-check-circle" style="color: #2ecc71;"></i></li>
                <li><strong>Current User:</strong> <?php echo htmlspecialchars($username); ?></li>
                <li><strong>Session:</strong> Active</li>
            </ul>
        </div>
    </div>

</body>
</html>