<?php
session_start();
include 'db.php';

// Only teachers can access
if(!isset($_SESSION['role']) || $_SESSION['role']!=='teacher'){
    header("Location: login.php"); exit();
}

$teacher_user_id = $_SESSION['user_id'];

// Get teacher record
$teacher = $conn->query("SELECT id, name FROM teachers WHERE user_id=$teacher_user_id");
$teacherData = $teacher->fetch_assoc();
$teacher_id = $teacherData['id'] ?? die("Teacher not found!");

// Fetch schedule
$schedule = $conn->query("
    SELECT c.id AS class_id, c.class_name, ts.subject, ts.day, ts.time_slot
    FROM teacher_schedule ts
    JOIN classes c ON ts.class_id = c.id
    WHERE ts.teacher_id = $teacher_id
    ORDER BY FIELD(ts.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), ts.time_slot
");

// Prepare class list for students
$class_ids = [];
while($row = $schedule->fetch_assoc()){
    $class_ids[$row['class_id']] = $row['class_name'];
}
$schedule->data_seek(0);

// --- Students grouped by class ---
$students_by_class = [];
if(count($class_ids) > 0){
    $ids_str = implode(',', array_keys($class_ids));
    $students_result = $conn->query("
        SELECT * FROM students 
        WHERE class IN (SELECT class_name FROM classes WHERE id IN ($ids_str))
        ORDER BY class, name
    ");
    while($s = $students_result->fetch_assoc()){
        $students_by_class[$s['class']][] = $s;
    }
}

// --- Attendance Save ---
if(isset($_POST['save_attendance'])){
    $att_date = mysqli_real_escape_string($conn,$_POST['att_date']);
    $att_class = mysqli_real_escape_string($conn,$_POST['att_class']);
    foreach($_POST['status'] as $sid=>$status){
        $sid = intval($sid);
        $status = mysqli_real_escape_string($conn,$status);
        $check = mysqli_query($conn,"SELECT id FROM attendance WHERE student_id=$sid AND date='$att_date'");
        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn,"UPDATE attendance SET status='$status' WHERE student_id=$sid AND date='$att_date'");
        } else {
            mysqli_query($conn,"INSERT INTO attendance (student_id,class,date,status) VALUES ($sid,'$att_class','$att_date','$status')");
        }
    }
    header("Location: teacher_dashboard.php?msg=att");
    exit();
}

// --- Marks Save ---
if(isset($_POST['save_marks'])){
    $subject = mysqli_real_escape_string($conn,$_POST['subject']);
    $att_class = mysqli_real_escape_string($conn,$_POST['class']);
    foreach($_POST['marks'] as $sid=>$mark){
        $sid = intval($sid);
        $mark = intval($mark);
        $date = date('Y-m-d');
        $check = mysqli_query($conn,"SELECT id FROM marks WHERE student_id=$sid AND subject='$subject' AND class='$att_class'");
        if(mysqli_num_rows($check) > 0){
            mysqli_query($conn,"UPDATE marks SET marks=$mark, date='$date' WHERE student_id=$sid AND subject='$subject' AND class='$att_class'");
        } else {
            mysqli_query($conn,"INSERT INTO marks (student_id,class,subject,marks,date) VALUES ($sid,'$att_class','$subject',$mark,'$date')");
        }
    }
    header("Location: teacher_dashboard.php?msg=marks");
    exit();
}

// --- File Upload ---
if(isset($_POST['upload_file']) && isset($_FILES['file'])){
    $att_class = mysqli_real_escape_string($conn,$_POST['class']);
    $file = $_FILES['file'];
    $upload_dir = 'uploads/';
    if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);
    $file_name = time().'_'.basename($file['name']);
    $target = $upload_dir.$file_name;
    if(move_uploaded_file($file['tmp_name'],$target)){
        mysqli_query($conn,"INSERT INTO uploaded_files (teacher_id,class,file_name,uploaded_at) VALUES ($teacher_id,'$att_class','$file_name',NOW())");
        header("Location: teacher_dashboard.php?msg=file"); exit();
    }
}

