<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
$action = $_GET['action'];
include_once 'admin_class.php';
include('db_connect.php');

$crud = new Action();
if ($action == 'login') {
    $login = $crud->login();
    echo $login; // Return the role-based code to the front-end
}

if ($action == 'login2') {
    $login = $crud->login2();
    echo $login; // Return the role-based code to the front-end
}

if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'update_account'){
	$save = $crud->update_account();
	if($save)
		echo $save;
}
if($action == "save_settings"){
	$save = $crud->save_settings();
	if($save)
		echo $save;
}
if($action == "save_course"){
	$save = $crud->save_course();
	if($save)
		echo $save;
}

if($action == "delete_course"){
	$delete = $crud->delete_course();
	if($delete)
		echo $delete;
}
if($action == "save_subject"){
	$save = $crud->save_subject();
	if($save)
		echo $save;
}

if($action == "delete_subject"){
	$delete = $crud->delete_subject();
	if($delete)
		echo $delete;
}
if($action == "save_faculty") {
    extract($_POST);
    $data = " lastname = '$lastname' ";
    $data .= ", firstname = '$firstname' ";
    $data .= ", email = '$email' ";
    $data .= ", contact = '$contact' ";
    $data .= ", gender = '$gender' ";
    $data .= ", address = '$address' ";
    
    if(empty($id)) {
        // Insert new faculty
        $save = $conn->query("INSERT INTO faculty SET $data");
        $faculty_id = $conn->insert_id;
    } else {
        // Update existing faculty
        $save = $conn->query("UPDATE faculty SET $data WHERE id = $id");
        $faculty_id = $id;
    }

    if ($save) {
        // Manage course assignments
        $conn->query("DELETE FROM faculty_courses WHERE faculty_id = $faculty_id"); // Remove old assignments

        // Only insert course assignments if there are selected courses
        if (isset($_POST['course_ids']) && is_array($_POST['course_ids'])) {
            foreach ($_POST['course_ids'] as $course_id) {
                $conn->query("INSERT INTO faculty_courses (faculty_id, course_id) VALUES ($faculty_id, $course_id)");
            }
        }
        echo 1;
    } else {
        echo 0;
    }
}


if($action == "delete_faculty"){
	$save = $crud->delete_faculty();
	if($save)
		echo $save;
}

