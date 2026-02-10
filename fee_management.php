<?php
include 'db.php'; 

$message = "";

// 1. DELETE FEE RECORD (New Feature)
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    if ($conn->query("DELETE FROM student_fees WHERE id = '$del_id'")) {
        $message = "<div class='alert' style='background: #f8d7da; color: #721c24; border-left: 5px solid #f5c6cb;'>🗑️ Record Deleted Successfully!</div>";
    }
}

// 2. AUTO-FILL FEE (AJAX Logic) - Yeh amount automatic lane ke liye hai
if (isset($_POST['get_class_fee'])) {
    $sid = $_POST['student_id'];
    $res = $conn->query("SELECT fs.monthly_fee FROM students s JOIN fee_structure fs ON s.class = fs.class_name WHERE s.id = $sid");
    if ($res && $r = $res->fetch_assoc()) {
        echo $r['monthly_fee'];
    } else {
        echo "0";
    }
    exit;
}

// 3. COLLECT NEW FEE (With Duplicate Check)
if (isset($_POST['collect_fee'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $amount_paid = mysqli_real_escape_string($conn, $_POST['amount']);
    $month = mysqli_real_escape_string($conn, $_POST['fee_month']);
    $year = date('Y');
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $payment_date = date('Y-m-d');

    // Duplicate Check: Ek student ki usi month ki fee dobara na ho
    $check_sql = "SELECT id FROM student_fees WHERE student_id = '$student_id' AND month = '$month' AND year = '$year' AND status = 'Paid'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert' style='background: #fff3cd; color: #856404; border-left: 5px solid #ffeeba;'>⚠️ Error: This student's fee for <b>$month</b> is already paid!</div>";
    } else {
        $sql = "INSERT INTO student_fees (student_id, month, year, amount_paid, discount, payment_date, status) 
                VALUES ('$student_id', '$month', '$year', '$amount_paid', '0.00', '$payment_date', '$status')";
        if ($conn->query($sql)) {
            $message = "<div class='alert success'>✅ Success: Fee recorded successfully!</div>";
        }
    }
}

// 4. SETUP CLASS FEES
if (isset($_POST['save_all_fees'])) {
    foreach ($_POST['fees'] as $class_name => $fee) {
        $fee = mysqli_real_escape_string($conn, $fee);
        $conn->query("INSERT INTO fee_structure (class_name, monthly_fee) 
                      VALUES ('$class_name', '$fee') 
                      ON DUPLICATE KEY UPDATE monthly_fee = '$fee'");
    }
    $message = "<div class='alert success'>✅ Success: Fee structure updated!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Management | School Portal</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; z-index: 1000; }
        .sidebar-header { padding: 20px; background: #1a252f; text-align: center; font-weight: bold; font-size: 20px; }
        .sidebar a { padding: 15px 25px; color: #bdc3c7; text-decoration: none; display: block; border-bottom: 1px solid #34495e; transition: 0.3s; }
        .sidebar a:hover { background: #34495e; color: white; padding-left: 35px; }
        .active-link { background: var(--accent) !important; color: white !important; border-left: 5px solid white; }

        .main { margin-left: 260px; flex: 1; padding: 30px; width: calc(100% - 260px); }
        
        .summary-scroll { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 15px; margin-bottom: 25px; }
        .widget { background: white; padding: 15px; border-radius: 12px; min-width: 160px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-bottom: 4px solid var(--accent); flex-shrink: 0; }
        .widget h4 { margin: 0; font-size: 11px; color: #7f8c8d; text-transform: uppercase; }
        .widget h2 { margin: 5px 0 0; color: var(--primary); font-size: 18px; }

        .layout-grid { display: grid; grid-template-columns: 1.4fr 0.6fr; gap: 25px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        input, select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .btn { padding: 10px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-collect { background: var(--primary); color: white; width: 100%; padding: 12px; }
        .btn-receipt { background: #e67e22; color: white; font-size: 11px; margin-right: 5px; }
        .btn-delete { background: var(--danger); color: white; font-size: 11px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f1f1; font-size: 13px; }
        .badge-Paid { background: #e8f8f0; color: #27ae60; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid var(--success); }

        #receiptModal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .receipt-card { background: white; width: 350px; margin: 60px auto; padding: 30px; border-radius: 15px; position: relative; }
        
        @media print {
            body * { visibility: hidden; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; left: 0; top: 0; width: 100% !important; border:none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_teachers.php">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php">🛠️ Manage Staff</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="attendance_report.php">📊 Attendance Report</a>
    <a href="fee_management.php" class="active-link">💰 Fee Management</a>
    <a href="setting.php">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="summary-scroll">
        <?php 
        $cl_list = ['Nursery','Prep','1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th'];
        foreach($cl_list as $cl) {
            $q = "SELECT SUM(f.amount_paid) as total FROM student_fees f JOIN students s ON f.student_id = s.id WHERE s.class = '$cl' AND f.status = 'Paid'";
            $res_cl = $conn->query($q);
            $total_cl = ($res_cl) ? $res_cl->fetch_assoc()['total'] : 0;
            echo "<div class='widget'><h4>$cl</h4><h2>Rs. ".number_format($total_cl ?? 0)."</h2></div>";
        }
        ?>
    </div>

    <?php echo $message; ?>

    <div class="layout-grid">
        <div class="left-section">
            <div class="card" style="margin-bottom: 25px;">
                <h3>📝 Collect New Fee</h3>
                <form method="POST">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div>
                            <label>Student</label>
                            <select name="student_id" id="studentSelect" required onchange="fetchAmount(this.value)">
                                <option value="">-- Choose Student --</option>
                                <?php 
                                $st_res = $conn->query("SELECT id, name, class FROM students ORDER BY name ASC");
                                while($s = $st_res->fetch_assoc()) echo "<option value='{$s['id']}'>{$s['name']} ({$s['class']})</option>";
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>Amount (Rs.)</label>
                            <input type="number" name="amount" id="feeAmount" required>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <select name="fee_month">
                            <?php $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                            foreach($months as $m) {
                                $selected = ($m == date('F')) ? 'selected' : '';
                                echo "<option value='$m' $selected>$m</option>";
                            } ?>
                        </select>
                        <select name="status">
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <button type="submit" name="collect_fee" class="btn btn-collect">Save Payment Record</button>
                </form>
            </div>

            <div class="card">
                <h3>📜 Recent History (Class-wise)</h3>
                <table>
                    <thead><tr><th>Name</th><th>Class</th><th>Month</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $history = $conn->query("SELECT f.*, s.name, s.class FROM student_fees f JOIN students s ON f.student_id = s.id ORDER BY f.id DESC LIMIT 15");
                        while($h = $history->fetch_assoc()){
                        ?>
                        <tr>
                            <td><?php echo $h['name']; ?></td>
                            <td><b style="color:var(--accent);"><?php echo $h['class']; ?></b></td>
                            <td><?php echo $h['month']; ?></td>
                            <td>Rs. <?php echo number_format($h['amount_paid']); ?></td>
                            <td><span class="badge-Paid"><?php echo $h['status']; ?></span></td>
                            <td style="white-space:nowrap;">
                                <button type="button" class="btn btn-receipt" onclick="openReceipt('<?php echo $h['name']; ?>', '<?php echo $h['class']; ?>', '<?php echo $h['month']; ?>', '<?php echo $h['amount_paid']; ?>')">Print 🖨️</button>
                                <a href="fee_management.php?delete_id=<?php echo $h['id']; ?>" class="btn btn-delete" onclick="return confirm('Pakka delete karna hai?')">Delete 🗑️</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right-section">
            <div class="card">
                <h3>⚙️ Setup Class Fees</h3>
                <form method="POST">
                    <table>
                        <?php 
                        foreach($cl_list as $cl){
                            $f_res = $conn->query("SELECT monthly_fee FROM fee_structure WHERE class_name='$cl'");
                            $f_val = ($f_res && $row = $f_res->fetch_assoc()) ? $row['monthly_fee'] : '0';
                            echo "<tr><td><b>$cl</b></td><td><input type='number' name='fees[$cl]' value='$f_val' style='width:75px; padding:5px;'></td></tr>";
                        }
                        ?>
                    </table>
                    <button type="submit" name="save_all_fees" class="btn btn-collect" style="margin-top:10px; background:var(--accent);">Update All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="receiptModal" class="no-print">
    <div class="receipt-card" id="printableArea">
        <div style="text-align:center; border-bottom:2px solid #333; padding-bottom:10px; margin-bottom:15px;">
            <h2 style="margin:0;">SCHOOL FEE RECEIPT</h2>
        </div>
        <div style="font-size:14px; line-height:1.8;">
            <p><strong>Date:</strong> <?php echo date('d-M-Y'); ?></p>
            <p><strong>Student:</strong> <span id="rName"></span></p>
            <p><strong>Class:</strong> <span id="rClass"></span></p>
            <p><strong>Month:</strong> <span id="rMonth"></span></p>
            <hr style="border:1px dashed #ccc">
            <h3 style="text-align:right;">Paid: Rs. <span id="rAmount"></span></h3>
        </div>
        <div class="no-print" style="margin-top:25px; display:flex; gap:10px;">
            <button onclick="window.print()" class="btn" style="background:var(--success); color:white; flex:1;">Print</button>
            <button onclick="document.getElementById('receiptModal').style.display='none'" class="btn" style="background:#ddd; flex:1;">Close</button>
        </div>
    </div>
</div>

<script>
// Automatic Amount Fetching
function fetchAmount(sid) {
    if(!sid) return;
    let fd = new FormData();
    fd.append('get_class_fee', 1);
    fd.append('student_id', sid);
    fetch('fee_management.php', { method: 'POST', body: fd })
    .then(res => res.text()).then(data => { document.getElementById('feeAmount').value = data.trim(); });
}

function openReceipt(name, sclass, month, amount) {
    document.getElementById('rName').innerText = name;
    document.getElementById('rClass').innerText = sclass;
    document.getElementById('rMonth').innerText = month;
    document.getElementById('rAmount').innerText = amount;
    document.getElementById('receiptModal').style.display = 'block';
}
</script>
</body>
</html>