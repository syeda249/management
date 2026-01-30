<?php
session_start();
include 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'];

// Fetch counts for the Stats Cards
$student_count = $conn->query("SELECT id FROM students")->num_rows;
$teacher_count = $conn->query("SELECT id FROM teachers")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Dashboard</title>
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
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            background: #1a252f;
            font-weight: bold;
            font-size: 20px;
        }

        .sidebar-menu {
            padding: 10px 0;
        }

        .menu-item {
            padding: 15px 25px;
            text-decoration: none;
            color: #bdc3c7;
            display: block;
            border-left: 4px solid transparent;
            transition: 0.2s;
        }

        .menu-item:hover {
            background-color: var(--secondary-bg);
            color: white;
            border-left: 4px solid var(--accent-color);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
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
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            text-align: center;
        }

        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: var(--primary-bg); }

        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .role-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            vertical-align: middle;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">SCHOOL MANAGER</div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">🏠 Dashboard</a>
            
            <?php if ($user_role == 'super_admin' || $user_role == 'admin'): ?>
                <a href="manage_teachers.php" class="menu-item">👨‍🏫 Manage Teachers</a>
               <a href="manage_staff.php" class="menu-item">🛠️ Manage Staff</a>
               
            <?php endif; ?>

           <a href="manage_students.php" class="menu-item">🎓 Manage Students</a>
  <a href="attendance.php" class="menu-item">📅 Attendance</a>
           
            <a href="fees.php" class="menu-item">💰 Fee Management</a>
            <a href="setting.php" class="menu-item">⚙️ Profile Settings</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>
                Welcome, <strong><?php echo htmlspecialchars($username); ?></strong> 
                <span class="role-badge"><?php echo strtoupper($user_role); ?></span>
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
                <p>12</p>
            </div>
        </div>

        <div class="welcome-section">
            <h2>Portal Overview</h2>
            <p>Select an option from the sidebar to manage school operations. Your access is restricted based on your <strong><?php echo $user_role; ?></strong> permissions.</p>
            <hr>
            <ul>
                <li><strong>Super Admin:</strong> Full system control.</li>
                <li><strong>Admin:</strong> Manage users and staff.</li>
                <li><strong>Manager:</strong> Handle students and classes.</li>
                <li><strong>Teacher:</strong> Manage grades and attendance.</li>
            </ul>
        </div>
    </div>

</body>
</html>