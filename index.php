<!DOCTYPE html>
<html lang="en">
<head>
    <title>School Management System</title>
    <style>
        body { font-family: Arial; margin: 50px; background-color: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; max-width: 400px; }
        input { width: 90%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Student</h2>
    <form id="studentForm">
        <input type="text" id="name" placeholder="Student Name" required>
        <input type="email" id="email" placeholder="Email Address" required>
        <input type="text" id="class" placeholder="Class" required>
        <button type="submit">Register Student</button>
    </form>
    <div id="response"></div>
</div>

<script>
document.getElementById('studentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let formData = new FormData();
    formData.append('name', document.getElementById('name').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('class', document.getElementById('class').value);

    fetch('save_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let resDiv = document.getElementById('response');
        if(data.status === "success") {
            resDiv.innerHTML = "<p style='color:green'>" + data.message + "</p>";
            document.getElementById('studentForm').reset();
        } else {
            resDiv.innerHTML = "<p style='color:red'>Error occurred!</p>";
        }
    });
});
</script>

</body>
</html>