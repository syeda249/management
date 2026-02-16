<?php
session_start();
include 'db.php'; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']); 
    $selected_class = $_POST['class_name'] ?? '';

    if (!empty($selected_class)) {
        // --- STUDENT LOGIN ---
        $sql = "SELECT * FROM students WHERE TRIM(roll_no) = '$username' AND TRIM(class) = '$selected_class' LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $db_pass = trim($row['password']);

            // Simple string comparison for plain text passwords
            if ($password === $db_pass) {
                $_SESSION['student_id'] = $row['id'];
                $_SESSION['student_name'] = $row['name'];
                $_SESSION['role'] = 'student';
                $_SESSION['class'] = $row['class'];
                header("Location: student_dashboard.php");
                exit();
            } else {
                $error = "Incorrect Password! Please try again.";
            }
        } else {
            $error = "No student found with Roll No: $username in Class: $selected_class";
        }
    } else {
        // --- ADMIN / STAFF LOGIN ---
        $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                header("Location: dashboard.php");
                exit();
            }
        }
        $error = "Invalid Admin Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System - Login</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #1a73e8; margin-bottom: 20px; }
        label { font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; display: block; }
        select, input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccd0d5; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #1a73e8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #1557b0; }
        .error-box { background: #ffebee; color: #c62828; padding: 10px; border-radius: 6px; text-align: center; font-size: 13px; margin-bottom: 15px; border: 1px solid #ffcdd2; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>School Portal</h2>
        
        <?php if($error): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Login As (Select Class for Students)</label>
            <select name="class_name">
                <option value="">-- Admin / Staff Login --</option>
                <option value="Nursery">Nursery</option>
                <option value="Prep">Prep</option>
                <option value="KG">KG</option>
                <option value="1st">1st</option>
                <option value="2">2nd</option>
                <option value="3">3rd</option>
                <option value="4th">4th</option>
                <option value="5th">5th</option>
                <option value="6th">6th</option>
                <option value="7th">7th</option>
                <option value="8th">8th</option>
                <option value="9th">9th</option>
                <option value="10th">10th</option>
                <option value="11th">11th</option>
                <option value="12th">12th</option>
            </select>

            <label>Roll Number or Username</label>
            <input type="text" name="username" placeholder="e.g. 101 or admin" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>