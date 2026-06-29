<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}
	$uri .= $_SERVER['HTTP_HOST'];
	header('Location: '.$uri.'/dashboard/');
	exit;
?>
Something is wrong with the XAMPP installation :-(

<nav class="top-nav">
    <div class="nav-brand">
        <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
        <h1>CMRICTHS</h1>
    </div>
    <div class="nav-links">
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#programs">Programs</a>
        <a href="#contact">Contact</a>
    </div>
    <div class="menu-btn" onclick="toggleMenu()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>
</nav>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="profile.jpg" alt="User Profile" class="sidebar-logo">
        <h3>Welcome, <?php echo $row["username"]; ?></h3>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-th-large"></i>Dashboard</a>
        <a href="profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
        <a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a>
        <a href="messages.php"><i class="fas fa-envelope"></i>Messages</a>
        <a href="Logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>Logout
        </a>
    </nav>
</div>
