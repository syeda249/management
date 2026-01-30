<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_user_id = $_SESSION['user_id'];

// get teacher record using user_id
$teacher = $conn->query("SELECT id, name FROM teachers WHERE user_id = $teacher_user_id");
$teacherData = $teacher->fetch_assoc();

if (!$teacherData) {
    die("Teacher record not found. Ask admin to link your account.");
}

$teacher_id = $teacherData['id'];

// get teacher schedule
$schedule = $conn->query("
    SELECT c.class_name, ts.subject, ts.day, ts.time_slot
    FROM teacher_schedule ts
    JOIN classes c ON ts.class_id = c.id
    WHERE ts.teacher_id = $teacher_id
    ORDER BY FIELD(ts.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), ts.time_slot
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Dashboard</title>
<style>
body{font-family:Segoe UI;background:#f4f6f8;padding:30px}
.card{background:#fff;padding:20px;border-radius:10px;max-width:800px;margin:auto}
h2{color:#1a73e8}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#1a73e8;color:#fff}
.logout{display:inline-block;margin-top:15px;color:red;text-decoration:none}
</style>
</head>
<body>

<div class="card">
<h2>Welcome <?= htmlspecialchars($teacherData['name']) ?></h2>

<h3>Your Teaching Schedule</h3>

<?php if($schedule->num_rows > 0): ?>
<table>
<tr>
    <th>Class</th>
    <th>Subject</th>
    <th>Day</th>
    <th>Time</th>
</tr>
<?php while($row = $schedule->fetch_assoc()): ?>
<tr>
    <td><?= $row['class_name'] ?></td>
    <td><?= $row['subject'] ?></td>
    <td><?= $row['day'] ?></td>
    <td><?= $row['time_slot'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p style="color:red;">No schedule assigned yet.</p>
<?php endif; ?>

<a class="logout" href="logout.php">Logout</a>
</div>

</body>
</html>
