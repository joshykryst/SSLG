// Make toggleMenu function globally available immediately
function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}
    
// Define the same grade sections as in admin.js
const gradeSections = {
    'Grade 7': ['Babbage', 'Byron', 'Cooper', 'Eckert', 'Kilby', 'Leibniz', 'Liscov', 'Osborne', 'Pascal', 'Rossum', 'Stallman', 'Thompson', 'Wilkes'],
    'Grade 8': ['Andreessen', 'Berners', 'Brin', 'Engelbart', 'Gray', 'Hamilton', 'Mauchly', 'Turing', 'Wilson'],
    'Grade 9': ['Atanasoff', 'Hollerith', 'Hopper', 'Hull', 'Iverson', 'Johansen', 'Johnson', 'Neumann', 'Page', 'Perlis'],
    'Grade 10': ['Allen', 'Banatao', 'Bryce', 'Cray', 'Minsky', 'Shannon', 'Stibitz', 'Torvalds', 'Wozniak', 'Zuse']
};

// Function to update section options based on selected grade level
function updateSectionOptions() {
    const gradeLevel = document.getElementById('gradeLevel').value;
    const sectionDropdown = document.getElementById('section');
    
    // Clear existing options
    sectionDropdown.innerHTML = '<option value="">Select Section</option>';
    
    // Add new options based on selected grade level
    if (gradeSections[gradeLevel]) {
        gradeSections[gradeLevel].forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            sectionDropdown.appendChild(option);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const profileTrigger = document.querySelector('.profile-trigger');
    const profileForm = document.getElementById('profileUpdateForm');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const formInputs = profileForm.querySelectorAll('input:not([type="file"])');
    const formSelects = profileForm.querySelectorAll('select');
    const formActions = profileForm.querySelector('.form-actions');
    const uploadBtn = document.querySelector('.upload-btn');

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && 
                (!profileTrigger || !profileTrigger.contains(e.target))) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Toggle Edit Mode for Profile
    window.toggleEditMode = function() {
        const isEditMode = !formInputs[0].readOnly;
        
        // Toggle input states
        formInputs.forEach(input => {
            if (!input.id.includes('Display')) {
                input.readOnly = isEditMode;
            }
        });

        // Toggle select visibility
        formSelects.forEach(select => {
            const displayInput = document.getElementById(select.id + 'Display');
            if (displayInput) {
                select.style.display = isEditMode ? 'none' : 'block';
                displayInput.style.display = isEditMode ? 'block' : 'none';
            }
        });

        // Toggle form actions and upload button
        formActions.style.display = isEditMode ? 'none' : 'flex';
        if (uploadBtn) {
            uploadBtn.style.display = isEditMode ? 'none' : 'block';
        }
    }

    // Profile Picture Preview
    const profilePicture = document.getElementById('profilePicture');
    if (profilePicture) {
        profilePicture.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Event Listeners
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', toggleEditMode);
    }

    document.querySelector('.cancel-btn').addEventListener('click', function(e) {
        e.preventDefault();
        toggleEditMode();
    });

    // Handle Grade Level Change
    const gradeLevelSelect = document.getElementById('gradeLevel');
    if (gradeLevelSelect) {
        gradeLevelSelect.addEventListener('change', updateSectionOptions);
    }

    // Form submission
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('update_profile.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                alert('Profile updated successfully!');
                location.reload();
            } else {
                alert('Error updating profile: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the profile');
        }
    });
});

// Function to toggle edit mode
function toggleEditMode() {
    const displayFields = document.querySelectorAll('[id$="Display"]');
    const editFields = document.querySelectorAll('select[id], input[type="file"]');
    const formActions = document.querySelector('.form-actions');
    const editBtn = document.getElementById('editProfileBtn');
    const uploadBtn = document.querySelector('.upload-btn');
    
    // Toggle display fields
    displayFields.forEach(field => {
        field.style.display = field.style.display === 'none' ? 'block' : 'none';
    });
    
    // Toggle edit fields
    editFields.forEach(field => {
        field.style.display = field.style.display === 'none' ? 'block' : 'none';
    });
    
    // Toggle form actions
    formActions.style.display = formActions.style.display === 'none' ? 'flex' : 'none';
    
    // Toggle edit button
    editBtn.style.display = editBtn.style.display === 'none' ? 'block' : 'none';
    
    // Toggle upload button
    if (uploadBtn) {
        uploadBtn.style.display = uploadBtn.style.display === 'none' ? 'block' : 'none';
    }
    
    // If entering edit mode, update section options based on current grade level
    if (formActions.style.display === 'flex') {
        updateSectionOptions();
    }
}