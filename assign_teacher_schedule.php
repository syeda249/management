<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    exit("Access Denied");
}

/* Insert schedule */
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

/* Fetch teachers */
$teachers = $conn->query("SELECT id,name FROM teachers");

/* Fetch classes */
$all_classes = [];
$result = $conn->query("SELECT id,class_name,section FROM classes");
while($row = $result->fetch_assoc()){
    $all_classes[] = $row;
}

/* Subjects */
$subjects_list = ['Mathematics','English','Science','Physics','Chemistry','Biology','Computer','History','Geography'];
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
        subjectOptions += `<option value="<?= $sub ?>"><?= $sub ?></option>`;
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
