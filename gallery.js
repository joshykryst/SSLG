// Gallery functionality

// Initialize gallery when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Handle file input display
    const fileInput = document.getElementById('image-upload');
    const fileName = document.getElementById('file-name');
    
    if(fileInput && fileName) {
        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                fileName.textContent = this.files[0].name;
            } else {
                fileName.textContent = 'No file chosen';
            }
        });
    }
    
    // Set up image click handlers
    setupGalleryImageClickHandlers();

    // Get current page URL
    const currentLocation = window.location.href;
    
    // Get all navigation links
    const navLinks = document.querySelectorAll('.nav-links a');
    
    // Check each link to see if it matches current page
    navLinks.forEach(link => {
        // If the link href is in the current location, mark it active
        if (currentLocation.includes(link.getAttribute('href')) && link.getAttribute('href') !== '#') {
            link.classList.add('active');
        }
    });
    
    // Add hover effects
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = 'rgba(52, 152, 219, 0.1)';
            }
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = 'transparent';
            }
        });
    });
});

// Set up click handlers for gallery images
function setupGalleryImageClickHandlers() {
    const galleryImages = document.querySelectorAll('.gallery-image');
    
    galleryImages.forEach(image => {
        image.addEventListener('click', function() {
            openFrame(this);
        });
        
        // Also handle click on the view button
        const viewButton = image.parentElement.querySelector('.view-button');
        if (viewButton) {
            viewButton.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent bubbling to parent
                openFrame(image);
            });
        }
    });
}

// Open the image frame/modal
function openFrame(image) {
    const frame = document.getElementById('imageFrame');
    const frameImage = document.getElementById('frameImage');
    const frameDescription = document.getElementById('frameDescription');
    const imageId = document.getElementById('imageId');
    const editDescription = document.getElementById('editDescription');
    
    // Set image source and description in the frame
    frameImage.src = 'uploads/' + image.dataset.image;
    frameDescription.textContent = image.dataset.description || 'No description available';
    
    // Set image ID for edit/delete operations
    imageId.value = image.dataset.id;
    
    // Set description in edit form
    editDescription.value = image.dataset.description || '';
    
    // Show the frame
    frame.classList.add('active');
    
    // Add body class to prevent scrolling
    document.body.classList.add('no-scroll');
}

// Close the image frame/modal
function closeFrame() {
    const frame = document.getElementById('imageFrame');
    frame.classList.remove('active');
    
    // Hide edit section when closing
    hideEdit();
    
    // Remove body class to allow scrolling again
    document.body.classList.remove('no-scroll');
}

// Show edit form
function showEdit() {
    const editSection = document.getElementById('editSection');
    editSection.classList.add('active');
}

// Hide edit form
function hideEdit() {
    const editSection = document.getElementById('editSection');
    editSection.classList.remove('active');
}

// Handle edit form submission
function handleEditSubmit(event) {
    // Form will be submitted normally
    return true;
}

// Delete image
function deleteImage() {
    const imageId = document.getElementById('imageId').value;
    
    if (!confirm('Are you sure you want to delete this image? This cannot be undone.')) {
        return;
    }
    
    // Send AJAX request to delete
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'gallerydelete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch(e) {
                alert('Error processing server response');
                return;
            }
            
            if (response.success) {
                // Close the frame
                closeFrame();
                
                // Remove the image from the gallery
                const imageElement = document.querySelector(`[data-id="${imageId}"]`);
                if (imageElement) {
                    const container = imageElement.closest('.image-container');
                    if (container) {
                        container.remove();
                    }
                }
                
                alert('Image deleted successfully');
            } else {
                alert(response.message || 'Error deleting image');
            }
        }
    };
    xhr.send('id=' + encodeURIComponent(imageId));
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const frame = document.getElementById('imageFrame');
    if (event.target === frame) {
        closeFrame();
    }
});

// Close modal with escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeFrame();
    }
});

// Toggle sidebar menu
function toggleMenu() {
    document.getElementById('sidebar').classList.toggle('active');
}

// Add drag and drop functionality if browser supports it
if ('draggable' in document.createElement('div')) {
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.querySelector('.file-input-container');
        
        if (dropZone) {
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Highlight drop zone when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropZone.classList.add('highlight');
            }
            
            function unhighlight() {
                dropZone.classList.remove('highlight');
            }
            
            // Handle dropped files
            dropZone.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const fileInput = document.getElementById('image-upload');
                const fileName = document.getElementById('file-name');
                const files = e.dataTransfer.files;
                
                if (fileInput && files.length > 0) {
                    fileInput.files = files;
                    fileName.textContent = files[0].name;
                }
            }
        }
    });
}

function showFrame(imgElement) {
    const frame = document.getElementById('imageFrame');
    const frameImage = document.getElementById('frameImage');
    const frameDesc = document.getElementById('frameDescription');
    const imageId = document.getElementById('imageId');
    const editButton = document.querySelector('.edit-button');
    const deleteButton = document.querySelector('.delete-button');
    
    // Get data from clicked image
    const imageSrc = 'uploads/' + imgElement.dataset.image;
    const description = imgElement.dataset.description;
    const id = imgElement.dataset.id;
    const userId = imgElement.dataset.userId;
    
    // Get current user ID from PHP session
    // This assumes you have a global variable with the current user ID
    const currentUserId = <?php echo isset($_SESSION['User_ID']) ? $_SESSION['User_ID'] : 'null'; ?>;
    
    // Set image and description
    frameImage.src = imageSrc;
    frameDescription.textContent = description || 'No description';
    imageId.value = id;
    
    // Show/hide edit and delete buttons based on ownership
    if (currentUserId && userId == currentUserId) {
        editButton.style.display = 'inline-block';
        deleteButton.style.display = 'inline-block';
    } else {
        editButton.style.display = 'none';
        deleteButton.style.display = 'none';
    }
    
    // Show frame
    frame.style.display = 'flex';
}