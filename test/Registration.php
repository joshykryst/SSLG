<?php
require 'Config.php';
if(!empty($_SESSION["id"])){
    header(Location: Indexs.php);
}
if (isset($_POST["submit"])) {
    $Username = $_POST["Username"] ?? '';
    $FirstName = $_POST["FirstName"] ?? '';
    $LastName = $_POST["LastName"] ?? '';
    $Birthday = $_POST["Birthday"] ?? '';
    $Gender = $_POST["Gender"] ?? '';
    $LRN = $_POST["LRN"] ?? '';
    $Email = $_POST["Email"] ?? '';
    $Password = $_POST["Password"] ?? '';

    $duplicate = mysqli_query($conn, "SELECT * FROM table_user WHERE Username = '$Username' OR Email = '$Email'");
    if (mysqli_num_rows($duplicate) > 0) {
        echo 
        "<script> alert('Username OR Email Has Already Been Taken'); </script>";
    } else {
        $query = "INSERT INTO table_user VALUES('', '$Username', '$FirstName', '$LastName', '$Birthday', '$Gender','$LRN', '$Email', '$Password')";
        mysqli_query($conn, $query);
        echo "<script> alert('Registration Successful') </script>";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <style>
   /* General form styling */
body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f4f4f4;
    font-family: 'Aharoni', sans-serif;
}

.frame {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    width: 400px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

h2 {
    font-size: 24px;
    font-weight: bold;
}

form {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Labels */
label {
    align-self: flex-start;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 16px;
}

/* Input fields */
input[type="text"], 
input[type="date"],
input[type="LRN"],  
input[type="email"], 
input[type="password"] {
    width: 100%;
    padding: 14px; /* Increased padding */
    margin: 8px 0; /* Increased margin for better spacing */
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    box-sizing: border-box;
}

/* Name container (First Name & Last Name) */
.name-container {
    width: 100%;
    display: flex;
    gap: 20px; /* Increased spacing between fields */
}

.name-container input {
    width: 50%; /* Equal width */
}

/* Gender container */
.gender-container {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin: 15px 0; /* More spacing above and below */
    position: relative;
    gap: 10px; /* Space between radio buttons and labels */
}

/* Fix alignment of radio buttons */
.gender-container input[type="radio"] {
    margin: 0;
    transform: scale(1.2); /* Make radio buttons slightly larger */
    position: relative;
    top: -2px; /* Adjust positioning to move slightly upward */
}

/* Add horizontal line below gender */
.gender-container::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 100%;
    height: 1px;
    background-color: #ccc;
}

/* Submit button */
button {
    width: 100%;
    padding: 14px; /* Bigger button for a modern look */
    font-size: 18px;
    background: url('icths.png') no-repeat center center/cover;
    font-weight: bold;
    border: none;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
    transition: transform 0.3s ease, background 0.3s ease;
    opacity: 75%;
}

button:hover {
    background: url('icths.png') no-repeat center center/cover;
    transform: scale(1.05);
    opacity: 75%;
}


    </style>
</head>
<body>
    <div class="frame">
        <h2>Registration</h2>
        <form action="" method="post" autocomplete="off">
            <label for="Username"></label>
            <input type="text" name="Username" id="Username" placeholder="Username" required>
            
            <div class="name-container">
                <input type="text" name="FirstName" id="FirstName" placeholder="First Name" required>
                <input type="text" name="LastName" id="LastName" placeholder="Last Name" required>
            </div>
            
            <label for="Birthday">Date of Birth:</label>
            <input type="date" name="Birthday" id="Birthday" required>
            
            <div class="gender-container">
                <label>Gender:</label>
                <input type="radio" name="Gender" value="Male" id="Male" required>
                <label for="Male">Male</label>
                <input type="radio" name="Gender" value="Female" id="Female" required>
                <label for="Female">Female</label>
                <input type="radio" name="Gender" value="Others" id="Others" required>
                <label for="Others">Others</label>
            </div>

            <label for="LRN"></label>
            <input type="LRN" name="LRN" id="LRN" placeholder="LRN" required>

            
            
            <label for="Email"></label>
            <input type="email" name="Email" id="Email" placeholder="name@example.com" required>
            
            <label for="Password"></label>
            <input type="password" name="Password" id="Password" placeholder="Password" required>
            
            <button type="submit" name="submit">Register</button>
        </form>
        <br>
        <a href="login.php">Login</a>
    </div>
</body>
</html>