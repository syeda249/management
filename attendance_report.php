<?php 
<<<<<<< Updated upstream
session_start();

// 1. Security Check: Agar login nahi hai to login page par bhej do
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('db.php');

// Filter setup
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_class = isset($_GET['class']) ? mysqli_real_escape_string($conn, $_GET['class']) : '';

// Exact School Sequence
$ordered_classes = ['Nursery', 'Prep', '1', '2', '3', '4', '5', '6', '7', '8', '9th', '10th', '1st year', '2nd year'];
=======
include('db.php');

$message = "";

// 1. DELETE STUDENT LOGIC
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM students WHERE id = '$id'");
    $message = "<div class='alert danger'>✅ Student record deleted successfully.</div>";
}

// 2. ADD NEW CLASS LOGIC
if (isset($_POST['add_class'])) {
    $c_name = mysqli_real_escape_string($conn, $_POST['new_class_name']);
    $section = mysqli_real_escape_string($conn, $_POST['section_type']);
    mysqli_query($conn, "INSERT INTO classes (class_name, section) VALUES ('$c_name', '$section')");
    $message = "<div class='alert success'>✅ New Class '$c_name' added to the system.</div>";
}

// 3. REGISTER NEW STUDENT
if (isset($_POST['register_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    // Auto Roll No Generation (Class wise)
    $res = mysqli_query($conn, "SELECT MAX(roll_no) as last_roll FROM students WHERE class = '$class'");
    $row = mysqli_fetch_assoc($res);
    $roll_no = ($row['last_roll'] > 0) ? $row['last_roll'] + 1 : 1;

    $query = "INSERT INTO students (name, class, roll_no, phone, password) VALUES ('$name', '$class', '$roll_no', '$phone', '123456')";
    if(mysqli_query($conn, $query)){
        $message = "<div class='alert success'>✅ $name enrolled in $class (Roll No: $roll_no).</div>";
    }
}

// Sequence for display
$cl_order = ['Nursery','Prep','1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th','11th','12th','BS','Masters','PHD'];
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< Updated upstream
    <title>Attendance Report | School ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --bg: #f1f5f9; --success: #22c55e; --danger: #ef4444; }
        body { font-family: 'Poppins', sans-serif; margin: 0; display: flex; background: var(--bg); color: #334155; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 25px; background: #0f172a; text-align: center; font-weight: 700; }
        .sidebar a { padding: 15px 25px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #334155; }
        .sidebar a:hover, .active { background: #334155; color: white; border-left: 5px solid var(--accent); }
        
        /* Main Content */
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        /* Filters */
        .filter-bar { display: flex; gap: 20px; margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 10px; align-items: flex-end; }
        .filter-bar input, .filter-bar select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        
        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-item { padding: 20px; border-radius: 12px; color: white; text-align: center; }
        .bg-1 { background: #6366f1; } .bg-2 { background: #10b981; } .bg-3 { background: #f43f5e; } .bg-4 { background: #f59e0b; }
        .stat-item h3 { margin: 0; font-size: 24px; }
        .stat-item p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.85rem; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        
        /* Badges */
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
        .p-badge { background: #dcfce7; color: #166534; }
        .a-badge { background: #fee2e2; color: #991b1b; }

        /* Print Button */
        .btn-print { background: white; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; cursor: pointer; float: right; font-weight: 500; }
        .btn-print:hover { background: #f9f9f9; }

        @media print {
            .sidebar, .filter-bar, .btn-print { display: none; }
            .main { margin-left: 0; padding: 0; }
            .card { box-shadow: none; border: none; }
        }
=======
    <title>Student Management | Admin Portal</title>
    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --success: #059669; --danger: #dc2626; --bg: #f8fafc; --border: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 25px; background: #020617; font-weight: 800; text-align: center; }
        .sidebar a { padding: 14px 20px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #1e293b; }
        .sidebar a:hover, .active { background: #1e293b; color: white; }

        .main { margin-left: 260px; flex: 1; padding: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        
        input, select { padding: 10px; border: 1px solid var(--border); border-radius: 8px; width: 100%; margin-bottom: 15px; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; color: white; text-decoration: none; }
        .btn-primary { background: var(--accent); }
        .btn-success { background: var(--success); }
        .btn-danger { background: var(--danger); font-size: 0.8rem; }
        
        .table-container { background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); margin-bottom: 25px; }
        .table-header { padding: 15px; background: #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        
        .badge { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #ecfdf5; color: #065f46; border-left: 5px solid var(--success); }
        .danger { background: #fef2f2; color: #991b1b; border-left: 5px solid var(--danger); }
        .promote-link { background: var(--accent); color: white; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; }
>>>>>>> Stashed changes
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL ERP</div>
    <a href="dashboard.php">🏠 Dashboard</a>
<<<<<<< Updated upstream
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="attendance_report.php" class="active">📊 Attendance Report</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <button class="btn-print" onclick="window.print()">🖨️ Print Report</button>
    <h2>📊 Attendance Analysis Report</h2>
    
    <div class="card">
        <form method="GET" class="filter-bar">
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size: 12px; font-weight: 600;">Date</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>">
            </div>
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size: 12px; font-weight: 600;">Class</label>
                <select name="class" required>
                    <option value="">-- Select Class --</option>
                    <?php foreach($ordered_classes as $cl): ?>
                        <option value="<?php echo $cl; ?>" <?php if($selected_class == $cl) echo 'selected'; ?>>Class <?php echo $cl; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="padding:11px 25px; background:var(--accent); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Generate Report</button>
        </form>

        <?php if($selected_class): ?>
            <?php 
            // 1. Total Students
            $res_total = mysqli_query($conn, "SELECT id FROM students WHERE class = '$selected_class'");
            $total_std = mysqli_num_rows($res_total);

            // 2. Present/Absent counts
            $res_p = mysqli_query($conn, "SELECT id FROM attendance WHERE class = '$selected_class' AND date = '$selected_date' AND status = 'P'");
            $p_count = mysqli_num_rows($res_p);

            $res_a = mysqli_query($conn, "SELECT id FROM attendance WHERE class = '$selected_class' AND date = '$selected_date' AND status = 'A'");
            $a_count = mysqli_num_rows($res_a);

            $perc = ($total_std > 0) ? round(($p_count / $total_std) * 100, 1) : 0;
            ?>

            <div class="stats-grid">
                <div class="stat-item bg-1"><h3><?php echo $total_std; ?></h3><p>Total Students</p></div>
                <div class="stat-item bg-2"><h3><?php echo $p_count; ?></h3><p>Present</p></div>
                <div class="stat-item bg-3"><h3><?php echo $a_count; ?></h3><p>Absent</p></div>
                <div class="stat-item bg-4"><h3><?php echo $perc; ?>%</h3><p>Attendance Rate</p></div>
            </div>

            <table>
                <thead>
                    <tr><th>Roll No</th><th>Student Name</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT s.roll_no, s.name, a.status 
                            FROM students s 
                            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$selected_date'
                            WHERE s.class = '$selected_class' 
                            ORDER BY CAST(s.roll_no AS UNSIGNED) ASC";
                    $res = mysqli_query($conn, $sql);

                    if(mysqli_num_rows($res) > 0):
                        while($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><b>#<?php echo $row['roll_no']; ?></b></td>
                            <td><?php echo strtoupper($row['name']); ?></td>
                            <td>
                                <?php 
                                if($row['status'] == 'P') echo '<span class="badge p-badge">PRESENT</span>';
                                elseif($row['status'] == 'A') echo '<span class="badge a-badge">ABSENT</span>';
                                else echo '<span style="color:#94a3b8; font-size:12px;">NOT MARKED</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="3" style="text-align:center; padding:20px;">No students found for this class.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align:center; color:#94a3b8; padding:40px;">
                <p>Please select a class and click "Generate Report" to view details.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
=======
    <a href="manage_students.php" class="active">🎓 Manage Students</a>
    <a href="promotion.php">📈 Class Promotion</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="fee_management.php">💰 Fee Management</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Student & Class Administration</h1>
    <?php echo $message; ?>

    <div class="grid">
        <div class="card">
            <h3>🏫 Add New Class</h3>
            <form method="POST">
                <input type="text" name="new_class_name" placeholder="e.g. 10th, BS-CS" required>
                <select name="section_type">
                    <option value="School">School</option>
                    <option value="College">College</option>
                    <option value="University">University</option>
                </select>
                <button type="submit" name="add_class" class="btn btn-success" style="width:100%">Register Class</button>
            </form>
        </div>

        <div class="card">
            <h3>🎓 New Enrollment</h3>
            <form method="POST">
                <div style="display:flex; gap:15px;">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <select name="class" required>
                        <option value="">Select Class</option>
                        <?php 
                        $cl_list = mysqli_query($conn, "SELECT class_name FROM classes ORDER BY id ASC");
                        while($c = mysqli_fetch_assoc($cl_list)) echo "<option value='".$c['class_name']."'>".$c['class_name']."</option>";
                        ?>
                    </select>
                </div>
                <input type="text" name="phone" placeholder="Guardian Phone Number" required>
                <button type="submit" name="register_student" class="btn btn-primary" style="width:100%">Enroll Student</button>
            </form>
        </div>
    </div>

    <?php 
    foreach ($cl_order as $current_cl) {
        $st_res = mysqli_query($conn, "SELECT * FROM students WHERE class = '$current_cl' ORDER BY roll_no ASC");
        if (mysqli_num_rows($st_res) > 0) {
    ?>
    <div class="table-container">
        <div class="table-header">
            <div>
                <b>Class:</b> <?php echo $current_cl; ?> 
                <span class="badge" style="margin-left:10px;">Enrolled: <?php echo mysqli_num_rows($st_res); ?></span>
            </div>
            <a href="promotion.php?view_class=<?php echo $current_cl; ?>" class="promote-link">📈 Promote This Class</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($s = mysqli_fetch_assoc($st_res)): ?>
                <tr>
                    <td><span class="badge">#<?php echo $s['roll_no']; ?></span></td>
                    <td><b><?php echo $s['name']; ?></b></td>
                    <td><?php echo $s['phone']; ?></td>
                    <td style="text-align:right">
                        <a href="?delete_id=<?php echo $s['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete student permanently?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php 
        }
    } 
    ?>
</div>

>>>>>>> Stashed changes
</body>
</html>