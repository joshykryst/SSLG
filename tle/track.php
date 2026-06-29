<?php
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "finance_db";  

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $income = $_POST['income'];
    $expense = $_POST['expense'];
    $savings = $income - $expense; // 

    $sql = "INSERT INTO finance (income, expense, savings) VALUES ('$income', '$expense', '$savings')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Data Saved Successfully!');</script>";
        header("Location: track.php"); // Redirect to clear POST data
        exit();
    } else {	
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}



$funds_data = mysqli_query($conn, "SELECT * FROM finance ORDER BY id DESC");


$total_savings_query = mysqli_query($conn, "SELECT SUM(savings) AS total_savings FROM finance");
$total_savings_row = mysqli_fetch_assoc($total_savings_query);
$total_savings = $total_savings_row['total_savings'] ?? 0;

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Funds</title>
    <link rel="stylesheet" href="styles.css">
	<script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        .tab {
            display: flex;
            justify-content: center;
            background-color: #B3D8A8;
            padding: 10px;
        }

        .tab button {
            background-color: #3D8D7A ;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .tab button:hover, .tab button.active {
            background-color: #3D8D7A;
        }

        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #ffffff; 
            color: black;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #00c27a; 
            color: white;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form input {
            padding: 10px;
            margin: 10px 0;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form input[type="submit"] {
            background-color: #00c27a;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
        }

        form input[type="submit"]:hover {
            background-color: #009b65;
        }

        .home-btn {
            background-color: #00c27a;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .home-btn:hover {
            background-color: #009b65;
        }

        .container {
            background: white;
            padding: 20px;
            margin: 20px auto; 
            width: 50%;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container h2 {
            font-size: 22px;
            color: #1b1b5f;
            margin-bottom: 10px;
        }

        .container p {
            font-size: 26px;
            font-weight: bold;
            color: #00c27a;
        }
.edit-btn, .delete-btn {
    padding: 5px 10px;
    margin: 2px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
}

.edit-btn {
    background-color: #f0ad4e; 
}

.delete-btn {
    background-color: #d9534f; 
}

.delete-btn:hover {
    background-color: #c9302c;
}

.edit-btn:hover {
    background-color: #ec971f;
}

    </style>
</head>
<body>

    <div class="header">
        <h1>Track Your Funds</h1>
        <a href="homepage.php" class="home-btn">Home</a>
    </div>

    <div class="tab">
        <button class="tab-link active" onclick="openTab(event, 'formTab')">Track Funds</button>
    </div>

    <div id="formTab" class="tab-content active">
        <h2>Finance Input Form</h2>
        <form action="track.php" method="POST">
            <input type="number" name="income" placeholder="Monthly Income" required><br>
            <input type="number" name="expense" placeholder="Monthly Expense" required><br>
            <input type="submit" value="Track Funds">
        </form>

      
        <div class="container">
            <h2>Total Savings</h2>
            <p>₱<?php echo number_format($total_savings, 2); ?></p>
        </div>

      <h2>Transaction History</h2>
<div class="table-container">
    <table>
        <tr>
            <th>Monthly Income</th>
            <th>Monthly Expense</th>
            <th>Savings</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($funds_data)) { ?>
            <tr>
                <td><?php echo number_format($row['income'], 2); ?></td>
                <td><?php echo number_format($row['expense'], 2); ?></td>
                <td><?php echo number_format($row['savings'], 2); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>


    
    <script>
        function openTab(event, tabId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabId).style.display = "block";
            event.currentTarget.classList.add("active");
        }
    </script>

</body>
</html>
