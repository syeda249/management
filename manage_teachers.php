<?php
session_start();
include 'db.php';

/* ============ ACCESS CONTROL ============ */
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    echo "<h2 style='color:red;'>Access Denied</h2>";
    exit();
}

/* ============ MARK ATTENDANCE ============ */
if (isset($_POST['mark_attendance'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $date       = $_POST['date'];
    $status     = $_POST['status'];

    $check = $conn->query("SELECT id FROM teacher_attendance WHERE teacher_id=$teacher_id AND date='$date'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO teacher_attendance (teacher_id,date,status) VALUES ($teacher_id,'$date','$status')");
        $success = "✅ Attendance marked successfully!";
    } else {
        $error = "⚠️ Attendance already marked for this date!";
    }
}

/* ============ ADD TEACHER ============ */
if (isset($_POST['add_teacher'])) {
    $name      = mysqli_real_escape_string($conn, $_POST['name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = $_POST['password'];
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $committee = mysqli_real_escape_string($conn, $_POST['committee']);
    $activity  = mysqli_real_escape_string($conn, $_POST['activity']);

    $check = $conn->query("SELECT id FROM teachers WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "❌ Teacher already exists!";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $conn->query("INSERT INTO teachers (name,email,password,role,committee,activity,created_at)
                      VALUES ('$name','$email','$password_hashed','$role','$committee','$activity',NOW())");
        $teacher_id = $conn->insert_id;

        $username = strtolower(str_replace(' ', '', $name));
        $conn->query("INSERT INTO users (username,password,role,email)
                      VALUES ('$username','$password_hashed','teacher','$email')");
        $user_id = $conn->insert_id;

        $conn->query("UPDATE teachers SET user_id=$user_id WHERE id=$teacher_id");

        $success = "✅ Teacher added successfully!";
    }
}

/* ============ GENERATE SALARY ============ */
if (isset($_POST['generate_salary'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $month      = $_POST['month'];
    $bonus      = floatval($_POST['bonus']);

    // Get teacher role
    $t = $conn->query("SELECT role FROM teachers WHERE id=$teacher_id")->fetch_assoc();
    if ($t) {
        $role = $t['role'];
        $s = $conn->query("SELECT basic_salary FROM salary_slabs WHERE category='$role'")->fetch_assoc();
        $basic = $s['basic_salary'] ?? 0;

        // Count attendance for the selected month
        $present = $conn->query("SELECT COUNT(*) as c FROM teacher_attendance WHERE teacher_id=$teacher_id AND status='Present' AND DATE_FORMAT(date,'%Y-%m')='$month'")->fetch_assoc()['c'] ?? 0;
        $absent  = $conn->query("SELECT COUNT(*) as c FROM teacher_attendance WHERE teacher_id=$teacher_id AND status='Absent' AND DATE_FORMAT(date,'%Y-%m')='$month'")->fetch_assoc()['c'] ?? 0;

        $per_day = $basic / 30;
        $deduction = $absent * $per_day;
        $net = $basic - $deduction + $bonus;

        // Insert into teacher_salaries
        $conn->query("INSERT INTO teacher_salaries (teacher_id,month,basic_salary,total_present,total_absent,deduction,bonus,net_salary,status)
                      VALUES ($teacher_id,'$month',$basic,$present,$absent,$deduction,$bonus,$net,'Unpaid')");
        $success = "✅ Salary generated successfully!";
    } else {
        $error = "❌ Teacher not found!";
    }
}

/* ============ FETCH TEACHERS + ATTENDANCE + SALARY ============ */
$sql = "
SELECT t.*,
       s.basic_salary,
       IFNULL(SUM(CASE WHEN ta.status='Present' THEN 1 ELSE 0 END),0) AS total_present,
       IFNULL(SUM(CASE WHEN ta.status='Absent' THEN 1 ELSE 0 END),0) AS total_absent,
       IFNULL(ts.net_salary,0) AS net_salary,
       IFNULL(ts.status,'Unpaid') AS salary_status,
       ts.paid_at,
       ts.id AS salary_id
FROM teachers t
LEFT JOIN salary_slabs s ON s.category = t.role
LEFT JOIN teacher_attendance ta ON ta.teacher_id = t.id
LEFT JOIN teacher_salaries ts ON ts.teacher_id = t.id
GROUP BY t.id
ORDER BY t.id DESC
";
$result = $conn->query($sql);

$teachers = $conn->query("SELECT id,name FROM teachers ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Teachers</title>
<style>
body{font-family:sans-serif;background:#f4f4f4;margin:0;display:flex}
.sidebar{width:220px;background:#2c3e50;color:#fff;height:100vh;padding:20px}
.main{flex:1;padding:30px;overflow-x:auto}
.card{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px}
table{width:100%;border-collapse:collapse;min-width:1200px}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#3498db;color:white}
input,select{padding:8px;margin:5px;width:180px}
.btn{padding:6px 12px;border:none;border-radius:4px;color:#fff;cursor:pointer;text-decoration:none;font-size:13px}
.btn-add{background:#28a745}
.btn-delete{background:#e74c3c}
.btn-blue{background:#0984e3}
</style>
</head>
<body>

<div class="sidebar">
<h3>School Admin</h3>
<a href="dashboard.php" style="color:white;display:block">📊 Dashboard</a><br>
<a href="manage_teachers.php" style="color:white;display:block">👨‍🏫 Manage Teachers</a><br>
<a href="assign_teacher_schedule.php" style="color:white;display:block">📅 Teacher Schedule</a><br>
<hr style="border:0.5px solid #555">
<a href="generate_salary.php" style="color:white;display:block">💰 Generate Salary</a><br>
<a href="salaries_list.php" style="color:white;display:block">📄 Salary List</a><br>
<hr style="border:0.5px solid #555">
<a href="logout.php" style="color:#ff7675;display:block">🚪 Logout</a>
<a href="reset_teachers.php" style="color:#ff7675;display:block">🚪 reset_teachers</a>
</div>

<div class="main">
<h1>👨‍🏫 Manage Teachers</h1>

<?php
if(isset($success)) echo "<p style='color:green'>$success</p>";
if(isset($error)) echo "<p style='color:red'>$error</p>";
?>

<!-- ADD TEACHER -->
<div class="card">
<h3>Add Teacher</h3>
<form method="POST">
<input type="text" name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" required>
<option value="">Select Role</option>
<option>Primary</option>
<option>Middle</option>
<option>High</option>
<option>Support Staff</option>
</select>

<select name="committee">
<option value="">Committee</option>
<option>Discipline Committee</option>
<option>Examination Committee</option>
<option>Admission Committee</option>
</select>

<select name="activity">
<option value="">Activity</option>
<option>Sports Coordinator</option>
<option>Program Conductor</option>
<option>Event Manager</option>
</select>

<br><br>
<button class="btn btn-add" name="add_teacher">Add Teacher</button>
</form>
</div>

<!-- MARK ATTENDANCE -->
<div class="card">
<h3>📅 Mark Attendance</h3>
<form method="POST">
<select name="teacher_id" required>
<option value="">Select Teacher</option>
<?php 
$teachers->data_seek(0);
while($t=$teachers->fetch_assoc()): ?>
<option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
<?php endwhile; ?>
</select>

<input type="date" name="date" required>
<select name="status">
<option value="Present">Present</option>
<option value="Absent">Absent</option>
</select>

<br><br>
<button class="btn btn-blue" name="mark_attendance">Mark Attendance</button>
</form>
</div>

<!-- GENERATE SALARY -->
<div class="card">
<h3>💰 Generate Salary</h3>
<form method="POST">
<select name="teacher_id" required>
<option value="">Select Teacher</option>
<?php 
$teachers->data_seek(0);
while($t=$teachers->fetch_assoc()): ?>
<option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
<?php endwhile; ?>
</select>

<input type="month" name="month" required>
<input type="number" name="bonus" value="0" placeholder="Bonus">
<br><br>
<button class="btn btn-add" name="generate_salary">Generate Salary</button>
</form>
</div>

<!-- TEACHERS LIST -->
<div class="card">
<h3>Teachers List</h3>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Role</th>
<th>Email</th>
<th>Basic Salary</th>
<th>Present</th>
<th>Absent</th>
<th>Net Salary</th>
<th>Status</th>
<th>Paid At</th>
<th>Schedule</th>
<th>Salary</th>
<th>Action</th>
</tr>

<?php 
$result->data_seek(0);
while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['role'] ?></td>
<td><?= $row['email'] ?></td>
<td><?= number_format($row['basic_salary']) ?></td>
<td><?= $row['total_present'] ?></td>
<td><?= $row['total_absent'] ?></td>
<td><?= number_format($row['net_salary']) ?></td>
<td><?= $row['salary_status'] ?></td>
<td><?= $row['paid_at'] ?? '-' ?></td>

<td>
<a class="btn btn-blue" href="assign_teacher_schedule.php?teacher_id=<?= $row['id'] ?>">Schedule</a>
</td>

<td>
<a class="btn btn-blue" href="#" onclick="alert('Use Generate Salary form above')">💰 Salary</a>
<?php if($row['salary_id']): ?>
<a class="btn btn-add" href="salary_slip.php?id=<?= $row['salary_id'] ?>" target="_blank">🧾 Slip</a>
<?php endif; ?>
</td>

<td>
<a class="btn btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

</div>
</body>
</html>
