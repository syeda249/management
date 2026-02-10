<?php
include 'db.php';
$data = $conn->query("
SELECT ts.*, t.name 
FROM teacher_salaries ts
JOIN teachers t ON t.id=ts.teacher_id
ORDER BY ts.id DESC
");
?>

<h2>📄 Salaries List</h2>

<table border="1" cellpadding="8">
<tr>
<th>Teacher</th><th>Month</th><th>Net</th><th>Status</th><th>Action</th>
</tr>

<?php while($r=$data->fetch_assoc()): ?>
<tr>
<td><?= $r['name'] ?></td>
<td><?= $r['month'] ?></td>
<td><?= $r['net_salary'] ?></td>
<td><?= $r['status'] ?></td>
<td>
<?php if($r['status']=='Unpaid'): ?>
<a href="pay_salary.php?id=<?= $r['id'] ?>">Mark Paid</a>
<?php endif; ?>
 | <a href="salary_slip.php?id=<?= $r['id'] ?>">Slip</a>
</td>
</tr>
<?php endwhile; ?>
</table>
