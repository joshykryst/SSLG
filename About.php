<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'Config.php';

$isLoggedIn = false;
$userData = null;

if(!empty($_SESSION["User_ID"])){
    $User_ID = $_SESSION["User_ID"];
    $result = mysqli_query($conn, "SELECT * FROM table_user 
        WHERE User_ID = '" . mysqli_real_escape_string($conn, $User_ID) . "'");
    
    if($result && mysqli_num_rows($result) > 0){
        $userData = mysqli_fetch_assoc($result);
        $isLoggedIn = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CMRICTHS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about.css">
    <style>
        .clubs-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }

        .clubs-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: left; /* Changed from center to left */
            margin-bottom: 40px;
            font-size: 2.2rem;
            color: #2d3748;
            position: relative;
            padding-left: 0; /* Added to ensure proper left alignment */
        }

        .section-title:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background-color: #4299e1;
            bottom: -10px;
            left: 0; 
            transform: none; 
        }

        .clubs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .club-frame {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .club-frame:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .club-frame img {
            width: 100%;
            height: 200px;
            object-fit: contain; 
            padding: 15px; 
            background-color: #fff; 
            border-bottom: 3px solid #4299e1;
        }

        .club-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .club-info h3 {
            font-size: 1.3rem;
            margin: 0 0 15px 0;
            color: #2d3748;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #edf2f7;
        }

        .club-info p {
            color: #4a5568;
            font-size: 0.95rem;
            line-height: 1.6;
            flex-grow: 1;
            margin: 0;
            overflow: auto;
            max-height: 150px; 
        }

        
        @media (max-width: 768px) {
            .clubs-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            
            .club-info p {
                max-height: none;
            }
        }

        .reverse-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        @media (max-width: 768px) {
            .reverse-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .reverse-grid .description-content {
                order: 2;
            }
            
            .reverse-grid .video-frame {
                order: 1;
            }
        }

        .reversed-layout {
            background-color: #f8f9fa; 
        }

        .video-frame {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .video-frame video {
            width: 100%;
            height: 100%;
            min-height: 350px;
            object-fit: cover;
            background-color: #000; 
            display: block;
        }

        .video-frame video::-webkit-media-controls {
            background-color: rgba(0, 0, 0, 0.5);
        }

        
        @media (max-width: 767px) {
            .video-frame video {
                min-height: 250px;
            }
        }

        .hover-play {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .hover-play .interactive-video {
            width: 100%;
            height: 100%;
            min-height: 350px;
            object-fit: cover;
            background-color: #000;
            display: block;
            transition: transform 0.5s ease;
        }

        .hover-play:hover .interactive-video {
            transform: scale(1.05);
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease;
        }

        .hover-play:hover .video-overlay {
            opacity: 0;
        }

        .play-instruction {
            color: white;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        @media (max-width: 767px) {
            .hover-play .interactive-video {
                min-height: 250px;
            }
        }

       
        .vm-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 0;
            margin: 40px 0;
        }

        .vm-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            height: 400px; 
            overflow: auto; 
        }

        .vm-card h2 {
            color: #2d3748;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #edf2f7;
        }

        .card-content {
            flex-grow: 1;
            overflow-y: auto; 
            padding-right: 5px; 
        }

        .card-content p {
            white-space: pre-line;
            margin: 0;
            line-height: 1.6;
        }

        .separator {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
        }

        .vertical-line {
            width: 2px;
            background-color: #e2e8f0;
            flex-grow: 1;
            margin: 15px 0;
        }

        .circle {
            background-color: #fff;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .separator-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

       
        .vm-card.vision .card-content p,
        .vm-card.mission .card-content p {
            text-align: justify;
            padding: 0 10px;
        }

      
        @media (max-width: 768px) {
            .vm-grid {
                grid-template-columns: 1fr;
            }
            
            .separator {
                flex-direction: row;
                height: 80px;
                padding: 20px 0;
            }
            
            .vertical-line {
                width: 100%;
                height: 2px;
                margin: 0 15px;
            }
            
            .vm-card {
                height: auto;
                min-height: 300px;
            }
        }

        .history-section {
            padding: 80px 0;
            background-color: #f8f9fa;
            background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url('vintage-paper.jpg');
            background-size: cover;
            background-position: center;
        }

        .history-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .history-title {
            font-size: 2.2rem;
            color: #2d3748;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .history-title:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background-color: #4299e1;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .history-content p {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: #2d3748;
            font-size: 1.1rem;
            text-align: justify;
        }

        @media (max-width: 768px) {
            .history-container {
                padding: 30px 20px;
            }
            
            .history-content p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="index.php" class="nav-brand">
                    <img src="logo.png" alt="CMRICTHS Logo" class="nav-logo">
                    <div class="brand-text">
                        <h1>CMRICTHS</h1>
                        <p>Information and Communication Technology High School</p>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="Indexs.php">Home</a>
                    <a href="about.php" class="active">About</a>
                    <a href="Gallery.php">Gallery</a>
                    <a href="News.php">News & Events</a>
                    <a href="contacts.php">Contact</a>
                </div>
            </div>
            <div class="nav-right">
                <?php if($isLoggedIn): ?>
                    <div class="nav-profile">
                        <div class="profile-dropdown">
                            <div class="profile-trigger" onclick="toggleMenu()">
                                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" 
                                     alt="Profile" 
                                     class="profile-avatar">
                                <span class="profile-name"><?php echo htmlspecialchars($userData['Username']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="Login.php" class="portal-btn">
                        <span class="icon icon-login"></span>
                        Student Portal
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <?php if($isLoggedIn): ?>
                <img src="<?php echo !empty($userData['profile_picture']) ? 'uploads/profile/' . htmlspecialchars($userData['profile_picture']) : 'profile-default.jpg'; ?>" alt="User Profile" class="sidebar-logo">
                <h3>Welcome, <?php echo htmlspecialchars($userData['Username']); ?></h3>
            <?php else: ?>
                <img src="profile-default.jpg" alt="Guest Profile" class="sidebar-logo">
                <h3>Welcome, Guest</h3>
            <?php endif; ?>
            <button class="close-btn" onclick="toggleMenu()">
                <span class="icon icon-close"></span>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <?php if($isLoggedIn): ?>
                <div class="nav-section">
                    <h4>Quick Links</h4>
                    <a href="dashboard.php"><span class="icon icon-dashboard"></span>Dashboard</a>
                   
                    <a href="grades.php"><span class="icon icon-grades"></span>Grades</a>
                    <a href="schedule.php"><span class="icon icon-calendar"></span>Schedule</a>
                </div>
                
                <div class="nav-section">
                    <h4>Settings</h4>
                
                    <a href="Logout.php" class="logout-btn">
                        <span class="icon icon-logout"></span>Logout
                    </a>
                </div>
            <?php else: ?>
                <div class="nav-section">
                    <h4>Menu</h4>
                    <a href="Login.php"><span class="icon icon-login"></span>Login</a>
                    <a href="Register.php"><span class="icon icon-register"></span>Register</a>
                    <a href="#about"><span class="icon icon-info"></span>About Us</a>
                    <a href="#contact"><span class="icon icon-contact"></span>Contact</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>

    
    <main class="main-content">
        
        <section class="hero-slider">
            <div class="slides">
                <div class="slide active">
                    <img src="1.jpg" alt="CMRICTHS Campus">
                </div>
                <div class="slide">
                    <img src="2.jpg" alt="ICT Laboratory">
                </div>
                <div class="slide">
                    <img src="3.jpg" alt="Student Life">
                </div>
            </div>
            <button class="slider-arrow prev">
                <span class="arrow-icon"></span>
            </button>
            <button class="slider-arrow next">
                <span class="arrow-icon"></span>
            </button>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </section>

        
        <section class="school-intro">
            <div class="container">
                <div class="intro-content">
                    <div class="decorative-line"></div>
                    <h2>Excellence in ICT Education</h2>
                    <p>CMRICTHS is a specialized public high school dedicated to developing future ICT professionals through innovative education, cutting-edge technology, and comprehensive learning experiences.</p>
                    <div class="decorative-line"></div>
                </div>
            </div>
        </section>

       
        <section class="video-description-section">
            <div class="container">
                <div class="content-grid">
                    <div class="video-frame">
                        <video autoplay muted loop playsinline>
                            <source src="ICT.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    <div class="description-content">
                        <h2>Leading ICT Education</h2>
                        <div class="description-text">
                            <p class="lead">Empowering students with cutting-edge technology and innovative learning.</p>
                            <p>At CMRICTHS, we provide:</p>
                            <ul>
                                <li>State-of-the-art computer laboratories</li>
                                <li>Industry-standard programming courses</li>
                                <li>Expert ICT instructors</li>
                                <li>Hands-on technical training</li>
                            </ul>
                            <p>Our modern facilities and comprehensive curriculum prepare students for success in the digital age.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

       
       

      
        <section class="core-values">
            <div class="container">
                <h2 class="section-title">Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-frame">
                        <div class="frame-header">
                            <h3>Maka - tao</h3>
                        </div>
                        <p>Pursuing excellence through compassion and service to others</p>
                    </div>
                    <div class="value-frame">
                        <div class="frame-header">
                            <h3>Maka - kalikasan</h3>
                        </div>
                        <p>Promoting environmental stewardship and sustainable practices</p>
                    </div>
                    <div class="value-frame">
                        <div class="frame-header">
                            <h3>Maka - Diyos</h3>
                        </div>
                        <p>Nurturing spiritual growth and moral values</p>
                    </div>
                    <div class="value-frame">
                        <div class="frame-header">
                            <h3>Maka - Bayan</h3>
                        </div>
                        <p>Developing patriotism and civic responsibility</p>
                    </div>
                </div>
            </div>
        </section>

        
        
        <section class="campus-highlight">
            <div class="container">
                <div class="highlight-content">
                    <div class="highlight-text">
                        <h2>Campus Life at CMRICTHS</h2>
                        <p>Experience a dynamic learning environment where technology meets creativity</p>
                    </div>
                    <div class="features-wrapper">
                        <div class="feature-box">
                            <h3>Well-Equipped Learning Spaces</h3>
                            <p>Our classrooms and computer labs provide the necessary tools and technology to support ICT-focused education.</p>
                        </div>
                        <div class="feature-box">
                            <h3>Dedicated and Experienced Teachers</h3>
                            <p>Learn from passionate educators with industry experience and a commitment to student success.
                            </p>
                        </div>
                        <div class="feature-box">
                            <h3>Practical and Industry-Relevant Curriculum</h3>
                            <p>Our programs focus on real-world applications, preparing students with essential skills for the future.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



       
        <section class="clubs-section">
    <div class="clubs-container">
        <h2 class="section-title">Join Our Clubs</h2>
        <div class="clubs-grid">
            
            <div class="club-frame">
                <img src="CK.png" alt="Cyberkada">
                <div class="club-info">
                    <h3>CYBERKADA</h3>
                    <p>TLE CLUB - This tech club focuses on video and photo editing, offering support and guidance to help members expand their technological expertise.</p>
                </div>
            </div>
            
            <div class="club-frame">
                <img src="ACT.png" alt="ICTACT">
                <div class="club-info">
                    <h3>ICTACT</h3>
                    <p>MEDIA COVERAGE CLUB - They document and make memories through multimedia. They're the ones who document every event happening in our school.</p>
                </div>
            </div>
            
            <div class="club-frame">
                <img src="MATH.png" alt="Wizmath">
                <div class="club-info">
                    <h3>WIZMATH</h3>
                    <p>MATH CLUB - This club uses a variety of activities and interactions to assist, inspire, and encourage kids to recognize the magic of mathematics.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="YES.png" alt="YES-O">
                <div class="club-info">
                    <h3>YES-O</h3>
                    <p>SCIENCE/ENVIRONMENTAL CLUB - The only recognized co-curricular environment club in the school that consolidates all environmental and ecology organizations with projects for the environment.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="PAGE.png" alt="Page Turners">
                <div class="club-info">
                    <h3>PAGE TURNERS</h3>
                    <p>LIBRARY CLUB - Dreamers and game-changers who inspire us to turn pages, spark discussions, and connect through the power of reading and storytelling.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="EUP.png" alt="Euphemist">
                <div class="club-info">
                    <h3>EUPHEMIST</h3>
                    <p>ENGLISH CLUB - Cultivates masterful communication skills through engaging discussions, insightful writing exercises, and critical analysis to empower members to articulate thoughts with precision.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="KAA.png" alt="Kaadman">
                <div class="club-info">
                    <h3>KAADMAN</h3>
                    <p>AP CLUB - Fostering a deeper connection and appreciation for historical figures while enhancing critical thinking, responsibility, environmental awareness, and nationalism.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="KAS.png" alt="Kasafi">
                <div class="club-info">
                    <h3>KASAFI</h3>
                    <p>FILIPINO CLUB - Celebrates Filipino culture through language and shared experience, exploring its nuances and using it as a vehicle for self-expression and cultural understanding.</p>
                </div>
            </div>
            <div class="club-frame">
                <img src="MAPEH.png" alt="mapeh">
                <div class="club-info">
                    <h3>MAPEH</h3>
                    <p>MAPEH CLUB - nurtures well-rounded individuals by fostering creativity, physical
prowess, and well-being. Through music, arts, physical education, and health
activities—including dance and singing—members explore their talents, build
confidence, and develop a healthy lifestyle.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="opkod.png" alt="opkod">
                <div class="club-info">
                    <h3>OPKOD</h3>
                    <p>- This club advocates against the misuse and dangerousness of drugs, but not only do
we advocate drugs; They also advocate hygiene.
</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="mdm.png" alt="MDM">
                <div class="club-info">
                    <h3>MAESTRO DEL MUSICO</h3>
                    <p> is your chance to take the stage, with lights illuminating your
unique voice and passion, while the audience eagerly anticipates your perfomance.
MDM is the best space where you can express yourself, inspire others, and create
unforgettable memories.
</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="ESP.png" alt="ESP">
                <div class="club-info">
                    <h3>ESP</h3>
                    <p>ESP CLUB - This club will make you understand your moral values about your life. They also want
you to showcase your talent. Esp Club will guide you through the rightness and the right
way to do.
</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="harbinger.png" alt="HARBINGER">
                <div class="club-info">
                    <h3>HARBINGER</h3>
                    <p>Harbinger Club - is our school's primary news source, keeping us informed on all
campus happenings. They function as the school newspaper.
</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="honsoc.png" alt="HONSOC">
                <div class="club-info">
                    <h3>HONOR SOCIETY</h3>
                    <p>Honor Society -  organizes and maintains events centered around specific themes
or topics. They are responsible for the planning, execution, and ongoing upkeep of
these events, ensuring their success and relevance to the school community. Their
work reflects their commitment to academic excellence and community engagement.</p>
                </div>
            </div>

            <div class="club-frame">
                <img src="alab.jpg" alt="ALAB">
                <div class="club-info">
                    <h3>ALAB</h3>
                    <p>- "Alliance of Liberated Artistic Bodies Dance Troupe" — where passion meets artistry
and every move tells a story. They deliver nothing but the best in dance, creativity, and
unforgettable performances.</p>
                </div>
            </div>




        </div>
    </div>
</section>

        <header class="hero about-hero">
            <div class="hero-content">
                <h1>About CMRICTHS</h1>
                <p>"Let Technology Lead You."</p>
            </div>  
        </header>

        
        <section class="video-description-section reversed-layout">
    <div class="container">
        <div class="content-grid reverse-grid">
            <div class="description-content">
                <h2>Sandigan</h2>
                <div class="description-text">
                    <p class="lead">Ang Sandigan ng CMRICTHS ay ang pinakamahalagang kasangkapang bumubuo sa tahanang ito. Ito ang nagsisilbing ilaw upang makita ng mga mag-aaral ang kahalagahan ng responsibilidad, kabutihan, at may kaalaman.</p>
                </div>
            </div>
            <div class="video-frame hover-play">
                <video class="interactive-video" loop playsinline poster="sandigan-poster.jpg">
                    <source src="Sandigan.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="video-overlay">
                    <span class="play-instruction">Hover to play with sound</span>
                </div>
            </div>
        </div>
    </div>
</section>

        <section class="video-description-section">
    <div class="container">
        <div class="content-grid">
            <div class="video-frame hover-play">
                <video class="interactive-video" loop playsinline poster="facilities-poster.jpg">
                    <source src="hymn.mp4" type="video/mp4">
                    <p>Your browser does not support HTML5 video.</p>
                </video>
                <div class="video-overlay">
                    <span class="play-instruction">Hover to play with sound</span>
                </div>
            </div>
            <div class="description-content">
                <h2>CMRICTHS HYMN</h2>
                <div class="description-text">
                    <p class="lead">It was this music that upheld the harmony  and bond of the ones who call CMRICTHS their "home".  Not just that, it gives light to the true purpose and objectives of the ICTians.  It exhibits their determination, resilience, and enthusiasm  to carve our world into a better place with the help of technology. 
                    We are the future and with that, we commit to showing them hope  and proving our potential.  Let us all rise and pay respect to our beloved school as we honor and proudly sing its hymn. </p>
                    
                </div>
            </div>
        </div>
    </div>
</section>
       
        <section class="vision-mission">
    <div class="container">
        <div class="vm-grid">
            <div class="vm-card vision">
                <h2>DEPED Vision</h2>
                <div class="card-content">
                    <p>We dream of Filipinos
who passionately love their country
and whose values and competencies
enable them to realize their full potential
and contribute meaningfully to building the nation.

As a learner-centered public institution,
the Department of Education
continuously improves itself
to better serve its stakeholders.</p>
                </div>
            </div>
            
            <div class="separator">
                <div class="vertical-line"></div>
                <div class="circle">
                    <img src="logo.png" alt="CMRICTHS Logo" class="separator-logo">
                </div>
                <div class="vertical-line"></div>
            </div>

            <div class="vm-card mission">
                <h2>DEPED Mission</h2>
                <div class="card-content">
                    <p>To protect and promote the right of every Filipino to quality, equitable, culture-based, and complete basic education where:

Students learn in a child-friendly, gender-sensitive, safe, and motivating environment.

Teachers facilitate learning and constantly nurture every learner.

Administrators and staff, as stewards of the institution, ensure an enabling and supportive environment for effective learning to happen.

Family, community, and other stakeholders are actively engaged and share responsibility for developing life-long learners.</p>
                </div>
            </div>
        </div>
    </div>
</section>

        
        <section class="history-section">
    <div class="container">
        <div class="history-container">
            <h2 class="history-title">Our History</h2>
            <div class="history-content">
                <p>"CMRICTHS is a school that focuses in ICT. When the school started it’s not that big, it has a one building that serves as the classrooms and faculty rooms. As time goes by the school expanded a bit and added a few more floors with more classrooms, giving more rooms for new students. The school also decides to make a covered court at the middle, they fixed the old gate, replacing them with new ones. After the pandemic the school added a few more gates so it won’t be crowded whenever students are going home. The school has a lot of events for specific months like February for Valentine's Day, October for Oktoberfest and etc. With the help of the club members, SSLG, and the teachers make these events enjoyable and fun for the students."</p>
            </div>
        </div>
    </div>
</section>

       
        <section class="achievements">
            <div class="container">
                <h2 class="section-title">Our Achievements</h2>
                <div class="achievements-grid">
                    <div class="achievement-card">
                        <i class="fas fa-trophy"></i>
                        <h3>Highly Competitive in competitions</h3>
                        <p>Consistently in competes with multiple awards
                        </p>
                    </div>
                    <div class="achievement-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>High passing rate</h3>
                        <p> consistently high academic performance and standard</p>
                    </div>
                    <div class="achievement-card">
                        <i class="fas fa-handshake"></i>
                        <h3>Best in TLE special subject</h3>
                        <p>Have a new coding speacial subjects</p>
                    </div>
                </div>
            </div>
        </section>

        
       
    </main>

    <footer class="footer">
        <div class="footer-top">
            <div class="container footer-grid">
                <div class="footer-section">
                    <img src="logo.png" alt="CMRICTHS Logo" class="footer-logo">
                    <h3>CMRICTHS</h3>
                    <p>Empowering students through quality ICT education and innovation.</p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/cmricthsunleashed"><img src="fb.png" alt="Facebook" class="social-icon"></a>
                        <a href="https://www.instagram.com/cmricths.sslg/"><img src="ig.png" alt="Instagram" class="social-icon"></a>
                        <a href="https://www.tiktok.com/@cmricths.sslg"><img src="tt.png" alt="TikTok" class="social-icon"></a>
                        <a href="https://www.facebook.com/CMRICTHSSSLG"><img src="fb.png" alt="Facebook Group" class="social-icon"></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="indexs.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="News.php">News & Events</a></li>
                        <li><a href="contacts.php">Contacts</a></li>
                    </ul>
                </div>

                

                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <ul class="contact-info">
                        <li><span class="icon icon-location"></span>Dona Aurora St, Claro M. Recto, Angeles City, Pampanga</li>
                        <li><span class="icon icon-phone"></span>(044) 123-4567</li>
                        <li><span class="icon icon-envelope"></span>info@cmricths.edu.ph</li>
                        <li><span class="icon icon-clock"></span>Monday - Friday: 7:00 AM - 5:00 PM</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> CMRICTHS. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#privacy">Privacy Policy</a>
                    <a href="#terms">Terms of Use</a>
                    <a href="#sitemap">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
    
    document.addEventListener('DOMContentLoaded', function() {
        const interactiveVideos = document.querySelectorAll('.interactive-video');
        
        interactiveVideos.forEach(video => {
            const videoContainer = video.parentElement;
            
           
            video.preload = "metadata";
            
          
            videoContainer.addEventListener('mouseenter', function() {
               
                if (video.currentTime === video.duration) {
                    video.currentTime = 0;
                }
                
                
                video.muted = false;
                
              
                const playPromise = video.play();
                
               
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        
                        const overlay = videoContainer.querySelector('.video-overlay');
                        if (overlay) {
                            const playBtn = document.createElement('button');
                            playBtn.innerHTML = '▶ Play with sound';
                            playBtn.classList.add('forced-play-btn');
                            playBtn.style.cssText = 'background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;';
                            
                          
                            overlay.innerHTML = '';
                            overlay.appendChild(playBtn);
                            overlay.style.opacity = '1';
                            
                           
                            playBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                video.muted = false;
                                video.play();
                                overlay.style.opacity = '0';
                            });
                        }
                    });
                }
            });
            
            videoContainer.addEventListener('mouseleave', function() {
                video.pause();
            });
            
       
            video.pause();
        });
    });
</script>
</body>
</html>