<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

$message = "";
$id = $_GET['id'] ?? null;

// Agar ID nahi mili toh wapas bhej do
if (!$id) { header("Location: manage_students.php"); exit(); }

// --- 1. GET STUDENT DATA ---
$res = $conn->query("SELECT * FROM students WHERE id = '$id'");
$student = $res->fetch_assoc();

if (!$student) { echo "Student not found!"; exit(); }

// --- 2. UPDATE LOGIC ---
if (isset($_POST['update_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $roll = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = $_POST['gender'];
    $status = $_POST['status'];

    // Photo Update Logic
    $photo_query = "";
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = time() . "_" . $_FILES['photo']['name'];
        if (move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo_name)) {
            $photo_query = ", photo = '$photo_name'";
            // Purani photo delete karna chaho toh yahan unlink kar sakte ho
        }
    }

    $sql = "UPDATE students SET 
            name = '$name', 
            class = '$class', 
            roll_no = '$roll', 
            phone = '$phone', 
            gender = '$gender', 
            status = '$status' 
            $photo_query 
            WHERE id = '$id'";

    if ($conn->query($sql)) {
        $message = "<div class='alert success'>✅ Student profile updated successfully!</div>";
        // Data refresh karein taake form mein naya data dikhay
        $res = $conn->query("SELECT * FROM students WHERE id = '$id'");
        $student = $res->fetch_assoc();
    } else {
        $message = "<div class='alert danger'>❌ Error: " . $conn->error . "</div>";
    }
}

$all_classes = ['Nursery', 'Prep', '1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student | <?php echo $student['name']; ?></title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        .main { margin-left: 260px; padding: 40px; flex: 1; }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-update { background: #2ecc71; color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; margin-top: 20px; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: bold; }
        .current-photo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent); margin-bottom: 10px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; }
        .danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <a href="manage_students.php" class="btn-back">⬅️ Back to List</a>
    
    <div class="card">
        <center>
            <img src="uploads/<?php echo $student['photo']; ?>" class="current-photo" onerror="this.src='https://via.placeholder.com/100'">
            <h2 style="margin: 10px 0;">Edit Profile: <?php echo $student['name']; ?></h2>
        </center>

        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
                </div>
                <div>
                    <label>Roll Number</label>
                    <input type="text" name="roll_no" value="<?php echo $student['roll_no']; ?>" required>
                </div>
                <div>
                    <label>Class</label>
                    <select name="class">
                        <?php foreach($all_classes as $c): ?>
                            <option value="<?php echo $c; ?>" <?php if($student['class'] == $c) echo 'selected'; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Gender</label>
                    <select name="gender">
                        <option value="Male" <?php if($student['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if($student['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div>
                    <label>Contact Number</label>
                    <input type="text" name="phone" value="<?php echo $student['phone']; ?>">
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="1" <?php if($student['status'] == 1) echo 'selected'; ?>>Active</option>
                        <option value="0" <?php if($student['status'] == 0) echo 'selected'; ?>>Inactive / Left</option>
                    </select>
                </div>
                <div style="grid-column: span 2;">
                    <label>Update Photo (Leave blank to keep current)</label>
                    <input type="file" name="photo">
                </div>
            </div>

            <button type="submit" name="update_student" class="btn-update">💾 Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>