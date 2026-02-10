<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) { 
    header("Location: login.php"); 
    exit(); 
}

$message = "";

// --- 1. AUTO ROLL NO LOGIC ---
if (isset($_POST['get_roll'])) {
    $cls = mysqli_real_escape_string($conn, $_POST['class']);
    $res = $conn->query("SELECT MAX(CAST(roll_no AS UNSIGNED)) as max_roll FROM students WHERE class='$cls'");
    $row = $res->fetch_assoc();
    echo ($row['max_roll'] ? $row['max_roll'] + 1 : 1);
    exit;
}

// --- 2. REGISTRATION LOGIC ---
if (isset($_POST['register_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $roll = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $class = mysqli_real_escape_string($conn, $_POST['class']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = $roll . "_" . time() . "@school.com"; // Unique email generation
    
    $photo = "default.png";
    if(!empty($_FILES['photo']['name'])){
        $photo = time()."_".$_FILES['photo']['name'];
        if(!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/".$photo);
    }

    $sql = "INSERT INTO students (name, email, class, phone, monthly_fee, gender, roll_no, photo, status, password) 
            VALUES ('$name', '$email', '$class', '$phone', 0, '$gender', '$roll', '$photo', 1, '123456')";
    
    if($conn->query($sql)) {
        $message = "<div class='alert success'>✅ Student <b>$name</b> registered successfully in Class <b>$class</b>!</div>";
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
    <title>New Admission | School Management</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #2ecc71; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); }
        .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; }
        .main { margin-left: 260px; flex: 1; padding: 40px; min-height: 100vh; }
        
        .card { background: white; padding: 35px; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.07); max-width: 900px; margin: auto; }
        .form-title { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 25px; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input, select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; font-size: 15px; }
        input[readonly] { background: #f9f9f9; cursor: not-allowed; }
        
        .btn-reg { background: var(--accent); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 17px; margin-top: 10px; transition: 0.3s; }
        .btn-reg:hover { background: #2980b9; transform: translateY(-2px); }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid var(--success); }
        .danger { background: #f8d7da; color: #721c24; border-left: 5px solid #e74c3c; }
        
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <a href="manage_students.php" class="back-link">⬅ Back to Dashboard</a>

    <div class="card">
        <h2 class="form-title">📝 New Student Admission Form</h2>
        
        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <label>Student Full Name</label>
                    <input type="text" name="name" placeholder="Enter student's name" required>
                </div>

                <div>
                    <label>Select Class</label>
                    <select name="class" id="classSelect" required>
                        <option value="">-- Choose Class --</option>
                        <?php foreach($all_classes as $c) echo "<option value='$c'>$c</option>"; ?>
                    </select>
                </div>

                <div>
                    <label>Roll Number (Auto-Generated)</label>
                    <input type="text" name="roll_no" id="rollNo" placeholder="Select class first" readonly>
                </div>

                <div>
                    <label>Gender</label>
                    <select name="gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div>
                    <label>Guardian Contact Number</label>
                    <input type="text" name="phone" placeholder="e.g. 03001234567">
                </div>

                <div>
                    <label>Student Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

                <div class="full-width">
                    <button type="submit" name="register_student" class="btn-reg">Submit Admission</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Logic to fetch next roll number automatically when class is selected
document.getElementById('classSelect').addEventListener('change', function() {
    let cls = this.value;
    if(cls) {
        fetch('admission.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'get_roll=1&class=' + encodeURIComponent(cls)
        })
        .then(res => res.text())
        .then(data => { document.getElementById('rollNo').value = data; });
    } else {
        document.getElementById('rollNo').value = "";
    }
});
</script>
</body>
</html>