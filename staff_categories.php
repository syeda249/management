<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    exit("Access Denied");
}

function getStaff($conn,$where){
    return $conn->query("SELECT * FROM teachers WHERE $where");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Categories</title>
<style>
body{font-family:sans-serif;background:#f4f4f4;padding:20px}
.card{background:#fff;padding:15px;margin-bottom:20px;border-radius:8px}
h2{background:#3498db;color:#fff;padding:8px}
</style>
</head>
<body>

<div class="card">
<h2>Principal</h2>
<?php $r=getStaff($conn,"role='Principal'"); while($row=$r->fetch_assoc()) echo $row['name']."<br>"; ?>
</div>

<div class="card">
<h2>Vice Principal</h2>
<?php $r=getStaff($conn,"role='Vice Principal'"); while($row=$r->fetch_assoc()) echo $row['name']."<br>"; ?>
</div>

<div class="card">
<h2>Discipline Committee</h2>
<?php $r=getStaff($conn,"committee LIKE '%Discipline%'"); while($row=$r->fetch_assoc()) echo $row['name']."<br>"; ?>
</div>

<div class="card">
<h2>Activities / Programs</h2>
<?php 
$r=getStaff($conn,"activity IS NOT NULL AND activity!=''");
while($row=$r->fetch_assoc()){
    echo $row['name']." (".$row['activity'].")<br>";
}
?>
</div>

</body>
</html>
