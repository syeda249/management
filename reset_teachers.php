<?php
include 'db.php';

// Reset passwords for teachers
$conn->query("UPDATE users SET password='".password_hash("habiba123", PASSWORD_DEFAULT)."' WHERE username='habiba'");
$conn->query("UPDATE users SET password='".password_hash("ali123", PASSWORD_DEFAULT)."' WHERE username='ali'");

echo "Teachers passwords reset successfully!";
?>
