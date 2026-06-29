<div class="grades-container">
   
    <div class="grades-summary">
        <h2>Academic Summary</h2>
        <div class="summary-stats">
            <div class="stat">
                <span class="stat-label">Overall Average:</span>
                <span class="stat-value"><?php echo number_format($summaryData['overall_average'], 2); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Total Subjects:</span>
                <span class="stat-value"><?php echo $summaryData['total_subjects']; ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Academic Standing:</span>
                <span class="stat-value standing"><?php echo $academicStanding; ?></span>
            </div>
        </div>
    </div>

   
    <div class="grades-section">
        <div class="grades-header">
            <h2>Current Grades</h2>
            <div class="quarter-selector">
                <span>Select Quarter: </span>
                <div class="quarter-buttons">
                    <?php for($q = 1; $q <= 4; $q++): ?>
                        <a href="?quarter=<?php echo $q; ?>" 
                           class="quarter-btn <?php echo ($quarter == $q) ? 'active' : ''; ?>">
                           Quarter <?php echo $q; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <div class="grades-table-wrapper">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($grades) > 0): ?>
                        <?php foreach($grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                <td class="grade-value"><?php echo number_format($grade['grade'], 2); ?></td>
                                <td class="<?php echo $grade['grade'] >= 75 ? 'passed' : 'failed'; ?>">
                                    <?php echo htmlspecialchars($grade['remarks']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="no-grades">No grades available for Quarter <?php echo $quarter; ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>