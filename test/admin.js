document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".edit-btn");
    const editContainer = document.querySelector(".edit-container");
    const closeButton = document.querySelector(".close-btn");

    // Initially hide the edit form with opacity
    editContainer.style.display = "none";
    editContainer.style.opacity = "0";
    editContainer.style.transition = "opacity 0.3s ease-in-out, transform 0.3s ease-in-out";

    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            editContainer.style.display = "block";  // Show form
            setTimeout(() => {
                editContainer.style.opacity = "1";
                editContainer.style.transform = "translate(-50%, -50%) scale(1)";
            }, 10);
        });
    });

    closeButton.addEventListener("click", function () {
        editContainer.style.opacity = "0";
        editContainer.style.transform = "translate(-50%, -50%) scale(0.9)";
        setTimeout(() => {
            editContainer.style.display = "none";  // Hide form after animation
        }, 300);
    });
});

function showEditForm(userId, event) {
    event.preventDefault(); // Prevent the page from reloading

    document.getElementById('edit-container').style.display = 'block';

    // Load user data into the form via AJAX (optional)
}
