<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) { header("Location: index.php"); exit(); }

$message = "";
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';

// --- 1. SAVE ATTENDANCE LOGIC ---
if (isset($_POST['submit_attendance'])) {
    $att_date = $_POST['attendance_date'];
    if (isset($_POST['status'])) {
        foreach ($_POST['status'] as $student_id => $status) {
            $student_id = intval($student_id);
            // Check if record already exists
            $check = $conn->query("SELECT id FROM attendance WHERE student_id = $student_id AND attendance_date = '$att_date'");
            
            if ($check->num_rows > 0) {
                $conn->query("UPDATE attendance SET status = '$status' WHERE student_id = $student_id AND attendance_date = '$att_date'");
            } else {
                $conn->query("INSERT INTO attendance (student_id, attendance_date, status) VALUES ($student_id, '$att_date', '$status')");
            }
        }
        $message = "<div class='alert success'>✅ Attendance for <b>Class $selected_class</b> on $att_date has been updated!</div>";
    }
}

// --- 2. FETCH SUMMARY (Class Specific) ---
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Leave' => 0];
if ($selected_class != '') {
    $stats = $conn->query("SELECT a.status, COUNT(*) as count 
                           FROM attendance a 
                           JOIN students s ON a.student_id = s.id 
                           WHERE a.attendance_date = '$selected_date' 
                           AND s.class = '$selected_class' 
                           GROUP BY a.status");
    while($row = $stats->fetch_assoc()) { $summary[$row['status']] = $row['count']; }
}

// --- 3. FETCH STUDENTS (Class Specific) ---
$students = null;
if ($selected_class != '') {
    $query = "SELECT * FROM students WHERE status = 1 AND class = '$selected_class' ORDER BY roll_no ASC";
    $students = $conn->query($query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>School Attendance System</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c; --warning: #f1c40f; --purple: #9b59b6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .sidebar { width: 250px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .main { margin-left: 250px; flex: 1; padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* Summary UI */
        .summary-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .summary-item { flex: 1; padding: 15px; border-radius: 8px; text-align: center; color: white; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Filter UI */
        .filter-bar { display: flex; gap: 15px; background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 20px; align-items: flex-end; border: 1px solid #ddd; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        select, input[type="date"] { padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; min-width: 180px; }
        
        /* Table UI */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f9fa; padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #555; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background-color: #fcfcfc; }

        /* Buttons */
        .btn-load { background: var(--accent); color: white; border: none; padding: 11px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-save { background: var(--success); color: white; border: none; padding: 15px 40px; border-radius: 8px; cursor: pointer; float: right; margin-top: 25px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3); }
        
        /* Status Radio Buttons */
        .st-lbl { cursor: pointer; padding: 8px 15px; border-radius: 20px; border: 1px solid #ddd; font-size: 13px; font-weight: 600; transition: 0.2s; display: inline-block; }
        input[type="radio"]:checked + .st-lbl.p { background: var(--success); color: white; border-color: var(--success); }
        input[type="radio"]:checked + .st-lbl.a { background: var(--danger); color: white; border-color: var(--danger); }
        input[type="radio"]:checked + .st-lbl.l { background: var(--warning); color: white; border-color: var(--warning); }
        input[type="radio"]:checked + .st-lbl.lv { background: var(--purple); color: white; border-color: var(--purple); }
        input[type="radio"] { display: none; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .no-data { text-align: center; padding: 40px; color: #95a5a6; border: 2px dashed #ddd; border-radius: 10px; margin-top: 20px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <h2>📝 Attendance Portal: Class Wise</h2>

    <?php if($selected_class != ''): ?>
    <div class="summary-row">
        <div class="summary-item" style="background: var(--success);">Present: <?php echo $summary['Present']; ?></div>
        <div class="summary-item" style="background: var(--danger);">Absent: <?php echo $summary['Absent']; ?></div>
        <div class="summary-item" style="background: var(--warning);">Late: <?php echo $summary['Late']; ?></div>
        <div class="summary-item" style="background: var(--purple);">Leave: <?php echo $summary['Leave']; ?></div>
    </div>
    <?php endif; ?>

    <?php echo $message; ?>

    <div class="card">
        <form method="GET" class="filter-bar">
            <div class="filter-group">
                <label>Attendance Date</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>">
            </div>
            <div class="filter-group">
                <label>Select Class</label>
                <select name="class" required>
                    <option value="">-- Choose Class --</option>
                    <?php 
                    $cl_list = ['Nursery', 'Prep', '1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th'];
                    foreach($cl_list as $cl) {
                        $s = ($selected_class == $cl) ? 'selected' : '';
                        echo "<option value='$cl' $s>$cl Class</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-load">🔍 Load Students</button>
            
            <?php if($selected_class != ''): ?>
            <div style="margin-left:auto;">
                <button type="button" onclick="markAllPresent()" style="background:#f1f3f4; border:1px solid #ccc; padding:10px; border-radius:6px; cursor:pointer; font-weight:600;">✅ Mark All Present</button>
            </div>
            <?php endif; ?>
        </form>

        <?php if($selected_class == ''): ?>
            <div class="no-data">
                <h3>Select a Class to start marking attendance</h3>
                <p>Upar di gayi list se class aur date muntakhib karein.</p>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
                <table>
                    <thead>
                        <tr>
                            <th width="100">Roll No</th>
                            <th>Student Name</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($students && $students->num_rows > 0): ?>
                            <?php while($row = $students->fetch_assoc()): 
                                $sid = $row['id'];
                                $att_q = $conn->query("SELECT status FROM attendance WHERE student_id = $sid AND attendance_date = '$selected_date'");
                                $existing_status = ($att_q->num_rows > 0) ? $att_q->fetch_assoc()['status'] : 'Present';
                            ?>
                            <tr>
                                <td><span style="background:#eee; padding:4px 10px; border-radius:4px; font-weight:bold;"><?php echo $row['roll_no']; ?></span></td>
                                <td><strong><?php echo $row['name']; ?></strong></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 10px; justify-content: center;">
                                        <label>
                                            <input type="radio" name="status[<?php echo $sid; ?>]" value="Present" class="p-radio" <?php echo ($existing_status == 'Present') ? 'checked' : ''; ?>>
                                            <span class="st-lbl p">Present</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="status[<?php echo $sid; ?>]" value="Absent" <?php echo ($existing_status == 'Absent') ? 'checked' : ''; ?>>
                                            <span class="st-lbl a">Absent</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="status[<?php echo $sid; ?>]" value="Late" <?php echo ($existing_status == 'Late') ? 'checked' : ''; ?>>
                                            <span class="st-lbl l">Late</span>
                                        </label>
                                        <label>
                                            <input type="radio" name="status[<?php echo $sid; ?>]" value="Leave" <?php echo ($existing_status == 'Leave') ? 'checked' : ''; ?>>
                                            <span class="st-lbl lv">Leave</span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="no-data">Is class mein koi student registered nahi hai.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if($students && $students->num_rows > 0): ?>
                    <button type="submit" name="submit_attendance" class="btn-save">💾 Save Class <?php echo $selected_class; ?> Attendance</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    function markAllPresent() {
        if(confirm('Kya aap tamam bacho ko Present mark karna chahte hain?')) {
            document.querySelectorAll('.p-radio').forEach(radio => {
                radio.checked = true;
            });
        }
    }
</script>

</body>
</html>