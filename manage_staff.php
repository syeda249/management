<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'db.php';

if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

$message = "";

// --- 1. Add Staff Logic ---
if (isset($_POST['add_staff'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $timing = mysqli_real_escape_string($conn, $_POST['timing']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    
    $sql = "INSERT INTO staff (name, designation, timing, description, salary, status) 
            VALUES ('$name', '$role', '$timing', '$description', '$salary', 'Active')";
    
    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✅ Staff Member added successfully!</div>";
    }
}

// --- 2. Edit/Update Staff Logic ---
if (isset($_POST['update_staff'])) {
    $id = $_POST['staff_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $timing = mysqli_real_escape_string($conn, $_POST['timing']);
    $salary = mysqli_real_escape_string($conn, $_POST['salary']);
    $bonus = mysqli_real_escape_string($conn, $_POST['bonus']);
    $fine = mysqli_real_escape_string($conn, $_POST['fine']);

    $sql = "UPDATE staff SET name='$name', designation='$role', timing='$timing', 
            salary='$salary', bonus='$bonus', fine='$fine' WHERE id='$id'";
    
    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✅ Staff record updated successfully!</div>";
    }
}

// --- 3. Attendance Save Logic ---
if (isset($_POST['save_attendance'])) {
    $staff_id = $_POST['staff_id'];
    $att_status = $_POST['att_status'];
    $date = date('Y-m-d');
    $check = $conn->query("SELECT id FROM staff_attendance WHERE staff_id='$staff_id' AND date='$date'");
    if($check->num_rows > 0) {
        $conn->query("UPDATE staff_attendance SET status='$att_status' WHERE staff_id='$staff_id' AND date='$date'");
    } else {
        $conn->query("INSERT INTO staff_attendance (staff_id, date, status) VALUES ('$staff_id', '$date', '$att_status')");
    }
    $message = "<div class='alert success'>✅ Attendance marked for today!</div>";
}

// --- 4. Delete Logic ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM staff WHERE id='$id'");
    header("Location: manage_staff.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff & Payroll Management</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #27ae60; --danger: #e74c3c; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .main-content { margin-left: 260px; flex: 1; padding: 30px; width: calc(100% - 260px); }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        label { font-size: 13px; font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; }
        th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-save { background: var(--primary); color: white; width: 100%; margin-top: 10px; }
        .btn-edit { background: #f1f3f4; color: var(--accent); margin-right: 5px; }
        .btn-att { background: #e8f5e9; color: #2e7d32; margin-right: 5px; }
        .btn-delete { color: var(--danger); text-decoration: none; font-weight: bold; }
        .payroll-calc { font-size: 11px; color: #666; background: #fffde7; padding: 5px; border-radius: 4px; margin-top: 5px; border: 1px dashed #ffd54f; }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; background: #e3f2fd; color: #1976d2; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid var(--success); }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 20px; background: #1a252f; text-align: center; font-weight: bold; font-size: 20px; }
        .sidebar a { padding: 15px 25px; color: #bdc3c7; text-decoration: none; display: block; border-bottom: 1px solid #34495e; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; width: 450px; margin: 10% auto; padding: 25px; border-radius: 12px; position: relative; }
        .close { position: absolute; right: 20px; top: 15px; cursor: pointer; font-size: 24px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php" style="background:var(--accent); color:white;">🛠️ Manage Staff</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="fee_management.php">💰 Fee Management</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>🛠️ Staff & Payroll</h1>
        <div style="text-align: right;">
            <small>Total Monthly Budget</small><br>
            <strong style="font-size: 20px; color: var(--primary);">Rs. <?php 
                $sum = $conn->query("SELECT SUM(salary + bonus - fine) as total FROM staff")->fetch_assoc();
                echo number_format($sum['total'] ?? 0);
            ?></strong>
        </div>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3>➕ Hire New Staff</h3>
        <form method="POST">
            <div class="form-grid">
                <div><label>Full Name</label><input type="text" name="name" required></div>
                <div>
                    <label>Role</label>
                    <select name="role">
                        <option value="Guard">👮 Guard</option>
                        <option value="Sweeper">🧹 Sweeper</option>
                        <option value="Peon">☕ Peon</option>
                    </select>
                </div>
                <div><label>Timing</label><input type="text" name="timing" required></div>
                <div><label>Salary</label><input type="number" name="salary" required></div>
            </div>
            <button type="submit" name="add_staff" class="btn btn-save">💾 Save Staff Record</button>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Staff Info</th>
                    <th>Duty & Timing</th>
                    <th>Salary (Net)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM staff ORDER BY id DESC");
                while($row = $res->fetch_assoc()) {
                    $bonus = $row['bonus'] ?? 0;
                    $fine = $row['fine'] ?? 0;
                    $net = ($row['salary'] + $bonus) - $fine;
                    $today = date('Y-m-d');
                    $att = $conn->query("SELECT status FROM staff_attendance WHERE staff_id='{$row['id']}' AND date='$today'")->fetch_assoc();
                ?>
                <tr>
                    <td>
                        <strong><?php echo $row['name']; ?></strong><br>
                        <span class="status-pill"><?php echo $row['designation']; ?></span>
                    </td>
                    <td><small><?php echo $row['timing']; ?></small></td>
                    <td>
                        <strong>Rs. <?php echo number_format($net); ?></strong>
                        <div class="payroll-calc">B: +<?php echo $bonus; ?> | F: -<?php echo $fine; ?></div>
                    </td>
                    <td>
                        <button onclick="openAttModal(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>')" class="btn btn-att">📅 Att</button>
                        <button onclick='openEditModal(<?php echo json_encode($row); ?>)' class="btn btn-edit">✏️ Edit</button>
                        <a href="manage_staff.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete?')">🗑️</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3>✏️ Edit Staff & Payroll</h3>
        <form method="POST">
            <input type="hidden" name="staff_id" id="edit_id">
            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div><label>Name</label><input type="text" name="name" id="edit_name" required></div>
                <div><label>Role</label><input type="text" name="role" id="edit_role" required></div>
                <div><label>Timing</label><input type="text" name="timing" id="edit_timing" required></div>
                <div><label>Basic Salary</label><input type="number" name="salary" id="edit_salary" required></div>
                <div><label style="color:var(--success)">Bonus (Rs.)</label><input type="number" name="bonus" id="edit_bonus"></div>
                <div><label style="color:var(--danger)">Fine/Deduction (Rs.)</label><input type="number" name="fine" id="edit_fine"></div>
            </div>
            <button type="submit" name="update_staff" class="btn btn-save" style="background:var(--accent)">Update Changes</button>
        </form>
    </div>
</div>

<div id="attModal" class="modal">
    <div class="modal-content" style="width:300px;">
        <span class="close" onclick="closeModal('attModal')">&times;</span>
        <h4 id="attName">Mark Attendance</h4>
        <form method="POST">
            <input type="hidden" name="staff_id" id="att_id">
            <select name="att_status" style="margin-bottom:20px;">
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
            </select>
            <button type="submit" name="save_attendance" class="btn btn-save">Save Today</button>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_role').value = data.designation;
    document.getElementById('edit_timing').value = data.timing;
    document.getElementById('edit_salary').value = data.salary;
    document.getElementById('edit_bonus').value = data.bonus || 0;
    document.getElementById('edit_fine').value = data.fine || 0;
    document.getElementById('editModal').style.display = "block";
}
function openAttModal(id, name) {
    document.getElementById('att_id').value = id;
    document.getElementById('attName').innerText = name;
    document.getElementById('attModal').style.display = "block";
}
function closeModal(m) { document.getElementById(m).style.display = "none"; }
</script>

</body>
</html>