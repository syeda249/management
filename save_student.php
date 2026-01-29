<?php
include 'db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$class = $_POST['class'];

$sql = "INSERT INTO students (name, email, class) VALUES ('$name', '$email', '$class')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Student added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>