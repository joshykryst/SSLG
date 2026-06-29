<?php
/**
 * Schedule Data File
 * This file provides schedule data for all grade levels and sections
 */

// Connect to database
require_once 'Config.php';

function getScheduleData() {
    global $conn;
    
    // Initialize the schedule array
    $scheduleData = [
        'Grade 7' => [],
        'Grade 8' => [],
        'Grade 9' => [],
        'Grade 10' => []
    ];
    
    // Get all sections first to ensure we have empty arrays for each section
    $sectionQuery = "SELECT DISTINCT grade_level, section_name FROM class_schedules ORDER BY grade_level, section_name";
    $sectionResult = mysqli_query($conn, $sectionQuery);
    
    if ($sectionResult) {
        while ($sectionRow = mysqli_fetch_assoc($sectionResult)) {
            $grade = $sectionRow['grade_level'];
            $section = $sectionRow['section_name'];
            
            // Initialize this section with an empty array
            $scheduleData[$grade][$section] = [];
        }
    }
    
    // Now get all schedule data
    $scheduleQuery = "SELECT * FROM class_schedules ORDER BY grade_level, section_name, time_slot";
    $result = mysqli_query($conn, $scheduleQuery);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $grade = $row['grade_level'];
            $section = $row['section_name'];
            $timeSlot = $row['time_slot'];
            $day = $row['day_of_week'];
            
            // Add to schedule data array
            if (!isset($scheduleData[$grade][$section][$timeSlot])) {
                $scheduleData[$grade][$section][$timeSlot] = [];
            }
            
            $scheduleData[$grade][$section][$timeSlot][$day] = [
                'subject' => $row['subject'],
                'teacher' => $row['teacher'],
                'room' => $row['room'],
                'type' => $row['class_type'] // For color-coding classes
            ];
        }
    }
    
    return $scheduleData;
}

// Return schedule data
return getScheduleData();