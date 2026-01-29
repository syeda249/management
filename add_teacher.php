<?php
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];

    $sql = "INSERT INTO teachers (name, email, subject) VALUES ('$name', '$email', '$subject')";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
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
    </style>
</head>
<body>
    <form method="POST">
        <h2>Register New Teacher</h2>
        <input type="text" name="name" placeholder="Teacher Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="subject" placeholder="Subject" required>
        <button type="submit">Add Teacher</button>
        <br><br>
        <a href="dashboard.php">Back to Dashboard</a>
    </form>
</body>
</html>