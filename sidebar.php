<div class="sidebar">
    <div class="sidebar-header">SCHOOL PORTAL</div>
    <a href="dashboard.php" id="nav-dash">🏠 Dashboard</a>
    <a href="manage_teachers.php" id="nav-teachers">👨‍🏫 Manage Teachers</a>
    <a href="manage_staff.php" id="nav-staff">🛠️ Manage Staff</a>
    <a href="manage_students.php" id="nav-students">🎓 Manage Students</a>
   
    <a href="attendance.php" id="nav-attend">📅 Mark Attendance</a>
<a href="attendance_report.php" id="nav-report">📊 Attendance Report</a>
    <a href="fee_management.php" id="nav-fee">💰 Fee Management</a>
    <a href="setting.php" id="nav-profile">⚙️ Profile Settings</a>
    <a href="logout.php">🚪 Logout</a>

</div>

<style>
    :root { --primary: #2c3e50; --secondary: #34495e; --accent: #3498db; }
    .sidebar { width: 260px; background: var(--primary); color: white; height: 100vh; position: fixed; transition: 0.3s; z-index: 1000; }
    .sidebar-header { padding: 20px; background: #1a252f; text-align: center; font-weight: bold; font-size: 20px; letter-spacing: 1px; }
    .sidebar a { 
        padding: 15px 25px; color: #bdc3c7; text-decoration: none; display: block; 
        border-bottom: 1px solid #34495e; transition: 0.3s; font-size: 15px;
    }
    .sidebar a:hover { background: var(--secondary); color: white; padding-left: 35px; }
    .active-link { background: var(--accent) !important; color: white !important; border-left: 5px solid white; }
</style>