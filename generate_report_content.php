<?php 
include('db_connect.php');



// Get the filter values from the AJAX request
$program_id = $_GET['program'];  // This is the program_id (e.g., 'BSIT')
$academic_year = $_GET['academic_year'];
$semester = $_GET['semester'];
$year = $_GET['year']; // Capture the selected year

// Fetch the program short code (e.g., 'BSIT' or 'BSCE') from the programs table
$program_query = $conn->query("SELECT program_id FROM programs WHERE program_id = '$program_id'");
$program = $program_query->fetch_assoc();

if ($program) {
    $program_code = $program['program_id'];  // Use 'program_id' as the short code
} else {
    $program_code = 'Unknown';  // Fallback value in case of errors
}

// Extract the last two digits of the selected year
$year_short = substr($year, -2);

// Query to find the latest section for this program and academic year
$latest_section_query = $conn->query("SELECT section_name FROM sections WHERE program_id = '$program_id' AND academic_year = '$academic_year' ORDER BY section_name DESC LIMIT 1");
$latest_section = $latest_section_query->fetch_assoc();

// If a section exists, increment the letter
if ($latest_section) {
    $last_letter = substr($latest_section['section_name'], -1);  // Get the last letter
    $section_letter = chr(ord($last_letter) + 1);  // Increment the letter (e.g., from A to B)
} else {
    $section_letter = 'A';  // Start with A if no section exists
}

// Generate the new section name
$section_name = "$program_code-$academic_year$section_letter"; // BSIT-4A, for example

// Generate the reference number in the desired format
$reference_number = "$section_name-$year_short";// E.g., BSIT-4A-24

// Format academic year for display
function formatAcademicYear($year) {
    switch ($year) {
        case '1':
            return '1st Year';
        case '2':
            return '2nd Year';
        case '3':
            return '3rd Year';
        case '4':
            return '4th Year';
        default:
            return $year;  // Return as-is if it's not one of the expected values
    }
}
$formatted_academic_year = formatAcademicYear($academic_year);  // Format academic year

// Check if the section already exists in the sections table
$check_section = $conn->prepare("SELECT section_id FROM sections WHERE section_name = ?");
$check_section->bind_param("s", $section_name);
$check_section->execute();
$result = $check_section->get_result();

if ($result->num_rows == 0) {
    // Insert the section into the sections table
    $insert_section = $conn->prepare("INSERT INTO sections (section_name, program_id, academic_year) VALUES (?, ?, ?)");
    $insert_section->bind_param("ssi", $section_name, $program_code, $academic_year);
    $insert_section->execute();
    $section_id = $conn->insert_id;
} else {
    // Use existing section
    $section_data = $result->fetch_assoc();
    $section_id = $section_data['section_id'];
}

$courses_data = []; 

// Updated query to fetch the courses based on program_id, academic_year, and semester
$courses = $conn->query("
    SELECT 
        c.id AS course_id,  -- Ensure course_id is selected
        c.course_code, 
        c.course_name,
        c.units,
        (CASE 
            WHEN c.is_lecture = 1 AND c.is_lab = 1 THEN 'Lecture & Laboratory'
            WHEN c.is_lecture = 1 THEN 'Lecture'
            WHEN c.is_lab = 1 THEN 'Laboratory'
        END) AS component,
        (c.lecture_hours + c.lab_hours) AS hours
    FROM 
        courses c
    JOIN 
        program_courses pc ON pc.course_id = c.id
    WHERE 
        pc.program_id = '$program_id' 
        AND c.academic_year = '$academic_year' 
        AND c.semester = '$semester'
    ORDER BY 
        c.course_code ASC;
");

$total_units = 0; // Initialize a variable to store the total units
$processed_courses = []; // Array to track unique course codes

// Generate the report header based on the filters
$html = "<h3>Program Report - $formatted_academic_year : $semester : $year</h3>";
$html .= "<h5>Reference Number: $reference_number</h5>";
$html .= "<h5>Section: $section_name</h5>";

$html .= '<table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Component</th>
                    <th>Hours</th>
                    <th>Units</th>
                </tr>
            </thead>
            <tbody>';

// Fetch and display each course in the table
while ($row = $courses->fetch_assoc()) {
    $courses_data[] = $row; // Add each course to the courses_data array

    error_log(print_r($courses_data, true));


    $html .= '<tr>
                <td>' . $row['course_code'] . '</td>
                <td>' . $row['course_name'] . '</td>
                <td>' . $row['component'] . '</td>
                <td>' . $row['hours'] . '</td>
                <td>' . $row['units'] . '</td>
              </tr>';

    if (!in_array($row['course_code'], $processed_courses)) {
        $total_units += $row['units'];
        $processed_courses[] = $row['course_code'];
    }
}

$html .= '</tbody></table>';
$html .= "<h5>Total Units: $total_units</h5>";

$html .= "<button id='save_report' data-reference='$reference_number' 
              data-program-id='$program_code'
              data-academic-year='$academic_year' 
              data-semester='$semester' 
              data-year='$year' 
              data-total-units='$total_units'
              data-section='$section_id'
              data-courses='" . htmlspecialchars(json_encode($courses_data), ENT_QUOTES, 'UTF-8') . "'>
              Save Report
          </button>";


// Return the generated HTML
echo $html;
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(document).on('click', '#save_report', function () {
    var referenceNumber = $(this).data('reference');
    var programId = $(this).data('program-id');
    var academicYear = $(this).data('academic-year');
    var semester = $(this).data('semester');
    var year = $(this).data('year');
    var totalUnits = $(this).data('total-units');
    var sectionId = $(this).data('section');
    var courses = JSON.parse($(this).attr('data-courses')); // Parse courses JSON

    // Disable the button to prevent duplicate submissions
    var button = $(this);
    button.prop('disabled', true);

    if (courses.length === 0) {
        toastr.warning('No courses found for the selected filters.', 'Warning');
        button.prop('disabled', false);
        return;
    }

    $.ajax({
        url: 'ajax.php?action=save_report',
        method: 'POST',
        data: {
            reference_number: referenceNumber,
            program_id: programId,
            academic_year: academicYear,
            semester: semester,
            year: year,
            total_units: totalUnits,
            section_id: sectionId,
            courses: JSON.stringify(courses)
        },
        success: function (response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
                toastr.success(res.message || 'Report saved successfully!', 'Success');

                // Automatically reload the page
                setTimeout(function () {
                    location.reload(); // Reload the page
                }, 2000); // Add a small delay for the user to see the success message
            } else {
                toastr.error(res.message || 'Something went wrong.', 'Error');
                button.prop('disabled', false);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText); // Debug server response
            toastr.error('An unexpected error occurred: ' + error, 'Error');
            button.prop('disabled', false);
        }
    });
});




</script>