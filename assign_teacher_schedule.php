<?php 
session_start(); 
include 'db.php';  

if (!isset($_SESSION['role']) || 
   ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    exit("Access Denied");
}

/* =========================
   ADD NEW SUBJECT
========================= */
if(isset($_POST['add_subject'])){
    $new_subject = mysqli_real_escape_string($conn, $_POST['new_subject']);

    if(!empty($new_subject)){
        $conn->query("INSERT INTO subjects (subject_name) VALUES ('$new_subject')");
        $msg = "✅ Subject Added Successfully!";
    }
}

/* =========================
   INSERT SCHEDULE
========================= */
if(isset($_POST['assign'])){
    $teacher_id  = $_POST['teacher_id'];
    $class_ids   = $_POST['class_id'];
    $subjects    = $_POST['subject'];
    $days        = $_POST['day'];
    $start_times = $_POST['start_time'];
    $end_times   = $_POST['end_time'];

    $count = count($class_ids);

    for($i=0; $i<$count; $i++){
        $class_id = $class_ids[$i];
        $subject  = $subjects[$i];
        $day      = $days[$i];
        $time_slot = $start_times[$i]." - ".$end_times[$i];

        $conn->query("INSERT INTO teacher_schedule 
            (teacher_id,class_id,subject,day,time_slot) 
            VALUES 
            ('$teacher_id','$class_id','$subject','$day','$time_slot')");
    }

    $success = "✅ Schedule Assigned Successfully!";
}

/* =========================
   FETCH TEACHERS
========================= */
$teachers = $conn->query("SELECT id,name FROM teachers");

/* =========================
   FETCH CLASSES
========================= */
$all_classes = [];
$result = $conn->query("SELECT id,class_name,section FROM classes");
while($row = $result->fetch_assoc()){
    $all_classes[] = $row;
}

/* =========================
   FETCH SUBJECTS
========================= */
$subjects_list = [];
$subjects = $conn->query("SELECT * FROM subjects");
while($s = $subjects->fetch_assoc()){
    $subjects_list[] = $s;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Assign Teacher Schedule</title>

<style>
body{font-family:sans-serif;background:#f4f4f4;padding:30px}
.card{background:#fff;padding:20px;border-radius:8px;width:800px}
select,input,button{padding:8px;width:100%;margin:6px 0}
button{background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer}
label{font-weight:bold;margin-top:10px;display:block}
.remove-btn{background:#e74c3c;color:#fff;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;margin-top:6px}
.row-container{display:flex;gap:10px;align-items:center;margin-bottom:5px}
.row-container select,.row-container input{flex:1}
</style>

<script>
let allClasses = <?php echo json_encode($all_classes); ?>;

function addRow(){
    const section = document.getElementById('section').value;
    if(!section){
        alert("Select Section first!");
        return;
    }

    const container = document.getElementById('rows');
    const row = document.createElement('div');
    row.className = 'row-container';

    let classOptions = `<option value="">Select Class</option>`;
    allClasses.forEach(c=>{
        if(c.section === section){
            classOptions += `<option value="${c.id}">${c.class_name}</option>`;
        }
    });

    let subjectOptions = `<option value="">Select Subject</option>`;
    <?php foreach($subjects_list as $sub): ?>
        subjectOptions += `<option value="<?= $sub['subject_name'] ?>">
            <?= $sub['subject_name'] ?>
        </option>`;
    <?php endforeach; ?>

    row.innerHTML = `
        <select name="class_id[]" required>${classOptions}</select>
        <select name="subject[]" required>${subjectOptions}</select>
        <select name="day[]" required>
            <option value="">Select Day</option>
            <option>Monday</option>
            <option>Tuesday</option>
            <option>Wednesday</option>
            <option>Thursday</option>
            <option>Friday</option>
            <option>Saturday</option>
        </select>
        <input type="time" name="start_time[]" required>
        <input type="time" name="end_time[]" required>
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">❌</button>
    `;

    container.appendChild(row);
}
</script>
</head>

<body>
<div class="card">

<h2>📅 Assign Teacher Schedule</h2>

<?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
<?php if(isset($msg)) echo "<p style='color:green'>$msg</p>"; ?>

<!-- =========================
     ADD SUBJECT FORM
========================= -->
<h3>➕ Add New Subject</h3>
<form method="POST">
    <input type="text" name="new_subject" placeholder="Enter Subject Name" required>
    <button type="submit" name="add_subject">Add Subject</button>
</form>

<hr>

<!-- =========================
     SCHEDULE FORM
========================= -->
<form method="POST">

<label>Teacher</label>
<select name="teacher_id" required>
    <option value="">Select Teacher</option>
    <?php while($t=$teachers->fetch_assoc()): ?>
        <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
    <?php endwhile; ?>
</select>

<label>Section</label>
<select id="section" required>
    <option value="">Select Section</option>
    <option value="Primary">Primary</option>
    <option value="Middle">Middle</option>
    <option value="High">High</option>
</select>

<label>Schedule Rows</label>
<div id="rows"></div>

<button type="button" onclick="addRow()">➕ Add Row</button>
<br><br>

<button name="assign">✅ Assign Schedule</button>

</form>
</div>
</body>
</html>
