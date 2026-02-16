<?php
session_start();
include('db.php');

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// 2. Handle Profile & Password Update
if (isset($_POST['update_settings'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Username update
    $update_user = mysqli_query($conn, "UPDATE users SET username = '$username' WHERE id = '$user_id'");
    
    if ($update_user) {
        $_SESSION['username'] = $username; // Session update
        $msg = "Profile updated successfully!";
    }

    // Password update logic (agar fill kiya ho)
    if (!empty($new_pass)) {
        if ($new_pass === $confirm_pass) {
            // Note: Real world mein password_hash() use karna chahiye
            $update_pass = mysqli_query($conn, "UPDATE users SET password = '$new_pass' WHERE id = '$user_id'");
            $msg = "Profile and Password updated successfully!";
        } else {
            $error = "Passwords do not match!";
        }
    }
}

// 3. Fetch current user data
$user_data = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Settings | School ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1e293b; --accent: #3b82f6; --bg: #f1f5f9; }
        body { font-family: 'Poppins', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .sidebar-header { padding: 25px; background: #0f172a; text-align: center; font-weight: 700; }
        .sidebar a { padding: 15px 25px; color: #94a3b8; text-decoration: none; display: block; border-bottom: 1px solid #334155; }
        .sidebar a:hover, .active { background: #334155; color: white; border-left: 5px solid var(--accent); }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #64748b; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; outline: none; }
        .btn-save { background: var(--accent); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 10px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { background: #dcfce7; color: #166534; }
        .danger { background: #fee2e2; color: #991b1b; }
        .info-box { background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid var(--accent); margin-bottom: 25px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">SCHOOL ERP</div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_students.php">🎓 Manage Students</a>
    <a href="attendance.php">📅 Mark Attendance</a>
    <a href="attendance_report.php">📊 Attendance Report</a>
    <a href="setting.php" class="active">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <h2>⚙️ Profile Settings</h2>
    
    <div class="card">
        <div class="info-box">
            <p style="margin:0; font-size:14px;">Logged in as: <b><?php echo strtoupper($user['role']); ?></b></p>
        </div>

        <?php if($msg) echo "<div class='alert success'>✅ $msg</div>"; ?>
        <?php if($error) echo "<div class='alert danger'>❌ $error</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <hr style="border:0; border-top:1px solid #eee; margin:30px 0;">
            <p style="font-size: 13px; color: #94a3b8; margin-bottom: 15px;">Leave password fields blank if you don't want to change it.</p>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password">
            </div>

            <button type="submit" name="update_settings" class="btn-save">💾 Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>