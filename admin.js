// Document ready function to initialize all components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize auto-hide alerts
    initAlerts();
    
    // Initialize search functionality
    initSearchBar();
    
    // Initialize filters
    initFilters();

    const gradeLevel = document.getElementById('grade_level');
    if (gradeLevel) {
        updateSectionOptions();
    }

    const gradeLevelFilter = document.getElementById('gradeLevelFilter');
    const sectionFilter = document.getElementById('sectionFilter');
    const quarterFilter = document.getElementById('quarterFilter');
    
    if (gradeLevelFilter) {
        gradeLevelFilter.addEventListener('change', updateSectionFilter);
        updateSectionFilter(); // Initialize sections
    }
    
    if (sectionFilter) {
        sectionFilter.addEventListener('change', filterGrades);
    }
    
    if (quarterFilter) {
        quarterFilter.addEventListener('change', function() {
            filterGrades();
        });
    }

    const gradeLevelSelect = document.getElementById('grade_level_select');
    if (gradeLevelSelect) {
        gradeLevelSelect.addEventListener('change', function() {
            const sectionSelect = document.getElementById('section_select');
            
            // Clear existing options except "All Sections"
            while (sectionSelect.options.length > 1) {
                sectionSelect.remove(1);
            }
            
            const gradeLevel = this.value;
            if (gradeLevel && gradeSections[gradeLevel]) {
                gradeSections[gradeLevel].forEach(section => {
                    const option = document.createElement('option');
                    option.value = section;
                    option.textContent = `Section ${section}`;
                    sectionSelect.appendChild(option);
                });
            }
        });
    }

    // Add validation for LRN
    const lrnInput = document.getElementById('LRN');
    if (lrnInput) {
        lrnInput.addEventListener('input', function() {
            // Force numeric input only
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limit to 12 characters
            if (this.value.length > 12) {
                this.value = this.value.slice(0, 12);
            }
            
            validateLRN(this);
        });
    }

    // Toggle for showing only students with grades
    const showOnlyWithGrades = document.getElementById('showOnlyWithGrades');
    if (showOnlyWithGrades) {
        showOnlyWithGrades.addEventListener('change', function() {
            const rows = document.querySelectorAll('.averages-table tbody tr');
            
            rows.forEach(row => {
                if (this.checked) {
                    if (row.getAttribute('data-has-grades') === 'false') {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = '';
                }
            });
        });
    }

    // Initial setup for the sidebar/dashboard
    const menuBtn = document.querySelector('.menu-btn');
    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('sidebar-active');
            this.classList.toggle('active');
        });
    }
});

// Auto-hide alert messages
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                // Fade out effect
                alert.style.opacity = '1';
                
                (function fade() {
                    if ((alert.style.opacity -= 0.1) < 0) {
                        alert.style.display = 'none';
                        
                        // Clean up URL params after alert is hidden
                        const url = new URL(window.location);
                        if (url.searchParams.has('success') || url.searchParams.has('error')) {
                            url.searchParams.delete('success');
                            url.searchParams.delete('error');
                            window.history.replaceState({}, document.title, url);
                        }
                    } else {
                        requestAnimationFrame(fade);
                    }
                })();
            });
        }, 5000);
    }
}

// Enhanced search functionality
function initSearchBar() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // First table - Student Records
            const studentTable = document.querySelector('.admin-table:not(.grades-table)');
            if (studentTable) {
                const studentRows = studentTable.querySelectorAll('tbody tr');
                filterTable(studentRows, searchTerm);
            }
        });
    }
}

