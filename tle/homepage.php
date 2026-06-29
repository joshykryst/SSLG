<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funds Manager</title>
    <link rel="stylesheet" href="styles.css">

    <style>
      <style>
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            background-color: #FBFFE4;
        }


        .hero {
            background-color: #B3D8A8;
            color: white;
            padding: 50px 20px;
            text-align: center;
        }

        .hero h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .buttons {
            margin-top: 20px;
        }

        .buttons button {
            padding: 12px 24px;
            margin: 10px;
            border: none;
            cursor: pointer;
            background-color: #3D8D7A;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .buttons button:hover {
            background-color: #009b65;
        }

        .content {
            text-align: center;
            padding: 40px 20px;
            background: white;
        }

        .content h2 {
            font-size: 26px;
            color: #1b1b5f;
            margin-bottom: 10px;
        }

        .content p {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }

        .content button {
            padding: 12px 24px;
            border: none;
            cursor: pointer;
            background-color: #00c27a;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .content button:hover {
            background-color: #009b65;
        }

        .footer {
            background-color: #1b1b5f;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            margin-top: 190px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>

<body>
   <div class="topnav">
 <h1 class="fund">Fund Manager</h1>
   <div class="rawr">
	<a href="About Us.php">About Us</a>
	<a href="view.php">View</a>
	<a href="track.php">Track</a>
   </div>
 </div>

         
    <div class="hero">
        <h2>A finance tracker that will change your life</h2>
        <p>Save, and track your funds at the same time.</p>
        <div class="buttons">
            
            <button onclick="window.location.href='view.php'">View Funds</button>
	    <button onclick="window.location.href='track.php'">Track Funds</button>
            <button onclick="window.location.href='maincode.php'">Log Out</button>
         	
        </div>
     </body>
</html>
