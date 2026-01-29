<?php
include 'db.php';
$id = $_GET['id'];
$data = $conn->query("SELECT f.*, s.name, s.roll_no, s.class FROM student_fees f JOIN students s ON f.student_id = s.id WHERE f.id = $id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?php echo $id; ?></title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .receipt-box { width: 400px; border: 2px solid #333; padding: 20px; margin: auto; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .content { margin-top: 20px; line-height: 2; }
        .footer { margin-top: 30px; text-align: right; font-style: italic; border-top: 1px solid #eee; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-box">
        <div class="header">
            <h2 style="margin:0;">SCHOOL NAME</h2>
            <p style="margin:5px;">Official Fee Receipt</p>
        </div>
        <div class="content">
            <b>Receipt No:</b> #<?php echo $data['id']; ?><br>
            <b>Roll No:</b> <?php echo $data['roll_no']; ?><br>
            <b>Student Name:</b> <?php echo $data['name']; ?><br>
            <b>Class:</b> <?php echo $data['class']; ?><br>
            <b>Fee Month:</b> <?php echo $data['month']." ".$data['year']; ?><br>
            <hr>
            <b style="font-size: 20px;">Amount Paid: Rs. <?php echo number_format($data['amount_paid']); ?></b><br>
            <b>Date:</b> <?php echo date('d-M-Y', strtotime($data['payment_date'])); ?>
        </div>
        <div class="footer">
            Cashier Signature: ________________
        </div>
    </div>
    <div style="text-align:center; margin-top:20px;" class="no-print">
        <button onclick="window.print()">Print Again</button>
    </div>
</body>
</html>