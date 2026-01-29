<?php
// Session start karna zaroori hai taake use khatam kiya ja sakay
session_start();

// Tamam session variables ko khali kar dena
$_SESSION = array();

// Agar session cookie maujood hai toh use bhi expire kar dena (Security ke liye)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Poora session destroy karna
session_destroy();

// User ko login page par redirect karna
header("Location: index.php");
exit();
?>