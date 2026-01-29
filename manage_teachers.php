<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// --- 1. ADD TEACHER LOGIC ---
if (isset($_POST['add_teacher'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);

    $sql = "INSERT INTO teachers (name, email, subject) VALUES ('$name', '$email', '$subject')";
    
    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✨ Teacher added successfully!</div>";
    } else {
        $message = "<div class='alert danger'>❌ Error: " . $conn->error . "</div>";
    }
}

// --- 2. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM teachers WHERE id = $id");
    header("Location: manage_teachers.php");
    exit();
}

// --- 3. FETCH DATA ---
$teachers_list = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers | School Portal</title>
    <style>
        /* --- Sidebar & Global Styles (Same as Staff Page) --- */
        :root { --primary: #2c3e50; --secondary: #34495e; --accent: #3498db; --success: #27ae60; --danger: #e74c3c; --bg: #f4f7f6; }
        
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }

        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; transition: 0.3s; z-index: 1000; }
        .sidebar-header { padding: 20px; background: #1a252f; text-align: center; font-weight: bold; font-size: 20px; letter-spacing: 1px; }
        .sidebar a { 
            padding: 15px 25px; color: #bdc3c7; text-decoration: none; display: block; 
            border-bottom: 1px solid #34495e; transition: 0.3s; font-size: 15px;
        }
        .sidebar a:hover { background: var(--secondary); color: white; padding-left: 35px; }
        .active-link { background: var(--accent) !important; color: white !important; border-left: 5px solid white; }

        /* --- Main Content --- */
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px; }
        
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 12px; font-weight: bold; color: #555; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; box-sizing: border-box; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f9fa; border-bottom: 2px solid #eee; font-size: 14px; color: #666; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-delete { background: #fff5f5; color: var(--danger); font-size: 12px; border: 1px solid #fed7d7; }
        
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .danger { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php" id="nav-dash">🏠 Dashboard</a>
    <a href="manage_teachers.php" id="nav-teachers" class="active-link">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php" id="nav-staff">🛠️ Manage Staff</a>
    <a href="manage_students.php" id="nav-students">🎓 Manage Students</a>
    <a href="attendance.php" id="nav-attend">📅 Mark Attendance</a>
    <a href="attendance_report.php" id="nav-report">📊 Attendance Report</a>
    <a href="fee_management.php" id="nav-fee">💰 Fee Management</a>
    <a href="setting.php" id="nav-profile">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>
<div class="main-content">
    <div class="header-flex">
        <h1>👨‍🏫 Manage Teachers</h1>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3>➕ Add New Teacher</h3>
        <form method="POST">
            <div class="form-row">
                <div class="input-group">
                    <label>Teacher Name</label>
                    <input type="text" name="name" placeholder="Enter Full Name" required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="email@school.com" required>
                </div>
                <div class="input-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g. Mathematics" required>
                </div>
                <div class="input-group">
                    <button type="submit" name="add_teacher" class="btn btn-primary" style="margin-top: 22px;">Add Teacher</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Email</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($teachers_list && $teachers_list->num_rows > 0): ?>
                    <?php while($row = $teachers_list->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td><span style="color: var(--accent); font-weight: 500;"><?php echo $row['subject']; ?></span></td>
                        <td><?php echo $row['email']; ?></td>
                        <td style="text-align: center;">
                            <a href="manage_teachers.php?delete=<?php echo $row['id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this teacher?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #999;">No teachers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>