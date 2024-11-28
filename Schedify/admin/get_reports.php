<?php
// Include the database connection file
include('db_connect.php');

// Correct query to fetch reports with a JOIN to programs for program name and sections for section name
$sql = "
    SELECT 
        generated_reports.report_id,  -- Use report_id instead of id
        programs.program_name, 
        generated_reports.academic_year, 
        generated_reports.semester, 
        generated_reports.year, 
        generated_reports.reference_number, 
        generated_reports.total_units, 
        generated_reports.created_at,
        sections.section_name  -- Fetch section name from the sections table
    FROM generated_reports
    JOIN programs ON generated_reports.program_id = programs.program_id
    JOIN sections ON generated_reports.section_id = sections.section_id  -- Join with sections table
";

$result = $conn->query($sql);

$reports = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format academic year
        switch ($row['academic_year']) {
            case '1':
                $row['academic_year'] = '1st Year';
                break;
            case '2':
                $row['academic_year'] = '2nd Year';
                break;
            case '3':
                $row['academic_year'] = '3rd Year';
                break;
            case '4':
                $row['academic_year'] = '4th Year';
                break;
        }
        $reports[] = $row;
    }
}

// Return JSON response
echo json_encode($reports);

// Close the connection if needed (optional based on your db_connect.php settings)
$conn->close();
?>
