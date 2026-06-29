<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "registerlog";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM table_user WHERE User_ID=$id");
    header("Location: admin.php");
    exit();
}

// Handle Edit Request
if (isset($_POST['update'])) {
    $id = (int) $_POST['User_ID'];
    $username = $_POST['Username'];
    $firstname = $_POST['FirstName'];
    $lastname = $_POST['LastName'];
    $birthday = $_POST['Birthday'];
    $gender = $_POST['Gender'];
    $lrn = $_POST['LRN'];
    $email = $_POST['Email'];
    $password = $_POST['Password'];
    
    $conn->query("UPDATE table_user SET Username='$username', FirstName='$firstname', LastName='$lastname', Birthday='$birthday', Gender='$gender', LRN='$lrn', Email='$email', Password='$password' WHERE User_ID=$id");
    header("Location: admin.php");
    exit();
}

// Fetch Records
$result = $conn->query("SELECT * FROM table_user");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="design.css">
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Birthday</th>
                    <th>Gender</th>
                    <th>LRN</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['User_ID'] ?></td>
                        <td><?= $row['Username'] ?></td>
                        <td><?= $row['FirstName'] ?></td>
                        <td><?= $row['LastName'] ?></td>
                        <td><?= $row['Birthday'] ?></td>
                        <td><?= $row['Gender'] ?></td>
                        <td><?= $row['LRN'] ?></td>
                        <td><?= $row['Email'] ?></td>
                        <td><?= $row['Password'] ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditForm(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                            <a href="?delete=<?= $row['User_ID'] ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Form Modal -->
    <div id="editForm" class="edit-container" style="display: none;">
        <span class="close-btn" onclick="closeEditForm()">&times;</span>
        <h3>Edit User</h3>
        <form method="POST" action="">
            <input type="hidden" id="User_ID" name="User_ID">
            <div class="input-group">
                <label>Username</label>
                <input type="text" id="Username" name="Username" required>
            </div>
            <div class="input-group">
                <label>First Name</label>
                <input type="text" id="FirstName" name="FirstName" required>
            </div>
            <div class="input-group">
                <label>Last Name</label>
                <input type="text" id="LastName" name="LastName" required>
            </div>
            <div class="input-group">
                <label>Birthday</label>
                <input type="date" id="Birthday" name="Birthday" required>
            </div>
            <div class="input-group">
                <label>Gender</label>
                <input type="text" id="Gender" name="Gender" required>
            </div>
            <div class="input-group">
                <label>LRN</label>
                <input type="text" id="LRN" name="LRN" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" id="Email" name="Email" required>
            </div>
            <div class="input-group" style="position: relative;">
                <label>Password</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="Password" name="Password" required style="width: 100%; padding-right: 40px;">
                    <button type="button" class="toggle-password" onclick="togglePassword()" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer;">👁</button>
                </div>
            </div>
            <button type="submit" name="update" class="update-btn">Update</button>
        </form>
    </div>

    <script>
        function openEditForm(user) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('User_ID').value = user.User_ID;
            document.getElementById('Username').value = user.Username;
            document.getElementById('FirstName').value = user.FirstName;
            document.getElementById('LastName').value = user.LastName;
            document.getElementById('Birthday').value = user.Birthday;
            document.getElementById('Gender').value = user.Gender;
            document.getElementById('LRN').value = user.LRN;
            document.getElementById('Email').value = user.Email;
            document.getElementById('Password').value = user.Password;
        }
        function closeEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
        function togglePassword() {
            var passwordInput = document.getElementById('Password');
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
