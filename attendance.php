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
        $message = "<div class='alert success'>✅ Attendance for $att_date has been saved and updated!</div>";
    }
}

// --- 2. FETCH SUMMARY ---
$stats = $conn->query("SELECT status, COUNT(*) as count FROM attendance WHERE attendance_date = '$selected_date' GROUP BY status");
$summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Leave' => 0];
while($row = $stats->fetch_assoc()) { $summary[$row['status']] = $row['count']; }

// --- 3. FETCH STUDENTS ---
$query = "SELECT * FROM students WHERE status = 1";
if ($selected_class != '') { $query .= " AND class = '$selected_class'"; }
$students = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Professional Attendance System</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c; --warning: #f1c40f; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        /* Sidebar Reuse Styles */
        .sidebar { width: 250px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar a { padding: 15px 25px; color: #bdc3c7; text-decoration: none; display: block; border-bottom: 1px solid #34495e; }
        .active-link { background: var(--accent) !important; color: white !important; }

        .main { margin-left: 250px; flex: 1; padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* Summary Box */
        .summary-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .summary-item { flex: 1; padding: 15px; border-radius: 8px; text-align: center; color: white; font-weight: bold; }

        .filter-bar { display: flex; gap: 15px; background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 20px; align-items: flex-end; border: 1px solid #eee; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f9fa; color: #333; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px; border-bottom: 1px solid #eee; }

        .btn-save { background: var(--success); color: white; border: none; padding: 15px 40px; border-radius: 8px; cursor: pointer; float: right; margin-top: 20px; font-size: 16px; font-weight: bold; }
        
        /* Status Labels */
        .st-lbl { cursor: pointer; padding: 6px 12px; border-radius: 20px; border: 1px solid #ddd; font-size: 13px; transition: 0.2s; }
        input[type="radio"]:checked + .st-lbl.p { background: var(--success); color: white; border-color: var(--success); }
        input[type="radio"]:checked + .st-lbl.a { background: var(--danger); color: white; border-color: var(--danger); }
        input[type="radio"]:checked + .st-lbl.l { background: var(--warning); color: white; border-color: var(--warning); }
        input[type="radio"]:checked + .st-lbl.lv { background: #9b59b6; color: white; border-color: #9b59b6; }
        input[type="radio"] { display: none; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <h2>📝 Student Attendance Portal</h2>
    
    <div class="summary-row">
        <div class="summary-item" style="background: var(--success);">Present: <?php echo $summary['Present']; ?></div>
        <div class="summary-item" style="background: var(--danger);">Absent: <?php echo $summary['Absent']; ?></div>
        <div class="summary-item" style="background: var(--warning);">Late: <?php echo $summary['Late']; ?></div>
        <div class="summary-item" style="background: #9b59b6;">Leave: <?php echo $summary['Leave']; ?></div>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <form method="GET" class="filter-bar">
            <div>
                <label>Select Date</label><br>
                <input type="date" name="date" value="<?php echo $selected_date; ?>" onchange="this.form.submit()" style="padding:10px; border-radius:5px; border:1px solid #ddd;">
            </div>
            <div>
                <label>Filter by Class</label><br>
                <select name="class" onchange="this.form.submit()" style="padding:10px; border-radius:5px; border:1px solid #ddd; width:150px;">
                    <option value="">All Classes</option>
                    <?php 
                    $cl_list = ['1st', '2nd', '3rd', '10th', 'bscs'];
                    foreach($cl_list as $cl) {
                        $s = ($selected_class == $cl) ? 'selected' : '';
                        echo "<option value='$cl' $s>$cl Class</option>";
                    }
                    ?>
                </select>
            </div>
            <div style="margin-left:auto;">
                <button type="button" onclick="markAllPresent()" style="background:#eee; border:1px solid #ccc; padding:10px; border-radius:5px; cursor:pointer;">✅ Mark All Present</button>
            </div>
        </form>

        <form method="POST">
            <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
            <table>
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th style="text-align: center;">Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students->fetch_assoc()): 
                        $sid = $row['id'];
                        $att_q = $conn->query("SELECT status FROM attendance WHERE student_id = $sid AND attendance_date = '$selected_date'");
                        $existing_status = ($att_q->num_rows > 0) ? $att_q->fetch_assoc()['status'] : 'Present';
                    ?>
                    <tr>
                        <td><?php echo $row['roll_no']; ?></td>
                        <td><strong><?php echo $row['name']; ?></strong></td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
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
                </tbody>
            </table>
            <button type="submit" name="submit_attendance" class="btn-save">💾 Save Attendance Records</button>
        </form>
    </div>
</div>

<script>
    // Sidebar highlight
    document.getElementById('nav-attend').classList.add('active-link');

    // Bulk action: Mark all as present
    function markAllPresent() {
        document.querySelectorAll('.p-radio').forEach(radio => {
            radio.checked = true;
        });
    }
</script>

</body>
</html>