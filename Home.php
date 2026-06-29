<html>
<head>
    <title>Home</title>
    <style>
body {
 margin: 0;
padding: 0;
height: 200vh;
font-family: 'Arial Black', sans-serif;
overflow-x: hidden;
background: white;
        }

.background {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100vh;
background: url('School.jpg') no-repeat center center/cover;
opacity: 0.75;
z-index: -1;
        }

.overlay {
position: absolute;
top: 32%;
left: 15%;
width: 350px;
height: 350px;
z-index: 10;
        }

.overlay img {
width: 100%;
height: 100%;
object-fit: contain;
        }

.content {
position: absolute;
top: 32%;
left: 34%;
 text-align: left;
color: white;
font-size: 24px;
 font-weight: bold;
text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }

.content h1 {
font-size: 148px;
margin: 0;
        }

.content p {
margin: 5px 0;
font-size: 28px;
        }

.border-section {
 position: relative;
 top: 100vh;
 width: 100%;
 height: auto;
 background: white;
 padding: 80px 50px;
 text-align: center;
        }

.courses-container {
 display: flex;
justify-content: center;
gap: 40px;
        }

.course-box {
width: 350px;
 padding: 30px;
 background: #ffecec;
 border-radius: 15px;
box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
 text-align: center;
        }

.course-box h3 {
margin: 0;
 font-size: 26px;
 font-weight: bold;
        }

.course-box p {
 font-size: 18px;
color: #555;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="overlay">
        <img src="ICT.png" alt="ICT Logo">
    </div>
    <div class="content">
        <h1>CMRICTHS</h1>
        <p>CLARO M. RECTO INFORMATION</p>
        <p>COMMUNICATION TECHNOLOGY HIGH SCHOOL</p>
    </div>
        
    <div class="border-section">
        <h2>Courses We Offer</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <br>
    <br>
    <br>
    
<div class="courses-container">
    <div class="course-box">
    <h3>Intermediate</h3>
     <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque aliquet turpis nulla.</p>
        </div>
    <div class="course-box">
        <h3>Degree</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque aliquet turpis nulla.</p>
    </div>
    <div class="course-box">
        <h3>Post Graduation</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque aliquet turpis nulla.</p>
    </div>
    </div>
    </div>
</body>
</html>