<?php
include 'db.php';

if(isset($_POST['mark'])){
    $teacher_id = $_POST['teacher_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    $conn->query("INSERT INTO teacher_attendance (teacher_id,date,status)
                  VALUES ($teacher_id,'$date','$status')");
}
$teachers = $conn->query("SELECT * FROM teachers");
?>
<form method="POST">
<select name="teacher_id">
<?php while($t=$teachers->fetch_assoc()): ?>
<option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
<?php endwhile; ?>
</select>

<input type="date" name="date" required>

<select name="status">
<option>Present</option>
<option>Absent</option>
</select>

<button name="mark">Mark Attendance</button>
</form>
