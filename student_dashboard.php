<?php
session_start();
include 'db.php';

// 1. Session Protection
if(!isset($_SESSION['student_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$sid = $_SESSION['student_id'];

// 2. Fetch Student Info
$st_query = mysqli_query($conn, "SELECT * FROM students WHERE id='$sid'");
$student = mysqli_fetch_assoc($st_query);
$stu_class_name = $student['class']; // Masal ke tor par '6th' ya '7th'

// 3. Attendance Calculation
$total_days_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE student_id='$sid'");
$present_days_res = mysqli_query($conn, "SELECT COUNT(*) as presents FROM attendance WHERE student_id='$sid' AND status='P'");

$total_days = mysqli_fetch_assoc($total_days_res)['total'];
$present_days = mysqli_fetch_assoc($present_days_res)['presents'];
$attendance_percentage = ($total_days > 0) ? round(($present_days / $total_days) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | <?php echo $student['name']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #1a73e8; --sidebar: #2c3e50; --bg: #f8fafc; --success: #22c55e; --danger: #ef4444; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid #3e4f5f; }
        .sidebar-header img { width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--primary); margin-bottom: 10px; }
        .sidebar-menu a { display: block; padding: 14px 20px; color: #cbd5e1; text-decoration: none; cursor: pointer; transition: 0.3s; }
        .sidebar-menu a:hover, .active-tab { background: #34495e; color: white; border-left: 4px solid var(--primary); }
        .sidebar-menu i { margin-right: 10px; width: 20px; }

        .main-content { margin-left: 260px; padding: 40px; width: 100%; box-sizing: border-box; }
        .section { display: none; animation: fadeIn 0.4s ease; }
        .active-section { display: block; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; background: #f1f5f9; padding: 12px; color: #475569; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .progress-bg { background: #e2e8f0; height: 10px; border-radius: 5px; width: 100%; margin: 10px 0; }
        .progress-fill { height: 100%; border-radius: 5px; background: var(--success); transition: 0.5s; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .status-p { background: #dcfce7; color: #166534; }
        .status-f { background: #fee2e2; color: #991b1b; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['name']); ?>&background=random">
        <h4 style="margin: 0;"><?php echo $student['name']; ?></h4>
        <p style="font-size:12px; color:#94a3b8; margin:5px 0;">Class: <?php echo $student['class']; ?></p>
    </div>
    <div class="sidebar-menu">
        <a onclick="showTab('dashboard')" id="btn-dashboard" class="active-tab"><i class="fas fa-home"></i> Overview</a>
        <a onclick="showTab('attendance')" id="btn-attendance"><i class="fas fa-calendar-check"></i> Attendance</a>
        <a onclick="showTab('summary')" id="btn-summary"><i class="fas fa-chalkboard-teacher"></i> Subject Summary</a>
        <a onclick="showTab('classtests')" id="btn-classtests"><i class="fas fa-file-invoice"></i> Test Results</a>
        <a href="logout.php" style="color: #fb7185;"><i class="fas fa-power-off"></i> Logout</a>
    </div>
</div>

<div class="main-content">

    <div id="dashboard" class="section active-section">
        <div class="card">
            <h2>Welcome, <?php echo $student['name']; ?>! 👋</h2>
            <p style="color:#64748b;">Aapka aaj ka academic status niche diya gaya hai.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3>Attendance Summary</h3>
                <h1 style="color:var(--primary); margin:0;"><?php echo $attendance_percentage; ?>%</h1>
                <div class="progress-bg"><div class="progress-fill" style="width:<?php echo $attendance_percentage; ?>%"></div></div>
                <small>Total Days: <?php echo $total_days; ?> | Present: <?php echo $present_days; ?></small>
            </div>
            <div class="card">
                <h3>Current Class</h3>
                <h1 style="margin:0; color:#334155;"><?php echo $student['class']; ?></h1>
                <p style="color:#64748b;">Roll No: <?php echo $student['roll_no']; ?></p>
            </div>
        </div>
    </div>

    <div id="attendance" class="section">
        <div class="card">
            <h3><i class="fas fa-calendar-alt"></i> Attendance History</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    $att_log = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id='$sid' ORDER BY date DESC LIMIT 10");
                    while($row = mysqli_fetch_assoc($att_log)) {
                        $s_class = ($row['status'] == 'P') ? 'status-p' : 'status-f';
                        $s_text = ($row['status'] == 'P') ? 'Present' : 'Absent';
                        echo "<tr><td>{$row['date']}</td><td><span class='badge $s_class'>$s_text</span></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="summary" class="section">
        <div class="card">
            <h3><i class="fas fa-graduation-cap"></i> My Subjects & Teachers</h3>
            <p style="font-size: 13px; color: #64748b;">List of teachers assigned to Class: <b><?php echo $stu_class_name; ?></b></p>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Assigned Teacher</th>
                        <th>Schedule (Day)</th>
                        <th>Timing</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // FIXED QUERY: Linking tables using classes table to match class_name correctly
                    $summary_sql = "SELECT ts.*, t.name as teacher_name 
                                    FROM teacher_schedule ts 
                                    INNER JOIN teachers t ON ts.teacher_id = t.id 
                                    INNER JOIN classes c ON ts.class_id = c.id 
                                    WHERE c.class_name = '$stu_class_name'"; 
                    
                    $summary_res = mysqli_query($conn, $summary_sql);

                    if($summary_res && mysqli_num_rows($summary_res) > 0) {
                        while($row = mysqli_fetch_assoc($summary_res)) {
                            echo "<tr>
                                    <td><b style='color:var(--primary);'>" . ucfirst($row['subject']) . "</b></td>
                                    <td>
                                        <div style='display:flex; align-items:center; gap:10px;'>
                                            <img src='https://ui-avatars.com/api/?name=".urlencode($row['teacher_name'])."&size=30&rounded=true' />
                                            <span>{$row['teacher_name']}</span>
                                        </div>
                                    </td>
                                    <td>{$row['day']}</td>
                                    <td><code style='background:#f1f5f9; padding:2px 5px;'>{$row['time_slot']}</code></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#94a3b8;'>No subjects assigned to your class yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="classtests" class="section">
        <div class="card">
            <h3><i class="fas fa-poll-h"></i> Examination Marks</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Subject</th><th>Type</th><th>Marks</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    $marks_sql = "SELECT * FROM marks WHERE student_id = '$sid' ORDER BY date DESC";
                    $marks_res = mysqli_query($conn, $marks_sql);
                    while($m = mysqli_fetch_assoc($marks_res)) {
                        $res_status = ($m['marks'] >= 15) ? 'Pass' : 'Fail';
                        $res_class = ($res_status == 'Pass') ? 'status-p' : 'status-f';
                        echo "<tr>
                                <td>{$m['date']}</td>
                                <td><b>".ucfirst($m['subject'])."</b></td>
                                <td>{$m['exam_type']}</td>
                                <td>{$m['marks']}</td>
                                <td><span class='badge $res_class'>$res_status</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function showTab(id) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active-section'));
        document.getElementById(id).classList.add('active-section');
        document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active-tab'));
        document.getElementById('btn-'+id).classList.add('active-tab');
    }
</script>

</body>
</html>