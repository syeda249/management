<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) {
    exit("Access Denied");
}

/*
TABLES USED:
teachers (id, name, role)
teacher_schedule (teacher_id, class_id, subject, day, time_slot)
classes (id, class_name, section)
*/

$sql = "
SELECT 
    t.name AS teacher_name,
    t.role AS teacher_role,
    c.class_name,
    c.section,
    ts.subject,
    ts.day,
    ts.time_slot
FROM teacher_schedule ts
JOIN teachers t ON ts.teacher_id = t.id
JOIN classes c ON ts.class_id = c.id
ORDER BY t.name, ts.day
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Schedule</title>
<style>
body{font-family:sans-serif;background:#f4f4f4;padding:30px}
.card{background:#fff;padding:20px;border-radius:8px}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{padding:10px;border:1px solid #ddd;text-align:center}
th{background:#3498db;color:white}
.badge{padding:4px 8px;border-radius:4px;color:white;font-size:12px}
.primary{background:#27ae60}
.middle{background:#f39c12}
.high{background:#e74c3c}
</style>
</head>
<body>

<div class="card">
<h2>📘 Teachers Schedule Overview</h2>

<table>
<thead>
<tr>
<th>Teacher</th>
<th>Teacher Role</th>
<th>Section</th>
<th>Class</th>
<th>Subject</th>
<th>Day</th>
<th>Time</th>
</tr>
</thead>
<tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['teacher_name'] ?></td>
<td><?= $row['teacher_role'] ?></td>

<td>
<?php if($row['section']=='Primary'): ?>
<span class="badge primary">Primary</span>
<?php elseif($row['section']=='Middle'): ?>
<span class="badge middle">Middle</span>
<?php else: ?>
<span class="badge high">High</span>
<?php endif; ?>
</td>

<td><?= $row['class_name'] ?></td>
<td><?= $row['subject'] ?></td>
<td><?= $row['day'] ?></td>
<td><?= $row['time_slot'] ?></td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>

</body>
</html>
