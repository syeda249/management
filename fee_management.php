<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) { header("Location: index.php"); exit(); }

$message = "";

// --- 1. COLLECT FEE LOGIC ---
if (isset($_POST['collect_fee'])) {
    $sid = intval($_POST['student_id']);
    $month = mysqli_real_escape_string($conn, $_POST['fee_month']);
    $year = intval($_POST['fee_year']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $p_date = date('Y-m-d');

    // Check if already paid
    $check = $conn->query("SELECT id FROM student_fees WHERE student_id = $sid AND month = '$month' AND year = $year");
    
    if ($check->num_rows > 0) {
        $message = "<div class='alert' style='background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px;'>⚠️ Error: Fee already recorded for this student for $month $year!</div>";
    } else {
        $sql = "INSERT INTO student_fees (student_id, month, year, amount_paid, payment_date) 
                VALUES ($sid, '$month', $year, '$amount', '$p_date')";
        if ($conn->query($sql)) {
            $message = "<div class='alert' style='background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;'>✅ Success: Fee collected and receipt generated!</div>";
        }
    }
}

// --- 2. FETCH RECORDS ---
$fee_records = $conn->query("SELECT f.*, s.name, s.roll_no, s.class 
                             FROM student_fees f 
                             JOIN students s ON f.student_id = s.id 
                             ORDER BY f.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fee Management System</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .main { margin-left: 250px; flex: 1; padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        
        .fee-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: flex-end; }
        label { font-size: 13px; font-weight: bold; color: #555; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box; }
        
        .btn-collect { background: var(--success); color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-collect:hover { background: #27ae60; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        
        .btn-print { background: #ebf5ff; color: #007bff; border: 1px solid #007bff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-print:hover { background: #007bff; color: white; }
        
        @media print { .sidebar, .fee-form, h2, .btn-collect { display: none !important; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <h2>💰 School Fee Management</h2>
    <?php echo $message; ?>

    <div class="card">
        <h3 style="margin-top:0;">Collect New Payment</h3>
        <form method="POST" class="fee-form">
            <div>
                <label>Student Name (Roll No)</label>
                <select name="student_id" required>
                    <option value="">-- Search Student --</option>
                    <?php 
                    $students = $conn->query("SELECT id, name, roll_no FROM students WHERE status=1");
                    while($s = $students->fetch_assoc()) {
                        echo "<option value='".$s['id']."'>".$s['name']." (".$s['roll_no'].")</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>For Month</label>
                <select name="fee_month">
                    <?php 
                    $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    foreach($months as $m) {
                        $sel = (date('F') == $m) ? "selected" : "";
                        echo "<option value='$m' $sel>$m</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Year</label>
                <input type="number" name="fee_year" value="2026">
            </div>
            <div>
                <label>Amount (Rs.)</label>
                <input type="number" name="amount" placeholder="5000" required>
            </div>
            <button type="submit" name="collect_fee" class="btn-collect">💾 Save & Print</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Recent Transactions</h3>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Details</th>
                        <th>Class</th>
                        <th>Month/Year</th>
                        <th>Amount</th>
                        <th>Date Paid</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $fee_records->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td>
                            <strong><?php echo $row['name']; ?></strong><br>
                            <small style="color:#666;"><?php echo $row['roll_no']; ?></small>
                        </td>
                        <td><?php echo $row['class']; ?></td>
                        <td><?php echo $row['month']." ".$row['year']; ?></td>
                        <td style="font-weight:bold; color:var(--success);">Rs. <?php echo number_format($row['amount_paid']); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['payment_date'])); ?></td>
                        <td>
                            <a href="print_receipt.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn-print">🖨️ Receipt</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Sidebar highlight
    document.getElementById('nav-fee').classList.add('active-link');
</script>

</body>
</html>