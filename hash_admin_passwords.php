<?php
include 'db.php';

// passwords
$superPass = password_hash("super123", PASSWORD_DEFAULT);
$adminPass = password_hash("admin123", PASSWORD_DEFAULT);

// update super admin
$conn->query("UPDATE users SET password='$superPass' WHERE username='super'");

// update admin
$conn->query("UPDATE users SET password='$adminPass' WHERE username='admin1'");

echo "✅ Admin & Super Admin passwords hashed successfully-y!";

