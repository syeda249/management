<?php
session_start();
include 'db.php';

// Access Control
if (!isset($_SESSION['role'])) { header("Location: index.php"); exit(); }

$message = "";

// --- 1. ARCHIVE/RESTORE LOGIC ---
if (isset($_GET['left_id'])) {
    $id = intval($_GET['left_id']);
    $conn->query("UPDATE students SET status = 0 WHERE id = $id");
    $message = "<div class='alert danger'>✅ Student moved to Archive (Left School).</div>";
}
if (isset($_GET['restore_id'])) {
    $id = intval($_GET['restore_id']);
    $conn->query("UPDATE students SET status = 1 WHERE id = $id");
    $message = "<div class='alert success'>✅ Student restored to Active list!</div>";
}

// --- 2. REGISTRATION & UPDATE LOGIC (FIXED) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $fp_data = mysqli_real_escape_string($conn, $_POST['fingerprint_data']);

    // Check if uploads folder exists, if not, create it automatically
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $photo_query = "";
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = time() . "_" . basename($_FILES['photo']['name']);
        $target_path = "uploads/" . $photo_name;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
            $photo_query = ", photo='$photo_name'";
        } else {
            $message = "<div class='alert danger'>❌ Photo upload failed! Make sure 'uploads' folder has write permissions.</div>";
        }
    }

    if (isset($_POST['update_student'])) {
        $id = intval($_POST['student_id']);
        $sql = "UPDATE students SET name='$name', roll_no='$roll_no', class='$class', gender='$gender', phone='$phone', fingerprint_data='$fp_data' $photo_query WHERE id=$id";
        if($conn->query($sql)) $message = "<div class='alert success'>✅ Records updated successfully!</div>";
    } else {
        $final_photo = !empty($photo_name) ? $photo_name : "default.png";
        $sql = "INSERT INTO students (name, roll_no, class, gender, phone, photo, fingerprint_data, status) 
                VALUES ('$name', '$roll_no', '$class', '$gender', '$phone', '$final_photo', '$fp_data', 1)";
        if($conn->query($sql)) $message = "<div class='alert success'>✅ Student Registered Successfully!</div>";
    }
}

// --- 3. FETCH DATA ---
$edit_data = (isset($_GET['edit_id'])) ? $conn->query("SELECT * FROM students WHERE id = ".intval($_GET['edit_id']))->fetch_assoc() : null;
$count_boys = $conn->query("SELECT COUNT(*) as t FROM students WHERE gender='Male' AND status=1")->fetch_assoc()['t'];
$count_girls = $conn->query("SELECT COUNT(*) as t FROM students WHERE gender='Female' AND status=1")->fetch_assoc()['t'];
$active_students = $conn->query("SELECT * FROM students WHERE status = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Students | School Portal</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #2ecc71; --danger: #e74c3c; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .main { margin-left: 250px; flex: 1; padding: 30px; min-height: 100vh; }
        .stats-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; flex: 1; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-top: 4px solid var(--accent); }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; color: #555; }
        .std-photo { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; border: none; cursor: pointer; font-size: 12px; font-weight: bold; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid var(--success); }
        .danger { background: #f8d7da; color: #721c24; border-left: 5px solid var(--danger); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="stats-row">
        <div class="stat-card"><h3>👦 Boys: <?php echo $count_boys; ?></h3></div>
        <div class="stat-card"><h3>👧 Girls: <?php echo $count_girls; ?></h3></div>
    </div>

    <?php echo $message; ?>

    <div class="card">
        <h3><?php echo $edit_data ? "✏️ Edit Student" : "➕ Register Student"; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <?php if($edit_data): ?><input type="hidden" name="student_id" value="<?php echo $edit_data['id']; ?>"><?php endif; ?>
            <div class="form-grid">
                <input type="text" name="name" placeholder="Full Name" value="<?php echo $edit_data['name'] ?? ''; ?>" required>
                <input type="text" name="roll_no" placeholder="Roll No" value="<?php echo $edit_data['roll_no'] ?? ''; ?>" required>
                
                <select name="gender">
                    <option value="Male" <?php echo (isset($edit_data['gender']) && $edit_data['gender']=='Male')?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($edit_data['gender']) && $edit_data['gender']=='Female')?'selected':''; ?>>Female</option>
                </select>

                <select name="class" required>
                    <option value="">Select Class</option>
                    <?php 
                    $classes = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th'];
                    foreach($classes as $c) {
                        $sel = (isset($edit_data['class']) && $edit_data['class'] == $c) ? 'selected' : '';
                        echo "<option value='$c' $sel>$c Class</option>";
                    }
                    ?>
                </select>

                <input type="text" name="phone" placeholder="Parent Phone" value="<?php echo $edit_data['phone'] ?? ''; ?>">
                <input type="file" name="photo">
                
                <div style="grid-column: span 1; display:flex; align-items:center; gap:10px;">
                    <input type="hidden" name="fingerprint_data" id="fp_data" value="<?php echo $edit_data['fingerprint_data'] ?? ''; ?>">
                    <button type="button" onclick="scanFP()" style="background:#f39c12; color:white; border:none; border-radius:5px; cursor:pointer; padding:10px;">👆 Scan</button>
                    <span id="fp_msg" style="font-size:11px;"><?php echo !empty($edit_data['fingerprint_data']) ? "✅ Scanned" : "No Scan"; ?></span>
                </div>
                
                <button type="submit" name="<?php echo $edit_data ? 'update_student' : 'register_student'; ?>" class="btn" style="background:var(--success); grid-column: span 2;">
                    <?php echo $edit_data ? 'Update Student Information' : 'Register Student'; ?>
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>🟢 Active Students</h3>
        <table>
            <thead><tr><th>Photo</th><th>Roll</th><th>Name</th><th>Class</th><th>Parent Contact</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($row = $active_students->fetch_assoc()): ?>
                <tr>
                    <td><img src="uploads/<?php echo $row['photo']; ?>" class="std-photo" onerror="this.src='https://via.placeholder.com/45'"></td>
                    <td><?php echo $row['roll_no']; ?></td>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><?php echo $row['class']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td>
                        <a href="manage_students.php?edit_id=<?php echo $row['id']; ?>" class="btn" style="background:#f39c12;">Edit</a>
                        <a href="manage_students.php?left_id=<?php echo $row['id']; ?>" class="btn" style="background:var(--danger);" onclick="return confirm('Archive this student?')">Mark Left</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function scanFP() {
        let id = "FP_" + Math.random().toString(36).substr(2, 5).toUpperCase();
        document.getElementById('fp_data').value = id;
        document.getElementById('fp_msg').innerText = "✅ Scanned: " + id;
        document.getElementById('fp_msg').style.color = "green";
    }
</script>
</body>
</html>