<?php 
session_start();

// 1. Security Check: Agar login nahi hai to login page par bhej do
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('db.php');

// 2. AUTOMATIC TABLE CHECK: Ye table ko sahi columns ke sath banayega
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(10) NOT NULL
)");

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_class = isset($_GET['class']) ? mysqli_real_escape_string($conn, $_GET['class']) : '';

// 3. SAVE ATTENDANCE LOGIC
if (isset($_POST['save_attendance'])) {
    $att_date = mysqli_real_escape_string($conn, $_POST['att_date']);
    $att_class = mysqli_real_escape_string($conn, $_POST['att_class']);

    foreach ($_POST['status'] as $student_id => $status) {
        $student_id = mysqli_real_escape_string($conn, $student_id);
        $status = mysqli_real_escape_string($conn, $status);
        
        // Check if attendance already exists for this student on this date
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = '$student_id' AND date = '$att_date'");
        
        if (mysqli_num_rows($check) > 0) {
            // Update existing record
            mysqli_query($conn, "UPDATE attendance SET status = '$status' WHERE student_id = '$student_id' AND date = '$att_date'");
        } else {
            // Insert new record
            mysqli_query($conn, "INSERT INTO attendance (student_id, class, date, status) VALUES ('$student_id', '$att_class', '$att_date', '$status')");
        }
    }
    header("Location: attendance.php?date=$att_date&class=$att_class&msg=1");
    exit();
}

$ordered_classes = ['Nursery', 'Prep', '1', '2', '3', '4', '5', '6', '7', '8', '9th', '10th', '1st year', '2nd year'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance System | School Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --bg: #f1f5f9; --success: #22c55e; --danger: #ef4444; }
        body { font-family: 'Poppins', sans-serif; margin: 0; display: flex; background: var(--bg); color: #334155; }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 25px; background: #0f172a; text-align: center; font-weight: 700; font-size: 1.2rem; }
        .sidebar a { padding: 15px 25px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #334155; transition: 0.3s; }
        .sidebar a:hover, .active { background: #334155; color: white; border-left: 5px solid var(--accent); }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .filter-bar { display: flex; gap: 20px; margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 10px; align-items: flex-end; }
        .input-box { display: flex; flex-direction: column; gap: 5px; }
        input[type="date"], select { padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.95rem; outline: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .status-grp { display: flex; gap: 15px; }
        .radio-lbl { cursor: pointer; position: relative; }
        .radio-lbl input { display: none; }
        .btn-status { padding: 8px 20px; border-radius: 20px; border: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 500; display: inline-block; transition: 0.3s; }
        input[value="P"]:checked + .btn-status { background: var(--success); color: white; border-color: var(--success); }
        input[value="A"]:checked + .btn-status { background: var(--danger); color: white; border-color: var(--danger); }
        .btn-save { background: var(--accent); color: white; border: none; padding: 15px 40px; border-radius: 10px; cursor: pointer; font-weight: 600; float: right; margin-top: 30px; }
        .msg { background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; }
        .btn-all-p { padding: 10px 20px; background: #fff; border: 1px solid #3b82f6; color: #3b82f6; border-radius: 8px; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL ERP</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php" class="active">📅 Mark Attendance</a>
    <a href="attendance_report.php">📊 Attendance Report</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h2>Student Attendance Portal</h2>
    
    <?php if(isset($_GET['msg'])) echo "<div class='msg'>✅ Attendance updated successfully for Class $selected_class!</div>"; ?>

    <div class="card">
        <form method="GET" class="filter-bar">
            <div class="input-box">
                <label>Attendance Date</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
            </div>
            <div class="input-box">
                <label>Select Class</label>
                <select name="class" onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php foreach($ordered_classes as $cl): ?>
                        <option value="<?php echo $cl; ?>" <?php if($selected_class == $cl) echo 'selected'; ?>>Class <?php echo $cl; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if($selected_class): ?>
            <form method="POST">
                <input type="hidden" name="att_date" value="<?php echo $selected_date; ?>">
                <input type="hidden" name="att_class" value="<?php echo $selected_class; ?>">
                
                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Status (P/A)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $s_query = "SELECT * FROM students WHERE class = '$selected_class' ORDER BY roll_no ASC";
                        $students = mysqli_query($conn, $s_query);

                        if(mysqli_num_rows($students) > 0):
                            while($row = mysqli_fetch_assoc($students)):
                                $sid = $row['id'];
                                $att_res = mysqli_query($conn, "SELECT status FROM attendance WHERE student_id = '$sid' AND date = '$selected_date'");
                                $att_row = mysqli_fetch_assoc($att_res);
                                $status = $att_row ? $att_row['status'] : '';
                        ?>
                        <tr>
                            <td><b>#<?php echo $row['roll_no']; ?></b></td>
                            <td><?php echo strtoupper($row['name']); ?></td>
                            <td>
                                <div class="status-grp">
                                    <label class="radio-lbl">
                                        <input type="radio" name="status[<?php echo $sid; ?>]" value="P" class="p-check" <?php if($status == 'P') echo 'checked'; ?> required>
                                        <span class="btn-status">Present</span>
                                    </label>
                                    <label class="radio-lbl">
                                        <input type="radio" name="status[<?php echo $sid; ?>]" value="A" <?php if($status == 'A') echo 'checked'; ?> required>
                                        <span class="btn-status">Absent</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="3" style="text-align:center; padding:30px;">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if(mysqli_num_rows($students) > 0): ?>
                    <button type="button" class="btn-all-p" onclick="allPresent()">✅ Mark All Present</button>
                    <button type="submit" name="save_attendance" class="btn-save">💾 Save Attendance</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    function allPresent() {
        document.querySelectorAll('.p-check').forEach(el => el.checked = true);
    }
</script>

</body>
</html>