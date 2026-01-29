<?php
session_start();
include 'db.php';

// 1. Session Check
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$user_role = $_SESSION['role'];
$username = $_SESSION['username'] ?? 'User';
$today = date('Y-m-d');

// 2. School Name Fetch (Added Error Suppression)
$display_name = 'SCHOOL MANAGER';
$school_res = $conn->query("SELECT school_name FROM school_settings LIMIT 1");
if ($school_res && $school_res->num_rows > 0) {
    $school_data = $school_res->fetch_assoc();
    $display_name = $school_data['school_name'];
}

// 3. Stats Fetching (Fixed SQL Queries)
$student_q = $conn->query("SELECT id FROM students");
$student_count = ($student_q) ? $student_q->num_rows : 0;

$teacher_q = $conn->query("SELECT id FROM teachers");
$teacher_count = ($teacher_q) ? $teacher_q->num_rows : 0;

// 4. Attendance Logic (Checked both common column names)
$today_att = ['Present' => 0, 'Absent' => 0];

// First attempt with 'date'
$att_stats = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE date = '$today' GROUP BY status");

// Second attempt with 'attendance_date' if the first fails
if (!$att_stats) {
    $att_stats = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE attendance_date = '$today' GROUP BY status");
}

if ($att_stats) {
    while($row = $att_stats->fetch_assoc()) {
        $status = $row['status'];
        if ($status == 'Present' || $status == 'Late') {
            $today_att['Present'] += $row['count'];
        } elseif ($status == 'Absent') {
            $today_att['Absent'] += $row['count'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $display_name; ?> - Dashboard</title>
    <style>
        :root {
            --primary-bg: #2c3e50; --secondary-bg: #34495e; --accent-color: #3498db;
            --light-text: #ecf0f1; --body-bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c;
        }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background-color: var(--body-bg); }
        .sidebar { width: 250px; background-color: var(--primary-bg); color: var(--light-text); height: 100vh; position: fixed; display: flex; flex-direction: column; z-index: 1000; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; }
        .menu-item { padding: 15px 25px; text-decoration: none; color: #bdc3c7; display: block; transition: 0.3s; }
        .menu-item:hover, .active-link { background-color: var(--secondary-bg); color: white; border-left: 4px solid var(--accent-color); }
        .main-content { margin-left: 250px; flex: 1; padding: 30px; }
        .top-bar { background: white; padding: 18px 30px; margin-bottom: 30px; border-radius: 8px; display: flex; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid var(--accent-color); }
        .stat-card p { margin: 10px 0 0; font-size: 32px; font-weight: bold; }
        .time-card { background: linear-gradient(135deg, var(--primary-bg), #1a252f); color: white; padding: 25px; border-radius: 15px; text-align: center; }
        .role-badge { background: var(--accent-color); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><?php echo $display_name; ?></div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active-link">🏠 Dashboard</a>
            <a href="manage_teachers.php" class="menu-item">👨‍🏫 Manage Teachers</a>
            <a href="manage_staff.php" class="menu-item">🛠️ Manage Staff</a>
            <a href="manage_students.php" class="menu-item">🎓 Manage Students</a>
            <a href="attendance.php" class="menu-item">📅 Daily Attendance</a>
            <a href="attendance_report.php" class="menu-item">📊 Attendance Report</a>
            <a href="index.php?logout=1" class="menu-item" style="color: #e74c3c;">🚪 Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div>Welcome back, <strong><?php echo $username; ?></strong> <span class="role-badge"><?php echo strtoupper($user_role); ?></span></div>
            <div style="color: #888;"><?php echo date('l, d F Y'); ?></div>
        </div>

        <div class="stats-container">
            <div class="stat-card"><h3>Total Students</h3><p><?php echo $student_count; ?></p></div>
            <div class="stat-card" style="border-top-color: var(--success);"><h3>Today Present</h3><p style="color: var(--success);"><?php echo $today_att['Present']; ?></p></div>
            <div class="stat-card" style="border-top-color: var(--danger);"><h3>Today Absent</h3><p style="color: var(--danger);"><?php echo $today_att['Absent']; ?></p></div>
            <div class="stat-card"><h3>Total Teachers</h3><p><?php echo $teacher_count; ?></p></div>
        </div>

        <div class="time-card">
            <div id="digital-clock" style="font-size: 40px; font-weight: bold;">00:00:00</div>
            <p>Current System Time</p>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('digital-clock').innerText = now.toLocaleTimeString();
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</body>
</html>