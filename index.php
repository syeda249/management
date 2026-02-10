<?php
session_start();
include 'db.php';

// 1. Session Check: Agar login nahi hai toh login page par bhejein
if (!isset($_SESSION['role'])) { 
    header("Location: login.php"); 
    exit(); 
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$today = date('Y-m-d');

// 2. Fetch School Settings Safely (Fixes Line 11 Error)
$school_res = $conn->query("SELECT school_name FROM school_settings WHERE id=1");
$school_data = ($school_res && $school_res->num_rows > 0) ? $school_res->fetch_assoc() : null;
$display_name = $school_data['school_name'] ?? 'SCHOOL PORTAL';

// 3. Fetch Statistics for Dashboard Cards
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0;
$total_teachers = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'] ?? 0;

// Attendance Stats for Today
$present_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = '$today' AND status IN ('Present', 'Late')")->fetch_assoc()['count'] ?? 0;
$absent_today = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE attendance_date = '$today' AND status = 'Absent'")->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo htmlspecialchars($display_name); ?></title>
    <style>
        :root { 
            --primary: #2c3e50; 
            --accent: #3498db; 
            --bg: #f4f7f6; 
            --white: #ffffff;
            --success: #2ecc71;
            --danger: #e74c3c;
        }
        
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        /* Layout Fix: Sidebar ke liye space chorni hai */
        .main-content { 
            margin-left: 260px; /* Sidebar width + space */
            flex: 1; 
            padding: 30px; 
            min-height: 100vh;
        }

        /* Top Header Bar */
        .header {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        /* Dashboard Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
            border-bottom: 4px solid var(--accent);
        }

        .card:hover { transform: translateY(-5px); }
        .card h3 { margin: 0; color: #7f8c8d; font-size: 16px; text-transform: uppercase; }
        .card .value { font-size: 36px; font-weight: bold; margin: 10px 0; color: var(--primary); }

        .btn-report {
            background: var(--accent);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .welcome-msg h1 { margin: 0; font-size: 24px; color: var(--primary); }
        .welcome-msg p { margin: 5px 0 0; color: #666; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <div class="welcome-msg">
                <h1>Welcome back, <span style="color:var(--accent);"><?php echo strtoupper($username); ?></span></h1>
                <p>Role: <?php echo $role; ?></p>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: bold; color: var(--primary);"><?php echo date('l, d F Y'); ?></div>
                <div id="digital-clock" style="color: var(--accent); font-weight: 600;"></div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card">
                <h3>Total Students</h3>
                <div class="value"><?php echo $total_students; ?></div>
                <p style="color: #666; font-size: 13px;">Registered in system</p>
            </div>

            <div class="card" style="border-color: var(--success);">
                <h3>Today Present</h3>
                <div class="value" style="color: var(--success);"><?php echo $present_today; ?></div>
                <p style="color: #666; font-size: 13px;">Students marked present</p>
            </div>

            <div class="card" style="border-color: var(--danger);">
                <h3>Today Absent</h3>
                <div class="value" style="color: var(--danger);"><?php echo $absent_today; ?></div>
                <p style="color: #666; font-size: 13px;">Students not in school</p>
            </div>

            <div class="card">
                <h3>Total Teachers</h3>
                <div class="value"><?php echo $total_teachers; ?></div>
                <p style="color: #666; font-size: 13px;">Active staff members</p>
            </div>
        </div>

        <div style="margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card" style="text-align: left;">
                <h3>Attendance Overview</h3>
                <p>Check detailed reports for monthly attendance and performance.</p>
                <a href="attendance_report.php" class="btn-report">View Full Report</a>
            </div>
            <div class="card" style="text-align: left; border-color: #f39c12;">
                <h3>Fee Management</h3>
                <p>Review pending fees and generate slips for the current month.</p>
                <a href="fee_management.php" class="btn-report" style="background:#f39c12;">Manage Fees</a>
            </div>
        </div>
    </div>

    <script>
        // Real-time Digital Clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString();
            document.getElementById('digital-clock').innerText = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

</body>
</html>