<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title>
    <link rel="stylesheet" href="about.css">
    <style>
        .video-section video {
            transition: transform 0.3s ease-in-out;
        }
        .video-section video:hover {
            transform: scale(1.05);
        }
        
        /* Divider Shape */
        .divider-shape {
            width: 100%;
            height: 120px;
            background: #800000; /* Dark Red, matching HAU color */
            clip-path: polygon(0% 0%, 100% 0%, 100% 75%, 50% 100%, 0% 75%);
            text-align: center;
            font-size: 1.8em;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="header">
    About
</div>
<div class="logo-container">
    <div class="text-container">
        <span class="logo-text">CMRICTHS</span><br>  
        <span class="sub-text">CLARO M. RECTO INFORMATION COMMUNICATION TECHNOLOGY HIGH SCHOOL</span>
    </div>
    <img src="logo.png" alt="School Logo">
</div>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br>
<div class="video-section" style="margin-top: 100px; text-align: center;">
    <video id="ictVideo" width="80%" autoplay muted>
        <source src="ICT.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</div>

<br><br><br><br>

<!-- Divider Shape -->
<div class="divider-shape">Welcome to CMRICTHS</div> <!-- Adjusted to match the design -->

<br><br><br><br>

<div class="small-videos">
    <video class="small-video" src="sandigan.mp4" controls></video>
    <video class="small-video" src="hymn.mp4" controls></video>
</div>

<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
<br>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Main Video
        const mainVideo = document.getElementById("ictVideo");
        
        mainVideo.removeAttribute("controls"); // Remove controls
        mainVideo.muted = false; // Ensure sound plays
        
        mainVideo.addEventListener("mouseenter", function () {
            mainVideo.play();
        });

        mainVideo.addEventListener("mouseleave", function () {
            mainVideo.pause();
        });
        
        mainVideo.addEventListener("click", function () {
            window.location.href = "https://www.youtube.com/watch?v=pTR6RyTLHtA";
        });

        // Small Videos
        const smallVideos = document.querySelectorAll(".small-video");

        smallVideos.forEach((video, index) => {
            video.removeAttribute("controls"); // Remove controls
            
            video.addEventListener("mouseenter", function () {
                video.play();
            });

            video.addEventListener("mouseleave", function () {
                video.pause();
            });

            video.addEventListener("click", function () {
                const videoLinks = [
                    "https://www.youtube.com/watch?v=svgYXgY9pso", // Replace with actual video links
                    "https://www.youtube.com/watch?v=I5KiT03MCyo"
                ];
                window.location.href = videoLinks[index];
            });
        });
    });
</script>

</body>
</html>  
