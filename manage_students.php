<?php 
include('db.php');

// --- 1. ADD NEW CLASS ---
if (isset($_POST['add_class'])) {
    $c_name = trim(mysqli_real_escape_string($conn, $_POST['new_class_name']));
    $s_type = mysqli_real_escape_string($conn, $_POST['section_type']);
    if(!empty($c_name)) {
        $check = mysqli_query($conn, "SELECT id FROM classes WHERE class_name = '$c_name'");
        if(mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO classes (class_name, section_type) VALUES ('$c_name', '$s_type')");
        }
    }
    header("Location: manage_students.php"); exit();
}

// --- 2. ENROLL STUDENT ---
if (isset($_POST['register_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $res = mysqli_query($conn, "SELECT MAX(roll_no) as last FROM students WHERE class = '$class'");
    $row = mysqli_fetch_assoc($res);
    $roll = ($row['last']) ? $row['last'] + 1 : 1;
    
    mysqli_query($conn, "INSERT INTO students (name, class, phone, roll_no) VALUES ('$name', '$class', '$phone', '$roll')");
    header("Location: manage_students.php"); exit();
}

// --- 3. DELETE STUDENT ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM students WHERE id = '$id'");
    header("Location: manage_students.php"); exit();
}

// --- EXACT SCHOOL SEQUENCE (Isi list se tables baneinge) ---
$ordered_classes = [
    'Nursery', 'Prep', '1', '2', '3', '4', '5', '6', '7', '8', '9th', '10th', '1st year', '2nd year'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management | Admin Portal</title>
    <style>
        :root { --primary: #0f172a; --accent: #2563eb; --success: #059669; --danger: #dc2626; --bg: #f8fafc; --border: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 25px; background: #020617; font-weight: 800; text-align: center; }
        .sidebar a { padding: 14px 20px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #1e293b; }
        .sidebar a:hover, .active { background: #1e293b; color: white; border-left: 4px solid var(--accent); }
        .main { margin-left: 260px; flex: 1; padding: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        input, select { padding: 12px; border: 1px solid var(--border); border-radius: 8px; width: 100%; margin-bottom: 15px; }
        .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; color: white; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--accent); }
        .btn-success { background: var(--success); }
        .btn-danger { background: var(--danger); font-size: 0.8rem; }
        .table-container { background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); margin-bottom: 30px; }
        .table-header { padding: 15px; background: #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        .badge { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: bold; }
        .promote-link { background: var(--accent); color: white; padding: 6px 15px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; }
        .no-data { padding: 20px; text-align: center; color: #64748b; font-style: italic; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL ERP</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_students.php" class="active">🎓 Manage Students</a>
    <a href="promotion.php">📈 Class Promotion</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="attendance_report.php" class="active"> 📊Attendance Report</a>
    <a href="fee_management.php">💰 Fee Management</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h1>Student & Class Administration</h1>
    
    <div class="grid">
        <div class="card">
            <h3>🏫 Register Class</h3>
            <form method="POST">
                <input type="text" name="new_class_name" placeholder="Class Name (e.g. 4, 5)" required>
                <select name="section_type">
                    <option value="School">School</option>
                    <option value="College">College</option>
                </select>
                <button type="submit" name="add_class" class="btn btn-success" style="width:100%">Add to System</button>
            </form>
        </div>

        <div class="card">
            <h3>🎓 New Student Enrollment</h3>
            <form method="POST">
                <div style="display:flex; gap:10px;">
                    <input type="text" name="name" placeholder="Full Name" required>
                    <select name="class" required>
                        <option value="">Select Class</option>
                        <?php foreach($ordered_classes as $oc) { echo "<option value='$oc'>$oc</option>"; } ?>
                    </select>
                </div>
                <input type="text" name="phone" placeholder="Phone Number">
                <button type="submit" name="register_student" class="btn btn-primary" style="width:100%">Enroll Student</button>
            </form>
        </div>
    </div>

    <?php 
    foreach($ordered_classes as $class_name): 
        $st_query = mysqli_query($conn, "SELECT * FROM students WHERE class = '$class_name' ORDER BY roll_no ASC");
        $count = mysqli_num_rows($st_query);
    ?>
    <div class="table-container">
        <div class="table-header">
            <div>
                <span style="font-size: 1.1rem; font-weight: bold;">Class: <?php echo $class_name; ?></span>
                <span class="badge" style="margin-left:10px;">Total: <?php echo $count; ?></span>
            </div>
            <a href="promotion.php?view_class=<?php echo urlencode($class_name); ?>" class="promote-link">📈 Promote Class</a>
        </div>
        
        <?php if($count > 0): ?>
        <table>
            <thead>
                <tr><th width="10%">Roll No</th><th width="45%">Full Name</th><th width="25%">Phone</th><th style="text-align:right">Action</th></tr>
            </thead>
            <tbody>
                <?php while($s = mysqli_fetch_assoc($st_query)): ?>
                <tr>
                    <td><span class="badge" style="background:#f1f5f9; color:#475569;">#<?php echo $s['roll_no']; ?></span></td>
                    <td><b><?php echo strtoupper($s['name']); ?></b></td>
                    <td><?php echo $s['phone']; ?></td>
                    <td style="text-align:right">
                        <a href="?delete_id=<?php echo $s['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="no-data">no student is enrolled in this class.</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>