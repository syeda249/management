<?php
include 'db.php';
$id = $_GET['id'];

$conn->query("UPDATE teacher_salaries
SET status='Paid', paid_at=NOW()
WHERE id=$id");

header("Location: salaries_list.php");
