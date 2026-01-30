<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1️⃣ Get form data safely
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password_plain = $_POST['password'];

    // 2️⃣ Hash password
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // 3️⃣ Insert into teachers table (password included)
    $sql = "INSERT INTO teachers (name, email, password, role, created_at) 
            VALUES ('$name', '$email', '$password_hashed', 'teacher', NOW())";

    if ($conn->query($sql) === TRUE) {

        $teacher_id = $conn->insert_id; // last inserted teacher ID

        // 4️⃣ Generate username (remove spaces, lowercase)
        $username = strtolower(str_replace(' ', '', $name));

        // 5️⃣ Insert into users table AND set teacher_id
        $sql_user = "INSERT INTO users (username, password, role, email, teacher_id) 
                     VALUES ('$username', '$password_hashed', 'teacher', '$email', $teacher_id)";

        if ($conn->query($sql_user) === TRUE) {
            $user_id = $conn->insert_id;

            // 6️⃣ Update teacher with user_id
            $conn->query("UPDATE teachers SET user_id=$user_id WHERE id=$teacher_id");

            // 7️⃣ Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error creating user: " . $conn->error;
        }

    } else {
        $error = "Error adding teacher: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; background: #f4f4f4; }
        form { background: white; padding: 20px; border-radius: 8px; width: 350px; box-shadow: 0 0 10px #ccc; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        a { text-decoration: none; color: #333; }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Register New Teacher</h2>

        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

        <input type="text" name="name" placeholder="Teacher Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Add Teacher</button>
        <br><br>
        <a href="dashboard.php">Back to Dashboard</a>
    </form>
</body>
</html>
