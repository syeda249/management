<?php
include 'db.php';
$id = intval($_GET['id']);

$r = $conn->query("
SELECT ts.*, t.name 
FROM teacher_salaries ts
JOIN teachers t ON t.id=ts.teacher_id
WHERE ts.id=$id
")->fetch_assoc();

// Handle missing values safely
$total_absent = $r['total_absent'] ?? 0;
$total_present = $r['total_present'] ?? 0;
$deduction = $r['deduction'] ?? 0;
$bonus = $r['bonus'] ?? 0;
$basic = $r['basic_salary'] ?? 0;
$net_salary = $r['net_salary'] ?? 0;
$month = $r['month'] ?? '';
$status = $r['status'] ?? '';
$teacher_name = $r['name'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .slip { background: #fff; max-width: 600px; margin: auto; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; }
        p { font-size: 16px; margin: 8px 0; }
        .salary-details { border-top: 2px solid #3498db; padding-top: 10px; margin-top: 10px; }
        .net { font-size: 20px; font-weight: bold; color: #27ae60; margin-top: 15px; }
        button { margin-top: 20px; padding: 10px 20px; background: #0984e3; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0652dd; }
    </style>
</head>
<body>
<div class="slip">
    <h2>Salary Slip</h2>
    <!-- DASHBOARD BUTTON -->
    <p style="text-align:center;">
        <a href="manage_teachers.php" style="
            display:inline-block;
            padding:8px 15px;
            background:#0984e3;
            color:white;
            border-radius:5px;
            text-decoration:none;
            margin-bottom:15px;
        ">👨‍🏫 Go to Teachers Dashboard</a>
    </p>

    <p><b>Teacher:</b> <?= htmlspecialchars($teacher_name) ?></p>
    <p><b>Month:</b> <?= htmlspecialchars($month) ?></p>
    <p><b>Basic Salary:</b> <?= number_format($basic) ?></p>
    <p><b>Total Present Days:</b> <?= $total_present ?></p>
    <p><b>Total Absent Days:</b> <?= $total_absent ?></p>
    <p><b>Deduction:</b> <?= number_format($deduction) ?></p>
    <p><b>Bonus:</b> <?= number_format($bonus) ?></p>
    <div class="salary-details">
        <p class="net">Net Salary: <?= number_format($net_salary) ?></p>
        <p>Status: <?= htmlspecialchars($status) ?></p>
    </div>
    <button onclick="window.print()">Print Slip</button>
</div>


</body>
</html>