// Filter tables by search term
function filterTable(rows, searchTerm) {
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (searchTerm === '' || text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Initialize filters
function initFilters() {
    const quarterFilter = document.getElementById('quarterFilter');
    const sectionFilter = document.getElementById('sectionFilter');
    
    if (quarterFilter) {
        quarterFilter.addEventListener('change', function() {
            filterByQuarter(this.value);
        });
    }
    
    if (sectionFilter) {
        sectionFilter.addEventListener('change', function() {
            filterBySection(this.value);
        });
    }
}

// Filter grades by quarter
function filterByQuarter(quarter) {
    const gradeRows = document.querySelectorAll('.grades-table tbody tr');
    
    gradeRows.forEach(row => {
        if (!quarter || row.dataset.quarter === quarter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Filter students by section
function filterBySection(section) {
    const studentSelect = document.getElementById('student_select');
    if (!studentSelect) return;
    
    const options = studentSelect.options;
    
    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const studentSection = option.getAttribute('data-section');
        
        if (!section || !studentSection || studentSection === section) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
}

// Confirm student deletion
function confirmDelete(userId) {
    if (confirm('Are you sure you want to delete this student? This will also delete all associated grades.')) {
        window.location.href = `admin.php?delete=${userId}`;
    }
}

// Confirm grade deletion
function confirmGradeDelete(userId, subject, quarter, studentName) {
    if (confirm(`Are you sure you want to delete the ${subject} grade for ${studentName}, Quarter ${quarter}?`)) {
        window.location.href = `admin.php?delete_grade=1&user_id=${userId}&subject=${encodeURIComponent(subject)}&quarter=${quarter}`;
    }
}

// Open the edit form modal
function openEditForm() {
    document.getElementById('editForm').style.display = 'block';
}

// Close the edit form modal
function closeEditForm() {
    document.getElementById('editForm').style.display = 'none';
}

// Define sections for each grade level
const gradeSections = {
    'Grade 7': ['Babbage', 'Byron', 'Cooper', 'Eckert', 'Kilby', 'Leibniz', 'Liscov', 'Osborne', 'Pascal', 'Rossum', 'Stallman', 'Thompson', 'Wilkes'],
    'Grade 8': ['Andreessen', 'Berners', 'Brin', 'Engelbart', 'Gray', 'Hamilton', 'Mauchly', 'Turing', 'Wilson'],
    'Grade 9': ['Atanasoff', 'Hollerith', 'Hopper', 'Hull', 'Iverson', 'Johansen', 'Johnson', 'Neumann', 'Page', 'Perlis'],
    'Grade 10': ['Allen', 'Banatao', 'Bryce', 'Cray', 'Minsky', 'Shannon', 'Stibitz', 'Torvalds', 'Wozniak', 'Zuse']
};

// Function to update section dropdown based on selected grade level
function updateSectionOptions() {
    const gradeLevel = document.getElementById('grade_level').value;
    const sectionDropdown = document.getElementById('section');
    
    // Clear existing options
    sectionDropdown.innerHTML = '';
    
    // Add new options based on selected grade level
    if (gradeSections[gradeLevel]) {
        gradeSections[gradeLevel].forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = `Section ${section}`;
            sectionDropdown.appendChild(option);
        });
    }
}

// Open the edit modal with student data
function openEditModal(userId, username, firstName, lastName, email, lrn, section, gradeLevel, schoolYear, birthday, gender) {
    document.getElementById('User_ID').value = userId;
    document.getElementById('Username').value = username;
    document.getElementById('FirstName').value = firstName;
    document.getElementById('LastName').value = lastName;
    document.getElementById('Email').value = email;
    document.getElementById('LRN').value = lrn;  // This line correctly sets the LRN value
    document.getElementById('grade_level').value = gradeLevel;
    // Update sections first, then set the selected section
    updateSectionOptions();
    document.getElementById('section').value = section;
    document.getElementById('school_year').value = schoolYear;
    document.getElementById('Birthday').value = birthday;
    document.getElementById('Gender').value = gender;
    
    // Validate LRN after setting its value
    validateLRN(document.getElementById('LRN'));
    
    openEditForm();
}

// Open grade edit form
function openGradeEditForm(userId, subject, grade, quarter) {
    document.getElementById('student_id').value = userId;
    document.getElementById('subject_name').value = subject;
    document.getElementById('grade').value = grade;
    document.getElementById('quarter').value = quarter;
    
    // Display the modal
    document.getElementById('gradeForm').style.display = 'block';
}

// Close grade edit form
function closeGradeForm() {
    document.getElementById('gradeForm').style.display = 'none';
}

// Update section filter based on selected grade level
function updateSectionFilter() {
    const gradeLevel = document.getElementById('gradeLevelFilter').value;
    const sectionFilter = document.getElementById('sectionFilter');
    const averageRows = document.querySelectorAll('.averages-table tbody tr');
    const gradeRows = document.querySelectorAll('.grades-table tbody tr');
    
    // Clear current options
    sectionFilter.innerHTML = '<option value="">All Sections</option>';
    
    // Get unique sections for the selected grade level
    const sections = new Set();
    
    averageRows.forEach(row => {
        if (!gradeLevel || row.dataset.gradeLevel === gradeLevel) {
            row.style.display = '';
            if (row.dataset.section) {
                sections.add(row.dataset.section);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    gradeRows.forEach(row => {
        if (!gradeLevel || row.dataset.gradeLevel === gradeLevel) {
            row.style.display = '';
            if (row.dataset.section) {
                sections.add(row.dataset.section);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Add section options
    Array.from(sections).sort().forEach(section => {
        const option = document.createElement('option');
        option.value = section;
        option.textContent = section;
        sectionFilter.appendChild(option);
    });
    
    // Trigger filtering
    filterGrades();
}

// Filter grades based on selected grade level, section, and quarter
function filterGrades() {
    const gradeLevelFilter = document.getElementById('gradeLevelFilter').value;
    const sectionFilter = document.getElementById('sectionFilter').value;
    const quarterFilter = document.getElementById('quarterFilter').value;
    
    const rows = document.querySelectorAll('.grades-table tbody tr');
    
    rows.forEach(row => {
        const studentName = row.cells[0].textContent;
        const quarter = row.getAttribute('data-quarter');
        
        // Get student info by name (we'll need to add data attributes later)
        let showRow = true;
        
        // Filter by quarter
        if (quarterFilter && quarter !== quarterFilter) {
            showRow = false;
        }
        
        // Additional filtering by grade level and section
        // This will require data attributes on the rows
        
        row.style.display = showRow ? '' : 'none';
    });
}

// Filter student dropdown based on selected grade level and section
function updateStudentOptions() {
    const gradeLevelSelect = document.getElementById('grade_level_select').value;
    const sectionSelect = document.getElementById('section_select').value;
    const studentSelect = document.getElementById('student_select');
    
    // Filter student options
    for (let i = 0; i < studentSelect.options.length; i++) {
        const option = studentSelect.options[i];
        
        if (i === 0) {
            // Always show the default option
            option.style.display = '';
            continue;
        }
        
        const studentGradeLevel = option.getAttribute('data-grade-level');
        const studentSection = option.getAttribute('data-section');
        
        // Show if both filters match or if filters are not set
        const showOption = 
            (!gradeLevelSelect || studentGradeLevel === gradeLevelSelect) && 
            (!sectionSelect || studentSection === sectionSelect);
        
        option.style.display = showOption ? '' : 'none';
    }
    
    // Reset student selection if currently selected option is hidden
    if (studentSelect.selectedIndex > 0) {
        const selectedOption = studentSelect.options[studentSelect.selectedIndex];
        if (selectedOption.style.display === 'none') {
            studentSelect.selectedIndex = 0;
        }
    }
}

// Ensure both tables are filtered by section
document.getElementById('sectionFilter').addEventListener('change', function() {
    const section = this.value;
    const averageRows = document.querySelectorAll('.averages-table tbody tr');
    const gradeRows = document.querySelectorAll('.grades-table tbody tr');
    
    averageRows.forEach(row => {
        if (!section || row.dataset.section === section) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    gradeRows.forEach(row => {
        if (!section || row.dataset.section === section) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Add this to your existing code
function validateLRN(lrnField) {
    const lrn = lrnField.value;
    const lrnPattern = /^[0-9]{12}$/;
    
    if (!lrnPattern.test(lrn)) {
        lrnField.setCustomValidity('LRN must be exactly 12 digits');
        return false;
    } else {
        lrnField.setCustomValidity('');
        return true;
    }
}

// Add this to your existing admin.js file

function toggleAdminMenu() {
    const profileMenu = document.getElementById('adminProfileMenu');
    profileMenu.classList.toggle('active');
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInside = event.target.closest('.profile-dropdown');
        if (!isClickInside && profileMenu.classList.contains('active')) {
            profileMenu.classList.remove('active');
        }
    }, { once: true });
}
