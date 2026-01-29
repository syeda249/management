<?php
session_start();
include 'db.php';

// 1. Session Check
if (!isset($_SESSION['role'])) { 
    header("Location: login.php"); 
    exit(); 
}

$username = $_SESSION['username'];
$message = "";

// 2. Database Fetch with Safety (Fixes Line 11 Error)
$school_res = $conn->query("SELECT * FROM school_settings WHERE id=1");
if (!$school_res || $school_res->num_rows == 0) {
    // Agar table khali ho toh error ke bajaye ek dummy row bana dein
    $conn->query("INSERT INTO school_settings (id, school_name) VALUES (1, 'Your School Name')");
    $school_res = $conn->query("SELECT * FROM school_settings WHERE id=1");
}
$school = $school_res->fetch_assoc();
$saved_fees = json_decode($school['class_fees'] ?? '{}', true);

// 3. Update School Profile Logic
if (isset($_POST['update_school'])) {
    $s_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $s_phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $s_email = mysqli_real_escape_string($conn, $_POST['email']);
    $s_address = mysqli_real_escape_string($conn, $_POST['address']);
    $s_session = mysqli_real_escape_string($conn, $_POST['current_session']);
    
    // Fee data ko JSON mein convert karna
    $fees = [];
    for($i=1; $i<=10; $i++) {
        $fees["Class $i"] = $_POST["fee_$i"] ?? 0;
    }
    $fees_json = mysqli_real_escape_string($conn, json_encode($fees));

    $update_sql = "UPDATE school_settings SET 
                   school_name='$s_name', phone='$s_phone', email='$s_email', 
                   address='$s_address', current_session='$s_session', 
                   class_fees='$fees_json' WHERE id=1";
    
    if($conn->query($update_sql)) {
        $message = "<div class='alert success'>✅ Settings saved successfully!</div>";
        header("Refresh:1"); // Data update hone ke baad page reload
    } else {
        $message = "<div class='alert danger'>❌ Error: " . $conn->error . "</div>";
    }
}

// 4. Update Password Logic
if (isset($_POST['update_password'])) {
    $curr_p = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_p = mysqli_real_escape_string($conn, $_POST['new_password']);

    $chk = $conn->query("SELECT id FROM users WHERE username='$username' AND password='$curr_p'");
    if($chk && $chk->num_rows > 0) {
        $conn->query("UPDATE users SET password='$new_p' WHERE username='$username'");
        $message = "<div class='alert success'>✅ Password updated successfully!</div>";
    } else {
        $message = "<div class='alert danger'>❌ Current password is incorrect!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Configuration | <?php echo htmlspecialchars($school['school_name']); ?></title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; --success: #2ecc71; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        
        /* Sidebar and Main Content Layout Fix */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; min-height: 100vh; }
        
        .container { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        h3 { color: var(--primary); border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #555; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        
        /* Fee Grid Styling */
        .fee-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8f9fa; padding: 15px; border-radius: 10px; }
        .fee-item { display: flex; align-items: center; gap: 10px; }
        .fee-item span { min-width: 60px; font-weight: bold; font-size: 13px; }

        .btn { border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; color: white; transition: 0.3s; }
        .btn-save { background: var(--success); }
        .btn-pass { background: var(--accent); }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid var(--success); }
        .danger { background: #f8d7da; color: #721c24; border-left: 5px solid var(--danger); }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1 style="color: var(--primary);">⚙️ System Configuration</h1>
        <?php echo $message; ?>

        <div class="container">
            <div class="card">
                <h3>🏫 School Profile</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text" name="school_name" value="<?php echo htmlspecialchars($school['school_name']); ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($school['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Official Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($school['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Academic Session</label>
                        <select name="current_session">
                            <option value="2024-2025" <?php if(($school['current_session']??'')=='2024-2025') echo 'selected'; ?>>2024-2025</option>
                            <option value="2025-2026" <?php if(($school['current_session']??'')=='2025-2026') echo 'selected'; ?>>2025-2026</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="2"><?php echo htmlspecialchars($school['address'] ?? ''); ?></textarea>
                    </div>

                    <h3>💰 Monthly Fee Structure</h3>
                    <div class="fee-grid">
                        <?php for($i=1; $i<=10; $i++): ?>
                        <div class="fee-item">
                            <span>Class <?php echo $i; ?></span>
                            <input type="number" name="fee_<?php echo $i; ?>" value="<?php echo $saved_fees["Class $i"] ?? ''; ?>" placeholder="0.00">
                        </div>
                        <?php endfor; ?>
                    </div>

                    <button type="submit" name="update_school" class="btn btn-save" style="margin-top: 20px;">💾 Save Configuration</button>
                </form>
            </div>

            <div class="card">
                <h3>🔒 Password Security</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required placeholder="New password">
                    </div>
                    <button type="submit" name="update_password" class="btn btn-pass">🔄 Update Password</button>
                </form>
                
                <div style="margin-top: 30px; padding: 20px; background: #eef2f7; border-radius: 10px; font-size: 14px;">
                    <p><strong>Logged User:</strong> <?php echo $username; ?></p>
                    <p><strong>System Role:</strong> <span class="role-badge"><?php echo strtoupper($_SESSION['role']); ?></span></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>