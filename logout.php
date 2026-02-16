<?php
session_start(); // Session shuru karein taake khatam kar saken

// Saare session variables ko khali karein
$_SESSION = array();

// Agar session cookie hai toh usay bhi expire karein
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Pura session destroy (khatam) karein
session_destroy();

// Logout ke baad wapis login page (index.php) par bhej dein
header("Location: index.php");
exit();
?>