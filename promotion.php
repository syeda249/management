<?php 
include('db.php');

$message = "";
$report_data = []; 

/**
 * AUTOMATED LOGIC: SMART PROMOTION
 */
if (isset($_POST['process_promotion'])) {
    if (isset($_POST['status']) && is_array($_POST['status'])) {
        $current_class_name = mysqli_real_escape_string($conn, $_POST['current_class']);
        $statuses = $_POST['status']; 

        // 1. Find the "Next Class" automatically from the database sequence
        $class_query = mysqli_query($conn, "SELECT id FROM classes WHERE class_name = '$current_class_name'");
        $current_class_data = mysqli_fetch_assoc($class_query);
        $current_id = $current_class_data['id'];

        // Get the class that has the next higher ID
        $next_class_query = mysqli_query($conn, "SELECT class_name FROM classes WHERE id > '$current_id' ORDER BY id ASC LIMIT 1");
        
        if (mysqli_num_rows($next_class_query) > 0) {
            $next_data = mysqli_fetch_assoc($next_class_query);
            $next_class_name = $next_data['class_name'];
        } else {
            $next_class_name = "Graduated"; 
        }

        // 2. Process each student
        foreach ($statuses as $student_id => $result) {
            $student_id = mysqli_real_escape_string($conn, $student_id);
            
            $fetch = mysqli_query($conn, "SELECT name, roll_no FROM students WHERE id = '$student_id'");
            $s = mysqli_fetch_assoc($fetch);
            $name = $s['name'];
            $roll = $s['roll_no'];

            if ($result == 'Pass') {
                // MOVE AUTOMATICALLY TO NEXT CLASS
                mysqli_query($conn, "UPDATE students SET class = '$next_class_name' WHERE id = '$student_id'");
                $report_data[] = ['name' => $name, 'roll' => $roll, 'result' => 'PROMOTED', 'target' => $next_class_name, 'css' => 'status-pass'];
            } else {
                // RETAIN IN CURRENT CLASS
                $report_data[] = ['name' => $name, 'roll' => $roll, 'result' => 'RETAINED', 'target' => $current_class_name, 'css' => 'status-fail'];
            }
        }
        $message = "<div class='alert-box'>Bulk Automation Completed Successfully.</div>";
    }
}

$class_list = mysqli_query($conn, "SELECT * FROM classes ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Promotion | School Portal</title>
    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --bg: #f8fafc; --border: #e2e8f0; --success: #059669; --danger: #dc2626; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--bg); color: #1e293b; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; overflow-y: auto; }
        .sidebar-header { padding: 25px; background: #020617; font-weight: 800; font-size: 1.1rem; text-align: center; border-bottom: 1px solid #1e293b; }
        .sidebar a { padding: 12px 20px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #1e293b; font-size: 0.9rem; }
        .sidebar a:hover { background: #1e293b; color: white; }
        .active-nav { background: #334155; color: white !important; }

        /* Main Area */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }
        .card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; background: #f1f5f9; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; }
        
        .select-input { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: white; }
        .btn-execute { background: var(--accent); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 20px; font-size: 1rem; }
        
        .status-pass { color: var(--success); font-weight: bold; }
        .status-fail { color: var(--danger); font-weight: bold; }
        .alert-box { padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid var(--success); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php">🛠️ Manage Staff</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="attendance_report.php">📊 Attendance Report</a>
    <a href="promotion.php" class="active-nav">📈 Class Promotion</a>
    <a href="fee_management.php">💰 Fee Management</a>
    <a href="setting.php">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <h1>Automated Class Promotion</h1>
    <?php echo $message; ?>

    <?php if (!empty($report_data)): ?>
    <div class="card" style="border: 2px solid var(--accent);">
        <h3 style="color:var(--accent); margin-top:0;">Final Result Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Status</th>
                    <th>Final Placement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $row): ?>
                <tr>
                    <td>#<?php echo $row['roll']; ?></td>
                    <td><b><?php echo $row['name']; ?></b></td>
                    <td class="<?php echo $row['css']; ?>"><?php echo $row['result']; ?></td>
                    <td><b><?php echo $row['target']; ?></b></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3>Step 1: Select Current Class</h3>
        <form method="GET">
            <select name="view_class" onchange="this.form.submit()" class="select-input" style="max-width: 400px;">
                <option value="">-- Choose Class --</option>
                <?php 
                mysqli_data_seek($class_list, 0);
                while($c = mysqli_fetch_assoc($class_list)) {
                    $sel = (isset($_GET['view_class']) && $_GET['view_class'] == $c['class_name']) ? 'selected' : '';
                    echo "<option value='".$c['class_name']."' $sel>".$c['class_name']."</option>";
                }
                ?>
            </select>
        </form>
    </div>

    <?php if (isset($_GET['view_class']) && !empty($_GET['view_class'])): 
        $v_class = mysqli_real_escape_string($conn, $_GET['view_class']);
        $students = mysqli_query($conn, "SELECT * FROM students WHERE class = '$v_class' ORDER BY roll_no ASC");
    ?>
    <div class="card">
        <form method="POST" onsubmit="return confirm('Apply changes to all students in this class?')">
            <input type="hidden" name="current_class" value="<?php echo $v_class; ?>">
            
            <p>Processing <b><?php echo $v_class; ?></b>. Promoted students move forward automatically.</p>

            <table>
                <thead>
                    <tr>
                        <th width="15%">Roll No</th>
                        <th>Student Name</th>
                        <th width="30%">Assign Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = mysqli_fetch_assoc($students)): ?>
                    <tr>
                        <td><b>#<?php echo $s['roll_no']; ?></b></td>
                        <td><?php echo $s['name']; ?></td>
                        <td>
                            <select name="status[<?php echo $s['id']; ?>]" class="select-input">
                                <option value="Pass">✅ PROMOTE (Pass)</option>
                                <option value="Fail">❌ RETAIN (Fail)</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="process_promotion" class="btn-execute">Execute All Decisions</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>