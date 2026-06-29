document.addEventListener('DOMContentLoaded', function() {
    const quarterFilter = document.getElementById('quarterFilter');
    
    quarterFilter.addEventListener('change', function() {
        // Add filtering logic here
        const quarter = this.value;
        filterGradesByQuarter(quarter);
    });

    function filterGradesByQuarter(quarter) {
        // Implement grade filtering based on selected quarter
        console.log('Filtering grades for quarter:', quarter);
        // You'll need to implement the actual filtering logic
    }
});