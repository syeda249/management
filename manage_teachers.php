<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    echo "<h2 style='color:red;'>Access Denied</h2>";
    exit();
}

// DELETE teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Optional: Delete user record as well
    $user = $conn->query("SELECT user_id FROM teachers WHERE id=$id")->fetch_assoc();
    if($user && $user['user_id']){
        $conn->query("DELETE FROM users WHERE id=".$user['user_id']);
    }

    $conn->query("DELETE FROM teachers WHERE id=$id");
    header("Location: manage_teachers.php");
    exit();
}

// ADD teacher
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_teacher'])) {

    $name      = mysqli_real_escape_string($conn, $_POST['name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = $_POST['password']; // plain password from form
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $committee = mysqli_real_escape_string($conn, $_POST['committee']);
    $activity  = mysqli_real_escape_string($conn, $_POST['activity']);

    // Duplicate email check
    $check = $conn->query("SELECT id FROM teachers WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "❌ Teacher already exists!";
    } else {
        // Hash the password
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert into teachers table
        $sql = "INSERT INTO teachers (name,email,password,role,committee,activity,created_at)
                VALUES ('$name','$email','$password_hashed','$role','$committee','$activity',NOW())";
        if ($conn->query($sql)) {

            $teacher_id = $conn->insert_id;

            // Create username for users table
            $username = strtolower(str_replace(' ', '', $name));

            // Insert into users table
            $conn->query("INSERT INTO users (username,password,role,email)
                          VALUES ('$username','$password_hashed','teacher','$email')");

            $user_id = $conn->insert_id;

            // Update teachers table with user_id
            $conn->query("UPDATE teachers SET user_id=$user_id WHERE id=$teacher_id");

            $success = "✅ Teacher added successfully!";
        } else {
            $error = "Error adding teacher: ".$conn->error;
        }
    }
}

// Fetch teachers
$result = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Teachers</title>
<style>
body{font-family:sans-serif;background:#f4f4f4;margin:0;display:flex}
.sidebar{width:220px;background:#2c3e50;color:#fff;height:100vh;padding:20px}
.main{flex:1;padding:30px}
.card{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px}
table{width:100%;border-collapse:collapse}
th,td{padding:10px;border:1px solid #ddd}
th{background:#3498db;color:white}
input,select{padding:8px;margin:5px;width:180px}
.btn{padding:7px 14px;border:none;border-radius:4px;color:#fff;cursor:pointer;text-decoration:none}
.btn-add{background:#28a745}
.btn-delete{background:#e74c3c}
.btn-blue{background:#0984e3}
.btn-purple{background:#6c5ce7}
.top-actions a{margin-right:10px}
</style>
</head>
<body>

<div class="sidebar">
    <h3>School Admin</h3>
    <a href="dashboard.php" style="color:white;display:block">📊 Dashboard</a><br>
    <a href="manage_teachers.php" style="color:white;display:block">👨‍🏫 Manage Teachers</a><br>
    <a href="assign_teacher_schedule.php" style="color:white;display:block">📅 Teacher Schedule</a><br>
    <a href="staff_categories.php" style="color:white;display:block">🗂 Staff Categories</a><br><br>
    <a href="logout.php" style="color:#ff7675">🚪 Logout</a>
</div>

<div class="main">
<h1>👨‍🏫 Manage Teachers</h1>

<div class="top-actions" style="margin-bottom:20px;">
    <a href="assign_teacher_schedule.php" class="btn btn-blue">📅 Assign Schedule</a>
    <a href="staff_categories.php" class="btn btn-purple">🗂 Staff Categories</a>
</div>

<?php
if(isset($success)) echo "<p style='color:green'>$success</p>";
if(isset($error)) echo "<p style='color:red'>$error</p>";
?>

<div class="card">
<h3>Add Teacher</h3>
<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="">Select Role</option>
        <option>Principal</option>
        <option>Vice Principal</option>
        <option>Teacher</option>
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

<div class="card">
<h3>Teachers List</h3>
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Role</th>
<th>Committee</th>
<th>Activity</th>
<th>Email</th>
<th>Schedule</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['role'] ?></td>
<td><?= $row['committee'] ?></td>
<td><?= $row['activity'] ?></td>
<td><?= $row['email'] ?></td>
<td>
<a href="assign_teacher_schedule.php?teacher_id=<?= $row['id'] ?>" 
class="btn btn-blue">View / Assign</a>
</td>
<td>
<a href="?delete=<?= $row['id'] ?>" 
class="btn btn-delete"
onclick="return confirm('Delete teacher?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
