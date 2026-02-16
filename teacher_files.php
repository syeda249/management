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

// Upload File
if(isset($_POST['upload']) && isset($_FILES['file'])){
    $upload_dir="uploads/";
    if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);

    $file_name=time().'_'.basename($_FILES['file']['name']);
    $target=$upload_dir.$file_name;

    if(move_uploaded_file($_FILES['file']['tmp_name'],$target)){
        $conn->query("INSERT INTO uploaded_files(teacher_id,file_name,uploaded_at) 
                      VALUES($teacher_id,'$file_name',NOW())");
        header("Location: teacher_files.php?msg=done");
        exit();
    }
}

// Get Uploaded Files
$files = $conn->query("SELECT * FROM uploaded_files 
                       WHERE teacher_id=$teacher_id 
                       ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Upload Files</title>
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
}
h2{
    color:#1a73e8;
}
input[type="file"]{
    padding:8px;
    border-radius:6px;
    border:1px solid #ccc;
}
button{
    padding:10px 18px;
    background:#6366f1;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}
button:hover{
    opacity:0.9;
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
<a href="teacher_marks.php">📝 Upload Marks</a>
<a href="teacher_files.php">📁 Upload Files</a>
<a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
<div class="card">

<h2>Upload Files - <?= htmlspecialchars($teacherData['name']) ?></h2>

<?php if(isset($_GET['msg'])): ?>
<p class="success">File Uploaded Successfully!</p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<input type="file" name="file" required>
<button type="submit" name="upload">📤 Upload</button>
</form>

<h3 style="margin-top:30px;">Your Uploaded Files</h3>

<table>
<tr>
<th>File Name</th>
<th>Upload Date</th>
<th>Download</th>
</tr>

<?php while($row=$files->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['file_name']) ?></td>
<td><?= $row['uploaded_at'] ?></td>
<td>
<a href="uploads/<?= $row['file_name'] ?>" target="_blank">
<button style="background:#22c55e;">Download</button>
</a>
</td>
</tr>
<?php endwhile; ?>

</table>

</div>
</div>

</body>
</html>