// --- Attendance Report ---
$attendance_report = [];
foreach($students_by_class as $class => $students){
    foreach($students as $s){
        $sid = $s['id'];
        $total = mysqli_query($conn,"SELECT COUNT(*) AS total FROM attendance WHERE student_id=$sid")->fetch_assoc()['total'] ?? 0;
        $present = mysqli_query($conn,"SELECT COUNT(*) AS present FROM attendance WHERE student_id=$sid AND status='P'")->fetch_assoc()['present'] ?? 0;
        $percentage = ($total > 0) ? round(($present/$total)*100,2) : 0;
        $attendance_report[$sid] = $percentage;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Teacher Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f4f6f8;margin:0;display:flex}
.sidebar{width:260px;background:#1e293b;color:white;height:100vh;position:fixed}
.sidebar-header{padding:25px;background:#0f172a;text-align:center;font-weight:700;font-size:1.2rem}
.sidebar a{padding:15px 25px;color:#94a3b8;text-decoration:none;display:block;border-bottom:1px solid #334155;transition:0.3s}
.sidebar a:hover,.active{background:#334155;color:white;border-left:5px solid #3b82f6}
.main{margin-left:260px;flex:1;padding:30px}
.card{background:white;padding:20px;border-radius:12px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,0.05)}
h2,h3{color:#1a73e8}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#1a73e8;color:#fff}
.btn{padding:8px 15px;border:none;border-radius:8px;cursor:pointer;color:white;font-weight:600;margin-top:5px}
.btn-accent{background:#3b82f6}
.btn-success{background:#22c55e}
.btn-danger{background:#ef4444}
.logout{display:inline-block;margin-top:15px;color:red;text-decoration:none}
</style>
</head>
<body>

<div class="sidebar">
<div class="sidebar-header">SCHOOL ERP</div>

<a href="teacher_dashboard.php" class="active">🏠 Dashboard</a>
<a href="teacher_schedule.php">📘 My Schedule</a>
<a href="teacher_attendance.php">📅 Attendance</a>
<a href="teacher_marks.php">📝 Upload Marks</a>
<a href="teacher_files.php">📁 Upload Files</a>
<a href="logout.php">🚪 Logout</a>

</div>

<div class="main">
<h2>Welcome <?= htmlspecialchars($teacherData['name']) ?></h2>

<!-- Schedule Section -->
<div class="card" id="schedule">
<h3>My Schedule</h3>
<table>
<tr>
<th>Class</th>
<th>Subject</th>
<th>Day</th>
<th>Time</th>
</tr>
<?php if($schedule->num_rows > 0): ?>
<?php while($row = $schedule->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['class_name']) ?></td>
<td><?= htmlspecialchars($row['subject']) ?></td>
<td><?= htmlspecialchars($row['day']) ?></td>
<td><?= htmlspecialchars($row['time_slot']) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No Schedule Assigned</td></tr>
<?php endif; ?>
</table>
</div>

<?php foreach($students_by_class as $class_name => $students): ?>
<!-- Attendance Section -->
<div class="card">
<h3>Mark Attendance - Class <?= htmlspecialchars($class_name) ?></h3>
<form method="POST">
<input type="hidden" name="att_date" value="<?= date('Y-m-d') ?>">
<input type="hidden" name="att_class" value="<?= htmlspecialchars($class_name) ?>">
<table>
<tr><th>Roll</th><th>Student</th><th>Status</th></tr>
<?php foreach($students as $s): ?>
<tr>
<td><?= $s['roll_no'] ?></td>
<td><?= htmlspecialchars($s['name']) ?></td>
<td>
<select name="status[<?= $s['id'] ?>]">
<option value="P">Present</option>
<option value="A">Absent</option>
</select>
</td>
</tr>
<?php endforeach; ?>
</table>
<button type="submit" name="save_attendance" class="btn btn-accent">💾 Save Attendance</button>
</form>
</div>

<!-- Marks Upload Section -->
<div class="card">
<h3>Upload Marks - Class <?= htmlspecialchars($class_name) ?></h3>
<form method="POST">
    <!-- Hidden field for current class -->
    <input type="hidden" name="class" value="<?= htmlspecialchars($class_name) ?>">

    <!-- Subject Dropdown -->
    Subject:
    <select name="subject" required>
        <option value="">--Select Subject--</option>
        <?php
        // Get class ID for current class
        $class_id_query = $conn->query("
            SELECT id FROM classes 
            WHERE class_name='".mysqli_real_escape_string($conn, $class_name)."' 
            LIMIT 1
        ");
        $class_id_row = $class_id_query->fetch_assoc();
        $class_id = $class_id_row['id'] ?? 0;

        if($class_id){
            // Fetch subjects for this teacher and class, join with subjects table for proper names
            $subj_result = $conn->query("
                SELECT DISTINCT s.id, s.subject_name
                FROM teacher_schedule ts
                JOIN subjects s ON ts.subject = s.id
                WHERE ts.teacher_id=$teacher_id AND ts.class_id=$class_id
                ORDER BY s.subject_name
            ");
            while($subj = $subj_result->fetch_assoc()){
                echo '<option value="'.htmlspecialchars($subj['id']).'">'.htmlspecialchars($subj['subject_name']).'</option>';
            }
        }
        ?>
    </select>

    <!-- Students and Marks Inputs -->
    <table>
        <tr><th>Student</th><th>Marks</th></tr>
        <?php foreach($students as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td>
                <input type="number" name="marks[<?= $s['id'] ?>]" min="0" max="100" required>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <button type="submit" name="save_marks" class="btn btn-success">💾 Save Marks</button>
</form>
</div>


<!-- Attendance Report Section -->
<div class="card">
<h3>Attendance Report - Class <?= htmlspecialchars($class_name) ?></h3>
<table>
<tr><th>Student</th><th>Class</th><th>Attendance %</th></tr>
<?php foreach($students as $s): ?>
<tr>
<td><?= htmlspecialchars($s['name']) ?></td>
<td><?= htmlspecialchars($class_name) ?></td>
<td><?= $attendance_report[$s['id']] ?>%</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<!-- File Upload Section -->
<div class="card">
<h3>Upload Files - Class <?= htmlspecialchars($class_name) ?></h3>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="class" value="<?= htmlspecialchars($class_name) ?>">
<input type="file" name="file" required>
<button type="submit" name="upload_file" class="btn btn-accent">📤 Upload</button>
</form>
</div>

<?php endforeach; ?>
</div>
</body>
</html>
