<?php
// index.php - The Main Entry Point
session_start();
include 'db.php'; // Ensure db.php has correct $conn details

// 1. Agar user logout karna chahta hai
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 2. Agar user pehle se login hai, toh usay dashboard par bhejein
if (isset($_SESSION['role']) && !isset($_GET['force_login'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// 3. Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Plain text check (As per your current setup)
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Session Variables Set Karna
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['role'] = $user_data['role']; 
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Login Portal</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0; color: #333;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }
        .school-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        h2 { margin: 0 0 10px 0; color: var(--primary); }
        p { color: #777; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--accent); outline: none; box-shadow: 0 0 8px rgba(52, 152, 219, 0.2); }
        button {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background: #2980b9; transform: translateY(-2px); }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="school-icon">🏫</div>
    <h2>School Portal</h2>
    <p>Please enter your credentials</p>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required autofocus>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit">LOGIN</button>
    </form>
    
    <div style="margin-top: 25px; font-size: 12px; color: #aaa;">
        System Version 2.0 | Secured Access
    </div>
</div>

</body>
</html>