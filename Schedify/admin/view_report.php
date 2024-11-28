<?php
include('db_connect.php');
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/php-error.log');  // Define your log file
ini_set('display_errors', 0);  // Don't display errors in the output
session_start();

// Check if the report_id is provided in the GET request
if (isset($_GET['id'])) {
    $report_id = $_GET['id'];

    // Query to fetch the report details along with the section_name
    $sql = "
        SELECT 
            generated_reports.report_id, 
            programs.program_name, 
            generated_reports.academic_year, 
            generated_reports.semester, 
            generated_reports.year, 
            generated_reports.reference_number, 
            generated_reports.total_units, 
            sections.section_name,  -- Fetch section_name
            generated_reports.section_id,
            generated_reports.created_at
        FROM generated_reports
        JOIN programs ON generated_reports.program_id = programs.program_id
        JOIN sections ON generated_reports.section_id = sections.section_id  -- Join with sections to get section_name
        WHERE generated_reports.report_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
        

        // Fetch the courses for this report using the section_id
        $courses_query = $conn->prepare("
    SELECT 
        courses.course_code, 
        courses.course_name, 
        courses.units,
        (CASE 
            WHEN courses.is_lecture = 1 AND courses.is_lab = 1 THEN 'Lecture & Laboratory'
            WHEN courses.is_lecture = 1 THEN 'Lecture'
            WHEN courses.is_lab = 1 THEN 'Laboratory'
        END) AS component,
        (courses.lecture_hours + courses.lab_hours) AS hours
    FROM 
        section_courses
    JOIN 
        courses ON section_courses.course_id = courses.id
    WHERE 
        section_courses.section_id = ?
");

        $courses_query->bind_param("i", $report['section_id']);
        $courses_query->execute();
        $courses_result = $courses_query->get_result();
         
        if ($courses_result->num_rows > 0) {
            while ($course = $courses_result->fetch_assoc()) {
                $report['courses'][] = $course;
            }
        } else {
            error_log("No courses found for section_id: " . $report['section_id']); // Log for debugging
        }


        // Return the report details as a JSON response
        echo json_encode($report);
    } else {
        echo json_encode(['error' => 'Report not found.']);
    }
} else {
    echo json_encode(['error' => 'No report ID provided.']);
}

$conn->close();
?>
