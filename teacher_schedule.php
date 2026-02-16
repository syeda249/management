<?php
session_start();
include 'db.php';

// Only teacher allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher'){
    header("Location: login.php");
    exit();
}

// Check user session
if(!isset($_SESSION['user_id'])){
    die("User not logged in!");
}

$teacher_user_id = intval($_SESSION['user_id']);

// Get teacher record
$teacherQuery = $conn->query("SELECT id, name FROM teachers WHERE user_id = $teacher_user_id");

if($teacherQuery->num_rows == 0){
    die("Teacher not found!");
}

$teacherData = $teacherQuery->fetch_assoc();
$teacher_id = $teacherData['id'];

// Fetch schedule
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
<title>My Schedule</title>
<style>
body{
    font-family:'Poppins',sans-serif;
    background:#f4f6f8;
    margin:0;
    display:flex;
}
.sidebar{
    width:260px;
    background:#1e293b;
    color:white;
    height:100vh;
    position:fixed;
}
.sidebar-header{
    padding:25px;
    background:#0f172a;
    text-align:center;
    font-weight:700;
    font-size:1.2rem;
}
.sidebar a{
    padding:15px 25px;
    color:#94a3b8;
    text-decoration:none;
    display:block;
    border-bottom:1px solid #334155;
}
.sidebar a:hover{
    background:#334155;
    color:white;
}
.main{
    margin-left:260px;
    flex:1;
    padding:30px;
}
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:30px;
}
h2,h3{
    color:#1a73e8;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
th,td{
    padding:10px;
    border:1px solid #ddd;
    text-align:center;
}
th{
    background:#1a73e8;
    color:white;
}
</style>
</head>
<body>

<div class="sidebar">
<div class="sidebar-header">SCHOOL ERP</div>
<a href="teacher_dashboard.php">🏠 Dashboard</a>
<a href="teacher_schedule.php">📘 My Schedule</a>
<a href="teacher_attendance.php">📅 Attendance</a>
<a href="teacher_marks.php">📝 Upload Marks</a>
<a href="teacher_files.php">📁 Upload Files</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
<div class="card">
<h2>Welcome <?= htmlspecialchars($teacherData['name']) ?></h2>
<h3>My Schedule</h3>

<table>
<tr>
<th>Class</th>
<th>Subject</th>
<th>Day</th>
<th>Time</th>
</tr>

<?php if($schedule && $schedule->num_rows > 0): ?>
    <?php while($row = $schedule->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['class_name']) ?></td>
        <td><?= htmlspecialchars($row['subject']) ?></td>
        <td><?= htmlspecialchars($row['day']) ?></td>
        <td><?= htmlspecialchars($row['time_slot']) ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="4">No Schedule Assigned</td>
</tr>
<?php endif; ?>

</table>
</div>
</div>

</body>
</html>
