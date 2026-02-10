<?php
session_start();
include 'db.php';

/* ============ ACCESS CONTROL ============ */
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    echo "<h2 style='color:red;'>Access Denied</h2>";
    exit();
}

/* ============ HANDLE SALARY GENERATION ============ */
if (isset($_POST['generate'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $month      = $_POST['month'];
    $bonus      = floatval($_POST['bonus']);

    // Get teacher role
    $t = $conn->query("SELECT role FROM teachers WHERE id=$teacher_id")->fetch_assoc();
    $cat = $t['role'];

    // Get basic salary
    $s = $conn->query("SELECT basic_salary FROM salary_slabs WHERE category='$cat'")->fetch_assoc();
    $basic = $s['basic_salary'];

    // Attendance counts for the month
    $present = $conn->query("SELECT COUNT(*) c FROM teacher_attendance
        WHERE teacher_id=$teacher_id AND status='Present'
        AND DATE_FORMAT(date,'%Y-%m')='$month'")->fetch_assoc()['c'];

    $absent = $conn->query("SELECT COUNT(*) c FROM teacher_attendance
        WHERE teacher_id=$teacher_id AND status='Absent'
        AND DATE_FORMAT(date,'%Y-%m')='$month'")->fetch_assoc()['c'];

    // Calculate salary
    $per_day   = $basic / 30;
    $deduction = $absent * $per_day;
    $net       = $basic - $deduction + $bonus;

    // Insert into teacher_salaries
    $conn->query("INSERT INTO teacher_salaries
        (teacher_id,month,basic_salary,present_days,absent_days,deduction,bonus,net_salary,status)
        VALUES ($teacher_id,'$month',$basic,$present,$absent,$deduction,$bonus,$net,'Unpaid')");

    $msg = "✅ Salary generated for " . $t['role'] . "!";
}

/* ============ FETCH TEACHERS ============ */
$teachers = $conn->query("SELECT id,name FROM teachers ORDER BY name");

/* ============ GET TEACHER ID FROM URL (IF CLICKED FROM TABLE) ============ */
$selected_teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : '';
?>

<!DOCTYPE html>
<html>
<head>
<title>Generate Salary</title>
<style>
body{font-family:sans-serif;background:#f4f4f4;margin:0;padding:30px}
input,select{padding:8px;margin:5px;width:180px}
button{padding:6px 12px;border:none;border-radius:4px;color:#fff;background:#28a745;cursor:pointer;font-size:13px}
</style>
</head>
<body>

<h2>💰 Generate Salary</h2>

<?php if(isset($msg)) echo "<p style='color:green'>$msg</p>"; ?>

<form method="POST">
<select name="teacher_id" required>
<option value="">Select Teacher</option>
<?php 
while($t=$teachers->fetch_assoc()): 
$selected = ($selected_teacher_id && $selected_teacher_id == $t['id']) ? 'selected' : '';
?>
<option value="<?= $t['id'] ?>" <?= $selected ?>><?= $t['name'] ?></option>
<?php endwhile; ?>
</select>

<input type="month" name="month" required>
<input type="number" name="bonus" value="0" placeholder="Bonus">
<br><br>
<button name="generate">Generate Salary</button>
</form>

</body>
</html>
