<?php
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "finance_db";  

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch finance data for the pie chart
$query = "SELECT SUM(income) AS total_income, SUM(expense) AS total_expense, SUM(savings) AS total_savings FROM finance";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $income = $row['total_income'] ?? 0;
    $expense = $row['total_expense'] ?? 0;
    $savings = $row['total_savings'] ?? 0;
} else {
    $income = 0;
    $expense = 0;
    $savings = 0;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pie Chart - Financial Records</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <link rel="stylesheet" href="signin.css.css">

    <style>
        .center-block {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #B3D8A8;
            color: white;
        }
        .container {
            text-align: center;
            margin-top: 20px;
        }
        .footer-link {
            background-color: #00c27a;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer-link:hover {
            background-color: #009b65;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>View Your Funds</h1>
    <a href="homepage.php" class="footer-link">Home</a>
</div>

<div class="container">
    <center>
        <div id="container" style="width:80%; height:400px;"></div>
    </center>
</div>

<script>
    Highcharts.chart('container', {
        chart: { type: 'pie' },
        title: { text: "Financial Records" },
        tooltip: { pointFormat: '{series.name}: <b>₱{point.y}</b>' },
        accessibility: { point: { valueSuffix: '%' } },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: { enabled: false },
                showInLegend: true
            }
        },
        series: [{
            name: 'Amount',
            colorByPoint: true,
            data: [
                { name: "Income", y: <?php echo $income; ?>, color: '#00c27a' },
                { name: "Expenses", y: <?php echo $expense; ?>, color: '#d9534f' },
                { name: "Savings", y: <?php echo $savings; ?>, color: '#1b1b5f' }
            ]
        }]
    });
</script>

</body>
</html>
