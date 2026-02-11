<?php 
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
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL ERP</div>
    <a href="dashboard.php">🏠 Dashboard</a>
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

</body>
</html>