if($action == "save_schedule"){
	$save = $crud->save_schedule();
	if($save)
		echo $save;
}
if ($action == "delete_section_schedules") {
    $save = $crud->delete_section_schedules();
    if ($save) {
        echo $save;
    }
}
if ($action == "update_schedule") {
    ob_clean(); // Clear output buffer
    header('Content-Type: application/json'); // Ensure JSON response

    // Log received POST data
    error_log("POST data: " . json_encode($_POST));

    // Validate required fields
    if (!isset($_POST['id'], $_POST['title'], $_POST['start'], $_POST['end'], $_POST['room'], $_POST['professor'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $id = $_POST['id'];
    $title = $_POST['title'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $room_name = $_POST['room']; // Use room_name from POST data
    $professor = trim($_POST['professor']); // Trim any unnecessary spaces

    include_once 'admin_class.php';
    $crud = new Action();

    // Log the data being sent for update
    error_log("Updating event ID: $id | Title: $title | Start: $start | End: $end | Room: $room_name | Professor: $professor");

    // Attempt to update the schedule
    $save = $crud->update_schedule_details($id, $title, $start, $end, $room_name, $professor);

    if ($save) {
        echo json_encode(['success' => true, 'message' => 'Schedule updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update schedule.']);
    }
    exit;
}




if ($action == "update_schedule_bulk") {
    ob_clean(); // Clear any previous output
    header('Content-Type: application/json'); // Ensure JSON response

    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Received events payload: ' . json_encode($data)); // Debug input

    $events = $data['events'] ?? [];
    if (empty($events)) {
        echo json_encode(['success' => false, 'message' => 'No events to update.']);
        exit;
    }

    include_once 'admin_class.php';
    $crud = new Action();

    $errors = [];
    foreach ($events as $event) {
        $id = $event['id'] ?? null;
        $title = $event['title'] ?? null;
        $start = $event['start'] ?? null;
        $end = $event['end'] ?? null;
        $room = $event['room'] ?? null;
        $professor = $event['professor'] ?? null;

        error_log("Processing Event: ID=$id, Title=$title, Start=$start, End=$end, Room=$room, Professor=$professor");

        if (empty($id) || empty($title) || empty($start) || empty($end)) {
            $errors[] = "Missing required parameters for event ID $id.";
            continue;
        }

        $result = $crud->update_schedule_details($id, $title, $start, $end, $room, $professor);

        if (!$result) {
            $errors[] = "Failed to update event ID $id.";
        }
    }

    // Send JSON response
    if (empty($errors)) {
        echo json_encode(['success' => true]); // All events updated successfully
    } else {
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]); // Send error details
    }

    exit; // Ensure no further PHP output
}

if ($action == "get_schedule") {
    $crud->get_schedule(); // This will echo the JSON response directly
}

if ($_GET['action'] == 'save_report') {
    include('db_connect.php');

    $debug = []; // Array to hold debugging information

    $reference_number = $_POST['reference_number'];
    $program_id = $_POST['program_id'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $total_units = $_POST['total_units'];
    $section_id = $_POST['section_id'];

    // Decode the courses data
    $courses = isset($_POST['courses']) ? json_decode($_POST['courses'], true) : [];
    $debug['raw_courses'] = $courses; // Add raw courses data to debug info

    if (json_last_error() !== JSON_ERROR_NONE) {
        $debug['json_error'] = json_last_error_msg(); // Log JSON error
        echo json_encode(['status' => 'error', 'message' => 'Invalid courses data.', 'debug' => $debug]);
        exit();
    }

    if (!is_array($courses) || empty($courses)) {
        $debug['validation_error'] = 'Courses data is empty or not an array.';
        echo json_encode(['status' => 'error', 'message' => 'No valid courses data received.', 'debug' => $debug]);
        exit();
    }

    // Add debug information about the inputs
    $debug['inputs'] = [
        'reference_number' => $reference_number,
        'program_id' => $program_id,
        'academic_year' => $academic_year,
        'semester' => $semester,
        'year' => $year,
        'total_units' => $total_units,
        'section_id' => $section_id,
    ];

    $stmt = $conn->prepare("INSERT INTO generated_reports (program_id, academic_year, semester, year, reference_number, total_units, section_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssii", $program_id, $academic_year, $semester, $year, $reference_number, $total_units, $section_id);

    if ($stmt->execute()) {
        $debug['generated_report_id'] = $conn->insert_id; // Log the report ID if successful

        foreach ($courses as $course) {
            // Use correct key for course_id
            $course_id = $course['course_id'] ?? null; 

            // Log each course being processed
            $debug['processing_course'] = $course;

            if ($course_id && is_numeric($course_id)) {
                $insert_section_course = $conn->prepare("INSERT INTO section_courses (section_id, course_id) VALUES (?, ?)");
                $insert_section_course->bind_param("ii", $section_id, $course_id);
                if (!$insert_section_course->execute()) {
                    $debug['insert_section_course_error'] = $insert_section_course->error; // Log any errors
                    echo json_encode(['status' => 'error', 'message' => 'Error inserting course into section_courses.', 'debug' => $debug]);
                    exit();
                }
            } else {
                $debug['invalid_course'] = $course; // Log invalid course data
                echo json_encode(['status' => 'error', 'message' => 'Invalid course data.', 'debug' => $debug]);
                exit();
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Report and courses saved successfully!', 'debug' => $debug]);
    } else {
        $debug['insert_report_error'] = $stmt->error; // Log the error for saving the report
        echo json_encode(['status' => 'error', 'message' => 'Error saving report.', 'debug' => $debug]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

if ($action == "save_room") {
    // Retrieve the room data from POST request
    $room_id = $_POST['room_id'];
    $room_name = $_POST['room_name'];
    
    // Check if the room exists
    $check = $conn->query("SELECT * FROM rooms WHERE room_id = '$room_id'");
    
    if ($check->num_rows > 0) {
        // Update room
        $query = "UPDATE rooms SET room_name = '$room_name' WHERE room_id = '$room_id'";
        if ($conn->query($query)) {
            echo 2;  // Room updated successfully
        } else {
            echo 0;  // Update failed
        }
    } else {
        // Insert new room
        $query = "INSERT INTO rooms (room_id, room_name) VALUES ('$room_id', '$room_name')";
        if ($conn->query($query)) {
            echo 1;  // Room added successfully
        } else {
            echo 0;  // Insert failed
        }
    }
}

if ($action == "delete_room") {
    // Retrieve room_id from POST request
    $room_id = $_POST['id'];

    // Delete the room from the database
    $query = "DELETE FROM rooms WHERE room_id = '$room_id'";
    if ($conn->query($query)) {
        echo 1;  // Room deleted successfully
    } else {
        echo 0;  // Delete failed
    }
}

if ($_GET['action'] == 'generate_schedule') {
    $section_id = $_POST['section_id'] ?? null; // Use null coalescing operator to handle missing parameter

    if (!$section_id) {
        // Return an error if section_id is not provided
        echo json_encode([
            "status" => "error",
            "message" => "Section ID is required to generate a schedule."
        ]);
        exit();
    }

    $action = new Action();
    $result = $action->generate_auto_schedule($conn, $section_id); // Pass section_id as parameter

    if ($result === 'success') {
        // Fetch the updated schedule for the section
        $scheduleData = $action->get_schedule_data($section_id); // Make sure get_schedule_data is implemented

        echo json_encode([
            "status" => "success",
            "scheduleData" => $scheduleData
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $result
        ]);
    }    
}
if ($_GET['action'] === 'get_rooms') {
    ob_clean(); // Clear any output before JSON
    header('Content-Type: application/json'); // Ensure JSON response

    // Query the database to get rooms
    $query = "SELECT room_id, room_name FROM rooms ORDER BY room_name ASC";
    $result = $conn->query($query);

    // Check for errors
    if (!$result) {
        error_log("Database Error: " . $conn->error); // Log database error
        echo json_encode([]); // Return an empty array in case of error
        exit;
    }

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row; // Add each row to the rooms array
    }

    echo json_encode($rooms); // Encode and send the result as JSON
    exit; // Terminate script
}


if ($action === 'get_professors') {
    ob_clean();
    header('Content-Type: application/json');

    try {
        $query = "SELECT id, full_name FROM faculty ORDER BY full_name ASC";
        $result = $conn->query($query);

        if (!$result) {
            throw new Exception("Failed to fetch professors: " . $conn->error);
        }

        $professors = [];
        while ($row = $result->fetch_assoc()) {
            $professors[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $professors]);
    } catch (Exception $e) {
        error_log("Error in get_professors: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit;
}



ob_end_flush();
?>
