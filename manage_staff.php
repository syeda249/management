<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// --- 1. ADD STAFF LOGIC ---
if (isset($_POST['add_staff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    $joining_date = mysqli_real_escape_string($conn, $_POST['joining_date']);

    $sql = "INSERT INTO staff (name, designation, email, phone, salary, joining_date, status) 
            VALUES ('$name', '$designation', '$email', '$phone', '$salary', '$joining_date', 'Active')";
    
    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✨ Staff member added successfully!</div>";
    }
}

// --- 2. UPDATE STAFF LOGIC ---
if (isset($_POST['update_staff'])) {
    $id = intval($_POST['staff_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE staff SET name='$name', designation='$designation', salary='$salary', status='$status' WHERE id=$id";
    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✅ Staff details updated!</div>";
    }
}

// --- 3. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM staff WHERE id = $id");
    header("Location: manage_staff.php");
    exit();
}

// --- 4. FETCH DATA ---
$staff_list = $conn->query("SELECT * FROM staff ORDER BY status ASC, id DESC");
$p_res = $conn->query("SELECT SUM(salary) as total FROM staff WHERE status='Active'");
$total_payroll = ($p_res) ? ($p_res->fetch_assoc()['total'] ?? 0) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | School Portal</title>
    <style>
        /* Your Sidebar Styles */
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

        /* Main Content Styles */
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 25px; }
        
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; }
        .input-group { display: flex; flex-direction: column; gap: 5px; }
        .input-group label { font-size: 12px; font-weight: bold; color: #555; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f9fa; border-bottom: 2px solid #eee; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-edit { background: #e3f2fd; color: var(--accent); font-size: 12px; }
        .btn-delete { background: #fff5f5; color: var(--danger); font-size: 12px; border: 1px solid #fed7d7; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-left { background: #f8d7da; color: #721c24; }

        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; width: 400px; margin: 10% auto; padding: 25px; border-radius: 10px; position: relative; }
        .close { position: absolute; right: 20px; top: 15px; cursor: pointer; font-size: 24px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php" id="nav-dash">🏠 Dashboard</a>
    <a href="manage_teachers.php" id="nav-teachers">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php" id="nav-staff" class="active-link">🛠️ Manage Staff</a>
    <a href="manage_students.php" id="nav-students">🎓 Manage Students</a>
    <a href="attendance.php" id="nav-attend">📅 Mark Attendance</a>
    <a href="attendance_report.php" id="nav-report">📊 Attendance Report</a>
    <a href="fee_management.php" id="nav-fee">💰 Fee Management</a>
    <a href="setting.php" id="nav-profile">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>
<div class="main-content">
    <div class="header-flex">
        <h1>🛠️ Manage Staff</h1>
        <div style="background: white; padding: 10px 20px; border-radius: 8px; border-right: 4px solid var(--success);">
            <small style="color: #888;">Monthly Payroll</small><br>
            <strong>Rs. <?php echo number_format($total_payroll); ?></strong>
        </div>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3>➕ Add Staff Member</h3>
        <form method="POST">
            <div class="form-row">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Name" required>
                </div>
                <div class="input-group">
                    <label>Designation</label>
                    <input type="text" name="designation" placeholder="e.g. Clerk" required>
                </div>
                <div class="input-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="Phone No">
                </div>
                <div class="input-group">
                    <label>Salary</label>
                    <input type="number" name="salary" placeholder="Amount" required>
                </div>
                <div class="input-group">
                    <label>Joining Date</label>
                    <input type="date" name="joining_date" required>
                </div>
                <button type="submit" name="add_staff" class="btn btn-primary" style="margin-top: 22px;">Save</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Designation</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $staff_list->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $row['name']; ?></strong><br><small><?php echo $row['phone']; ?></small></td>
                    <td><?php echo $row['designation']; ?></td>
                    <td>Rs. <?php echo number_format($row['salary']); ?></td>
                    <td>
                        <span class="badge <?php echo ($row['status'] == 'Active') ? 'badge-active' : 'badge-left'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td>
                        <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="btn btn-edit">Edit</button>
                        <a href="manage_staff.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this record?')">Del</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>✏️ Edit Staff Details</h3>
        <form method="POST">
            <input type="hidden" name="staff_id" id="edit_id">
            <div class="input-group" style="margin-bottom:15px;">
                <label>Name</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="input-group" style="margin-bottom:15px;">
                <label>Designation</label>
                <input type="text" name="designation" id="edit_designation" required>
            </div>
            <div class="input-group" style="margin-bottom:15px;">
                <label>Salary</label>
                <input type="number" name="salary" id="edit_salary" required>
            </div>
            <div class="input-group" style="margin-bottom:20px;">
                <label>Status</label>
                <select name="status" id="edit_status">
                    <option value="Active">Active</option>
                    <option value="Left">Left</option>
                </select>
            </div>
            <button type="submit" name="update_staff" class="btn btn-primary" style="width:100%;">Update Changes</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(staff) {
        document.getElementById('edit_id').value = staff.id;
        document.getElementById('edit_name').value = staff.name;
        document.getElementById('edit_designation').value = staff.designation;
        document.getElementById('edit_salary').value = staff.salary;
        document.getElementById('edit_status').value = staff.status;
        document.getElementById('editModal').style.display = "block";
    }
    function closeModal() { document.getElementById('editModal').style.display = "none"; }
</script>

</body>
</html>