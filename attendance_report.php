<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) { header("Location: index.php"); exit(); }

// Filter parameters
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$search_name = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Student fetching with filters
$query = "SELECT id, name, roll_no, class FROM students WHERE status = 1";
if ($selected_class) { $query .= " AND class = '$selected_class'"; }
if ($search_name) { $query .= " AND (name LIKE '%$search_name%' OR roll_no LIKE '%$search_name%')"; }
$students = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance Report</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .main { margin-left: 260px; flex: 1; padding: 25px; transition: 0.3s; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* Filters Styling */
        .filter-header { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: #fff; padding: 15px; border-radius: 10px; border: 1px solid #eee; flex-wrap: wrap; }
        .filter-header input, .filter-header select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-search { background: var(--accent); color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; }
        .btn-print { background: #34495e; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; margin-left: auto; }

        /* Professional Report Table */
        .report-wrapper { overflow-x: auto; position: relative; border-radius: 8px; border: 1px solid #eee; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 11px; white-space: nowrap; }
        .report-table th, .report-table td { border: 1px solid #eee; padding: 8px; text-align: center; }
        
        /* Sticky Column Logic */
        .report-table th { background: var(--primary); color: white; position: sticky; top: 0; z-index: 10; }
        .sticky-col { position: sticky; left: 0; background: #fdfdfd !important; z-index: 5; text-align: left; font-weight: bold; border-right: 2px solid #ddd !important; }
        
        /* Attendance Symbols Colors */
        .P { background: #e8f5e9; color: #2e7d32; font-weight: bold; }
        .A { background: #ffebee; color: #c62828; font-weight: bold; }
        .L { background: #fffde7; color: #f9a825; font-weight: bold; }
        .LV { background: #f3e5f5; color: #7b1fa2; font-weight: bold; }
        
        .percentage-cell { font-weight: bold; background: #f8f9fa; }
        .low-att { color: var(--danger); }

        /* Print Settings */
        @media print {
            .sidebar, .filter-header, .btn-print { display: none !important; }
            .main { margin-left: 0 !important; padding: 0 !important; }
            .sticky-col { position: static !important; border: 1px solid #ddd !important; }
            .card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h2>📊 Monthly Attendance Report</h2>
        <button class="btn-print" onclick="window.print()">🖨️ Print as PDF</button>
    </div>

    <div class="card">
        <form method="GET" class="filter-header">
            <input type="text" name="search" placeholder="Search Name or Roll No..." value="<?php echo htmlspecialchars($search_name); ?>">
            
            <select name="month">
                <?php for($m=1; $m<=12; $m++): $v = sprintf("%02d", $m); ?>
                    <option value="<?php echo $v; ?>" <?php if($month == $v) echo 'selected'; ?>><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                <?php endfor; ?>
            </select>

            <select name="year">
                <?php for($y=2024; $y<=2026; $y++): ?>
                    <option value="<?php echo $y; ?>" <?php if($year == $y) echo 'selected'; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>

            <select name="class">
                <option value="">All Classes</option>
                <?php 
                $classes = ['1st', '2nd', '3rd', '10th', 'bscs'];
                foreach($classes as $c) {
                    $sel = ($selected_class == $c) ? 'selected' : '';
                    echo "<option value='$c' $sel>$c Class</option>";
                }
                ?>
            </select>

            <button type="submit" class="btn-search">🔍 Filter</button>
        </form>

        <div class="report-wrapper">
            <table class="report-table">
                <thead>
                    <tr>
                        <th class="sticky-col">Student Name & Roll No</th>
                        <?php for($d=1; $d<=$days_in_month; $d++) echo "<th>$d</th>"; ?>
                        <th style="background:#27ae60;">P</th>
                        <th style="background:#c0392b;">A</th>
                        <th style="background:#34495e;">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($students->num_rows > 0): ?>
                        <?php while($s = $students->fetch_assoc()): 
                            $sid = $s['id'];
                            $p_count = 0; $a_count = 0;
                        ?>
                        <tr>
                            <td class="sticky-col">
                                <?php echo $s['name']; ?><br>
                                <small style="color:#777; font-weight:normal;"><?php echo $s['roll_no']; ?></small>
                            </td>
                            <?php for($d=1; $d<=$days_in_month; $d++): 
                                $current_date = "$year-$month-" . sprintf("%02d", $d);
                                $att_data = $conn->query("SELECT status FROM attendance WHERE student_id = $sid AND attendance_date = '$current_date'")->fetch_assoc();
                                
                                $status = $att_data['status'] ?? '-';
                                $short = ($status != '-') ? substr($status, 0, 1) : '-';
                                if($status == 'Leave') $short = 'LV'; // special case for Leave

                                // Statistics logic
                                if($status == 'Present' || $status == 'Late') $p_count++;
                                if($status == 'Absent') $a_count++;

                                echo "<td class='$status'>$short</td>";
                            endfor; 
                            
                            $percent = round(($p_count / $days_in_month) * 100);
                            ?>
                            <td class="percentage-cell" style="color:green;"><?php echo $p_count; ?></td>
                            <td class="percentage-cell" style="color:red;"><?php echo $a_count; ?></td>
                            <td class="percentage-cell <?php echo ($percent < 75) ? 'low-att' : ''; ?>">
                                <?php echo $percent; ?>%
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo $days_in_month + 4; ?>">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; display: flex; gap: 20px; font-size: 13px;">
            <div><span style="color:green; font-weight:bold;">P:</span> Present</div>
            <div><span style="color:red; font-weight:bold;">A:</span> Absent</div>
            <div><span style="color:orange; font-weight:bold;">L:</span> Late (Counts as P)</div>
            <div><span style="color:purple; font-weight:bold;">LV:</span> Leave</div>
        </div>
    </div>
</div>

<script>
    // Sidebar highlight
    document.getElementById('nav-attend').classList.add('active-link');
</script>

</body>
</html>