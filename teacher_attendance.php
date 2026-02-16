<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role']!=='teacher'){
    header("Location: login.php"); exit();
}

$teacher_user_id = intval($_SESSION['user_id']);
$teacherQuery = $conn->query("SELECT id,name FROM teachers WHERE user_id=$teacher_user_id");
if($teacherQuery->num_rows==0) die("Teacher not found!");
$teacherData = $teacherQuery->fetch_assoc();
$teacher_id = $teacherData['id'];

// Get classes
$classQuery = $conn->query("SELECT DISTINCT c.class_name FROM teacher_schedule ts JOIN classes c ON ts.class_id=c.id WHERE ts.teacher_id=$teacher_id");
$classes = [];
while($c=$classQuery->fetch_assoc()) $classes[]=$c['class_name'];

// Students
$students=[];
if(count($classes)>0){
    $cls = "'" . implode("','",$classes) . "'";
    $studentQuery = $conn->query("SELECT * FROM students WHERE class IN ($cls) ORDER BY class, roll_no");
    while($s=$studentQuery->fetch_assoc()) $students[]=$s;
}

// AJAX update
if(isset($_POST['ajax_update'])){
    $sid = intval($_POST['sid']);
    $status = $_POST['status']=='P'?'P':'A';
    $date = $_POST['date'];
    $today = date('Y-m-d');
    if($date<=$today){
        $check = $conn->query("SELECT id FROM attendance WHERE student_id=$sid AND date='$date'");
        if($check->num_rows>0){
            $conn->query("UPDATE attendance SET status='$status' WHERE student_id=$sid AND date='$date'");
        }else{
            $conn->query("INSERT INTO attendance(student_id,date,status) VALUES($sid,'$date','$status')");
        }
        echo "success";
    }else{
        echo "future";
    }
    exit();
}

// Delete
if(isset($_GET['delete']) && isset($_GET['sid']) && isset($_GET['date'])){
    $sid = intval($_GET['sid']);
    $date = $_GET['date'];
    if($date<=date('Y-m-d')){
        $conn->query("DELETE FROM attendance WHERE student_id=$sid AND date='$date'");
        header("Location: teacher_attendance.php?msg=deleted");
        exit();
    }
}

// Month filter
$filter_month = $_GET['month'] ?? date('Y-m');
$start_date = $filter_month."-01";
$end_date = date("Y-m-t", strtotime($start_date));

// Attendance data
$attendance_report=[];
foreach($students as $s){
    $sid=$s['id'];
    $rec = $conn->query("SELECT date,status FROM attendance WHERE student_id=$sid AND date BETWEEN '$start_date' AND '$end_date' ORDER BY date DESC");
    $attendance_report[$sid]=[];
    while($r=$rec->fetch_assoc()) $attendance_report[$sid][]=$r;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Attendance</title>
<style>
body{font-family:Poppins,sans-serif;background:#f4f6f8;margin:0;display:flex}
.sidebar{width:260px;background:#1e293b;color:white;height:100vh;position:fixed}
.sidebar-header{padding:25px;background:#0f172a;text-align:center;font-weight:700;font-size:1.2rem}
.sidebar a{padding:15px 25px;color:#94a3b8;text-decoration:none;display:block;border-bottom:1px solid #334155;}
.sidebar a:hover,.active{background:#334155;color:white;}
.main{margin-left:260px;flex:1;padding:30px}
.card{background:white;padding:25px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin-bottom:30px;}
h2,h3{color:#1a73e8}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#1a73e8;color:white}
select,input[type=date],button{padding:6px 12px;border-radius:6px;border:1px solid #ccc;cursor:pointer;}
button{background:#3b82f6;color:white;border:none;font-weight:600;margin-top:10px}
.success{color:green;margin-bottom:15px}
.delete-btn{background:#ef4444;color:white;padding:4px 8px;border-radius:6px;text-decoration:none;}
</style>
<script>
function updateStatus(sid,date,sel){
    var status=sel.value;
    var xhr=new XMLHttpRequest();
    xhr.open("POST","teacher_attendance.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onreadystatechange=function(){
        if(xhr.readyState==4 && xhr.status==200){
            if(xhr.responseText=="success"){sel.style.borderColor="green";}
            else if(xhr.responseText=="future"){alert("Cannot edit future date!"); sel.value=sel.getAttribute('data-old');}
        }
    };
    xhr.send("ajax_update=1&sid="+sid+"&date="+date+"&status="+status);
}
</script>
</head>
<body>

<div class="sidebar">
<div class="sidebar-header">SCHOOL ERP</div>
<a href="teacher_dashboard.php">🏠 Dashboard</a>
<a href="teacher_schedule.php">📘 My Schedule</a>
<a href="teacher_attendance.php" class="active">📅 Attendance</a>
<a href="teacher_marks.php">📝 Upload Marks</a>
<a href="teacher_files.php">📁 Upload Files</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
<div class="card">
<h2>Attendance - <?= htmlspecialchars($teacherData['name']) ?></h2>
<?php if(isset($_GET['msg'])): ?>
<p class="success"><?= $_GET['msg']=='deleted'?"Attendance deleted":"Saved!" ?></p>
<?php endif; ?>

<form method="POST">
<label>Date:</label>
<input type="date" name="att_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
<table>
<tr><th>Roll</th><th>Name</th><th>Class</th><th>Status</th></tr>
<?php foreach($students as $s): ?>
<tr>
<td><?= $s['roll_no'] ?></td>
<td><?= htmlspecialchars($s['name']) ?></td>
<td><?= $s['class'] ?></td>
<td>
<select name="status[<?= $s['id'] ?>]">
<option value="P">Present</option>
<option value="A">Absent</option>
</select>
</td>
</tr>
<?php endforeach; ?>
</table>
<button type="submit" name="save_attendance">Save Attendance</button>
</form>
</div>

<div class="card">
<h3>Attendance Report</h3>
<form method="GET">
<label>Filter by Month:</label>
<input type="month" name="month" value="<?= $filter_month ?>" onchange="this.form.submit()">
</form>

<table>
<tr><th>Roll</th><th>Name</th><th>Class</th><th>Date</th><th>Status</th><th>Action</th></tr>
<?php foreach($students as $s): ?>
<?php foreach($attendance_report[$s['id']] as $rec): ?>
<tr>
<td><?= $s['roll_no'] ?></td>
<td><?= htmlspecialchars($s['name']) ?></td>
<td><?= $s['class'] ?></td>
<td><?= date('d M Y',strtotime($rec['date'])) ?></td>
<td>
<select data-old="<?= $rec['status'] ?>" onchange="updateStatus(<?= $s['id'] ?>,'<?= $rec['date'] ?>',this)">
<option value="P" <?= $rec['status']=='P'?'selected':'' ?>>Present</option>
<option value="A" <?= $rec['status']=='A'?'selected':'' ?>>Absent</option>
</select>
</td>
<td>
<?php if($rec['date']<=date('Y-m-d')): ?>
<a href="teacher_attendance.php?delete=1&sid=<?= $s['id'] ?>&date=<?= $rec['date'] ?>" class="delete-btn" onclick="return confirm('Delete?')">Delete</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; endforeach; ?>
</table>
</div>
</div>
</body>
</html>
