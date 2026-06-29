function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const menuBtn = document.querySelector('.menu-btn');
    
    sidebar.classList.toggle('active');
    mainContent.classList.toggle('sidebar-active');
    menuBtn.classList.toggle('active');
}

// Close sidebar with escape key
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        const sidebar = document.getElementById("sidebar");
        if (sidebar.classList.contains("active")) {
            toggleMenu();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Get all slider elements
    const slider = document.querySelector('.hero-slider');
    if (!slider) return; // Exit if no slider found
    
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    const prevBtn = slider.querySelector('.prev');
    const nextBtn = slider.querySelector('.next');
    
    let currentIndex = 0;
    let slideInterval;

    // Show slide function
    function showSlide(index) {
        // Handle bounds
        if (index >= slides.length) index = 0;
        if (index < 0) index = slides.length - 1;
        
        currentIndex = index;
        
        // Hide all slides
        slides.forEach(slide => {
            slide.style.opacity = '0';
            slide.classList.remove('active');
        });
        
        // Hide all dots
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Show current slide
        slides[currentIndex].style.opacity = '1';
        slides[currentIndex].classList.add('active');
        dots[currentIndex].classList.add('active');
    }

    // Set up click events for arrows
    prevBtn.addEventListener('click', () => {
        showSlide(currentIndex - 1);
        resetTimer();
    });

    nextBtn.addEventListener('click', () => {
        showSlide(currentIndex + 1);
        resetTimer();
    });

    // Set up click events for dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetTimer();
        });
    });

    // Auto advance slides
    function startTimer() {
        slideInterval = setInterval(() => {
            showSlide(currentIndex + 1);
        }, 5000);
    }

    function resetTimer() {
        clearInterval(slideInterval);
        startTimer();
    }

    // Initialize slider
    showSlide(0);
    startTimer();
});