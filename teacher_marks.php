<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher'){
    header("Location: login.php");
    exit();
}

$teacher_user_id = intval($_SESSION['user_id']);

$teacherQuery = $conn->query("SELECT id,name FROM teachers WHERE user_id = $teacher_user_id");
if($teacherQuery->num_rows == 0){
    die("Teacher not found!");
}

$teacherData = $teacherQuery->fetch_assoc();
$teacher_id = $teacherData['id'];

// Classes
$classQuery = $conn->query("
SELECT DISTINCT c.class_name 
FROM teacher_schedule ts
JOIN classes c ON ts.class_id=c.id
WHERE ts.teacher_id=$teacher_id
");

$classes=[];
while($c=$classQuery->fetch_assoc()){
    $classes[]=$c['class_name'];
}

$students=[];
if(count($classes)>0){
    $classList = "'" . implode("','",$classes) . "'";
    $studentQuery = $conn->query("SELECT * FROM students WHERE class IN ($classList) ORDER BY class, roll_no");
    while($s=$studentQuery->fetch_assoc()){
        $students[]=$s;
    }
}

// DELETE mark
if(isset($_POST['delete']) && isset($_POST['sid']) && isset($_POST['subject']) && isset($_POST['exam_type'])){
    $sid = intval($_POST['sid']);
    $subject = mysqli_real_escape_string($conn,$_POST['subject']);
    $exam_type = mysqli_real_escape_string($conn,$_POST['exam_type']);

    $conn->query("DELETE FROM marks WHERE student_id=$sid AND subject='$subject' AND exam_type='$exam_type'");
    header("Location: teacher_marks.php?msg=deleted&subject=".urlencode($subject)."&exam_type=".urlencode($exam_type));
    exit();
}

// Save / Update Marks
if(isset($_POST['save'])){
    $subject=mysqli_real_escape_string($conn,$_POST['subject']);
    $exam_type=mysqli_real_escape_string($conn,$_POST['exam_type']);
    $date=date('Y-m-d');

    foreach($_POST['marks'] as $sid=>$mark){
        $sid=intval($sid);
        $mark=intval($mark);

        // Check if marks already exist
        $check = $conn->query("SELECT id FROM marks WHERE student_id=$sid AND subject='$subject' AND exam_type='$exam_type'");
        if($check->num_rows > 0){
            // UPDATE existing
            $conn->query("UPDATE marks SET marks=$mark, date='$date' WHERE student_id=$sid AND subject='$subject' AND exam_type='$exam_type'");
        } else {
            // INSERT new
            $conn->query("INSERT INTO marks(student_id,subject,exam_type,marks,date) 
                          VALUES($sid,'$subject','$exam_type',$mark,'$date')");
        }
    }

    header("Location: teacher_marks.php?msg=done&subject=".urlencode($subject)."&exam_type=".urlencode($exam_type));
    exit();
}

// Pre-fill marks if subject + exam_type selected
$selected_subject = $_GET['subject'] ?? '';
$selected_exam = $_GET['exam_type'] ?? '';
$existing_marks = [];

if($selected_subject && $selected_exam){
    $marksQuery = $conn->query("SELECT * FROM marks WHERE subject='$selected_subject' AND exam_type='$selected_exam'");
    while($m = $marksQuery->fetch_assoc()){
        $existing_marks[$m['student_id']] = $m['marks'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Marks</title>
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
.sidebar a:hover,.sidebar a.active{
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
}
h2{
    color:#1a73e8;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
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
input[type="text"], input[type="number"], select{
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}
button{
    padding:8px 15px;
    background:#22c55e;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    margin-top:5px;
}
button.delete{
    background:#ef4444;
}
button:hover{
    opacity:0.9;
}
.success{
    color:green;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="sidebar">
<div class="sidebar-header">SCHOOL ERP</div>
<a href="teacher_dashboard.php">🏠 Dashboard</a>
<a href="teacher_schedule.php">📘 My Schedule</a>
<a href="teacher_attendance.php">📅 Attendance</a>
<a href="teacher_marks.php" class="active">📝 Upload Marks</a>
<a href="teacher_files.php">📁 Upload Files</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">

<div class="card">

<h2>Upload Marks - <?= htmlspecialchars($teacherData['name']) ?></h2>

<?php if(isset($_GET['msg'])): ?>
<p class="success">
<?php 
if($_GET['msg']=='done') echo "Marks Saved Successfully!";
elseif($_GET['msg']=='deleted') echo "Marks Deleted Successfully!";
?>
</p>
<?php endif; ?>

<form method="GET" style="margin-bottom:20px">
<label>Subject:</label>
<input type="text" name="subject" value="<?= htmlspecialchars($selected_subject) ?>" required>

<label>Exam Type:</label>
<select name="exam_type" required>
    <option value="">Select Exam Type</option>
    <option value="Class Test" <?= $selected_exam=='Class Test'?'selected':'' ?>>Class Test</option>
    <option value="Quiz" <?= $selected_exam=='Quiz'?'selected':'' ?>>Quiz</option>
    <option value="Midterm" <?= $selected_exam=='Midterm'?'selected':'' ?>>Midterm</option>
    <option value="Final Exam" <?= $selected_exam=='Final Exam'?'selected':'' ?>>Final Exam</option>
</select>

<button type="submit">Load Marks</button>
</form>

<?php if($selected_subject && $selected_exam): ?>
<form method="POST">

<input type="hidden" name="subject" value="<?= htmlspecialchars($selected_subject) ?>">
<input type="hidden" name="exam_type" value="<?= htmlspecialchars($selected_exam) ?>">

<table>
<tr>
<th>Student Name</th>
<th>Marks</th>
<th>Action</th>
</tr>

<?php foreach($students as $s): ?>
<tr>
<td><?= htmlspecialchars($s['name']) ?></td>
<td>
<input type="number" name="marks[<?= $s['id'] ?>]" min="0" max="100" value="<?= $existing_marks[$s['id']] ?? '' ?>" required>
</td>
<td>
<?php if(isset($existing_marks[$s['id']])): ?>
<button type="submit" name="delete" class="delete" onclick="return confirm('Are you sure?')" value="1" formaction="">
<input type="hidden" name="sid" value="<?= $s['id'] ?>">
</button>
<?php else: ?>
-
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</table>

<button type="submit" name="save">💾 Save / Update Marks</button>

</form>
<?php endif; ?>

</div>
</div>

</body>
</html>
