<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	// Helper Functions for Role Checks
	function isAdmin() {
		return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
	}

	function isProgramHead() {
		return isset($_SESSION['role']) && $_SESSION['role'] === 'program_head';
	}

	function isFaculty() {
		return isset($_SESSION['role']) && $_SESSION['role'] === 'faculty';
	}

	function isStudent() {
		return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
	}

	function login() {
		extract($_POST);        
		$qry = $this->db->query("SELECT * FROM users WHERE username = '".$username."' AND password = '".md5($password)."' ");
		
		if ($qry->num_rows > 0) {
			$user = $qry->fetch_array();
			
			// Set session variables
			foreach ($user as $key => $value) {
				if ($key != 'password' && !is_numeric($key)) {
					$_SESSION['login_'.$key] = $value;
				}
			}
			$_SESSION['role'] = $user['role'];
			$_SESSION['program_id'] = $user['program_id'] ?? null;
	
			// Return different codes based on role
			if ($_SESSION['role'] == 'admin') {
				return 1; // Admin login success
			} elseif ($_SESSION['role'] == 'program_head') {
				return 4; // Program Head login success
			} elseif ($_SESSION['role'] == 'faculty') {
				return 5; // Faculty login success
			} elseif ($_SESSION['role'] == 'student') {
				return 6; // Student login success
			}
		} else {
			return 3; // Incorrect username or password
		}
	}
	
	
	function login2() {
		extract($_POST);
		if (isset($email)) $username = $email;
		$qry = $this->db->query("SELECT * FROM users WHERE username = '".$username."' AND password = '".md5($password)."' ");
		if ($qry->num_rows > 0) {
			$user = $qry->fetch_array();
			foreach ($user as $key => $value) {
				if ($key != 'password' && !is_numeric($key)) {
					$_SESSION['login_'.$key] = $value;
				}
			}
			$_SESSION['role'] = $user['role'];
			$_SESSION['program_id'] = $user['program_id'] ?? null;
	
			if ($_SESSION['role'] == 'student') {
				return 6; // Student login success
			}
		} else {
			return 3; // Incorrect username or password
		}
	}
	
	
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user() {
		extract($_POST);
	
		// Default values for role and faculty_id
		$faculty_id = NULL;
		$name = NULL;
	
		if (($role == 'faculty' || $role == 'program_head') && !empty($_POST['faculty_id'])) {
			$faculty_id = $_POST['faculty_id'];
	
			// Fetch the name of the selected faculty member
			$faculty_query = $this->db->query("SELECT concat(firstname, ' ', lastname) as name FROM faculty WHERE id = ".$faculty_id);
			if ($faculty_query->num_rows > 0) {
				$faculty_row = $faculty_query->fetch_assoc();
				$name = $faculty_row['name'];
			}
		}
	
		// Check if the username already exists (case-insensitive)
		$check = $this->db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)" . (!empty($id) ? " AND id != ?" : ""));
		if (!empty($id)) {
			$check->bind_param('si', $username, $id);
		} else {
			$check->bind_param('s', $username);
		}
		$check->execute();
		$check->store_result();
		if ($check->num_rows > 0) {
			return 2; // Username already exists
		}
	
		// Prepare the password hash only if a new password is provided
		$password_hash = null; // Default to null, indicating no password update
		if (!empty($password)) {
			$password_hash = md5($password);  // Hash the password with MD5
		}
	
		// Insert or update user details
		if (empty($id)) { // New user
			$stmt = $this->db->prepare("INSERT INTO users (name, username, password, role, program_id, faculty_id) VALUES (?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('sssssi', $name, $username, $password_hash, $role, $program_id, $faculty_id);
			$save = $stmt->execute();
		} else { // Updating an existing user
			// Choose the correct query based on whether the password is updated
			if (!is_null($password_hash)) {
				$stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, password = ?, role = ?, program_id = ?, faculty_id = ? WHERE id = ?");
				$stmt->bind_param('sssssii', $name, $username, $password_hash, $role, $program_id, $faculty_id, $id);
			} else {
				$stmt = $this->db->prepare("UPDATE users SET name = ?, username = ?, role = ?, program_id = ?, faculty_id = ? WHERE id = ?");
				$stmt->bind_param('ssssii', $name, $username, $role, $program_id, $faculty_id, $id);
			}
			$save = $stmt->execute();
		}
	
		if (!$save) {
			return $this->db->error;
		}
	
		return 1;  // Success
	}
	
	
	
	
	
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function signup(){
		extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("INSERT INTO users set ".$data);
		if($save){
			$uid = $this->db->insert_id;
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
							$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
							$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
							$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("INSERT INTO alumnus_bio set $data ");
			if($data){
				$aid = $this->db->insert_id;
				$this->db->query("UPDATE users set alumnus_id = $aid where id = $uid ");
				$login = $this->login2();
				if($login)
				return 1;
			}
		}
	}
	function update_account(){
		extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		if(!empty($password))
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
		if($save){
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
							$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
							$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
							$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
			if($data){
				foreach ($_SESSION as $key => $value) {
					unset($_SESSION[$key]);
				}
				$login = $this->login2();
				if($login)
				return 1;
			}
		}
	}

	function save_settings(){
		extract($_POST);
		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['settings'][$key] = $value;
		}

			return 1;
				}
	}

	
	function save_course() {
		extract($_POST);
	
		// Default values for is_lecture and is_lab
		$is_lecture = isset($is_lecture) && $is_lecture ? 1 : 0;
		$is_lab = isset($is_lab) && $is_lab ? 1 : 0;
	
		// Set default values for lecture and lab hours if applicable
		$lecture_hours = $is_lecture ? 2 : 0; // Default lecture hours are 2 if lecture is selected
		$lab_hours = $is_lab ? 3 : 0;         // Default lab hours are 3 if lab is selected
	
		// Insert or update course details
		if (empty($id)) {
			$stmt = $this->db->prepare("INSERT INTO courses (course_code, course_name, units, semester, academic_year, is_lecture, is_lab, lecture_hours, lab_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('ssissiiii', $course_code, $course_name, $units, $semester, $academic_year, $is_lecture, $is_lab, $lecture_hours, $lab_hours);
			$save = $stmt->execute();
			$course_id = $this->db->insert_id; // Get the new course ID
		} else {
			$stmt = $this->db->prepare("UPDATE courses SET course_code = ?, course_name = ?, units = ?, semester = ?, academic_year = ?, is_lecture = ?, is_lab = ?, lecture_hours = ?, lab_hours = ? WHERE id = ?");
			$stmt->bind_param('ssissiiiii', $course_code, $course_name, $units, $semester, $academic_year, $is_lecture, $is_lab, $lecture_hours, $lab_hours, $id);
			$save = $stmt->execute();
			$course_id = $id;
		}
	
		// Check if course insert/update was successful
		if (!$save) {
			return $this->db->error;
		}
	
		// Update or insert course components without deleting
		if ($is_lecture) {
			// Check if lecture component exists
			$stmt = $this->db->prepare("SELECT component_id FROM course_components WHERE course_id = ? AND component_type = 'Lecture'");
			$stmt->bind_param('i', $course_id);
			$stmt->execute();
			$stmt->store_result();
	
			if ($stmt->num_rows > 0) {
				// Update existing lecture component
				$stmt->bind_result($component_id);
				$stmt->fetch();
				$update_component = $this->db->prepare("UPDATE course_components SET hours = ? WHERE component_id = ?");
				$update_component->bind_param('ii', $lecture_hours, $component_id);
				$update_component->execute();
			} else {
				// Insert new lecture component
				$insert_component = $this->db->prepare("INSERT INTO course_components (course_id, component_type, hours) VALUES (?, 'Lecture', ?)");
				$insert_component->bind_param('ii', $course_id, $lecture_hours);
				$insert_component->execute();
			}
		}
	
		if ($is_lab) {
			// Check if lab component exists
			$stmt = $this->db->prepare("SELECT component_id FROM course_components WHERE course_id = ? AND component_type = 'Laboratory'");
			$stmt->bind_param('i', $course_id);
			$stmt->execute();
			$stmt->store_result();
	
			if ($stmt->num_rows > 0) {
				// Update existing lab component
				$stmt->bind_result($component_id);
				$stmt->fetch();
				$update_component = $this->db->prepare("UPDATE course_components SET hours = ? WHERE component_id = ?");
				$update_component->bind_param('ii', $lab_hours, $component_id);
				$update_component->execute();
			} else {
				// Insert new lab component
				$insert_component = $this->db->prepare("INSERT INTO course_components (course_id, component_type, hours) VALUES (?, 'Laboratory', ?)");
				$insert_component->bind_param('ii', $course_id, $lab_hours);
				$insert_component->execute();
			}
		}
	
		// Validate and insert program associations
		if (isset($_POST['program_ids']) && is_array($_POST['program_ids'])) {
			$selected_program_ids = $_POST['program_ids'];
	
			// Fetch the current program associations for the course
			$current_program_ids = [];
			$stmt = $this->db->prepare("SELECT program_id FROM program_courses WHERE course_id = ?");
			$stmt->bind_param('i', $course_id);
			$stmt->execute();
			$result = $stmt->get_result();
			while ($row = $result->fetch_assoc()) {
				$current_program_ids[] = $row['program_id'];
			}
			$stmt->close();
	
			// Determine programs to delete (those in current_program_ids but not in selected_program_ids)
			$programs_to_remove = array_diff($current_program_ids, $selected_program_ids);
			if (!empty($programs_to_remove)) {
				$placeholders = implode(',', array_fill(0, count($programs_to_remove), '?'));
				$delete_stmt = $this->db->prepare("DELETE FROM program_courses WHERE course_id = ? AND program_id IN ($placeholders)");
				$delete_stmt->bind_param('i' . str_repeat('s', count($programs_to_remove)), $course_id, ...$programs_to_remove);
				$delete_stmt->execute();
				$delete_stmt->close();
			}
	
			// Insert new associations (those in selected_program_ids but not in current_program_ids)
			foreach ($selected_program_ids as $program_id) {
				if (!in_array($program_id, $current_program_ids)) {
					$insert_program = $this->db->prepare("INSERT INTO program_courses (course_id, program_id) VALUES (?, ?)");
					$insert_program->bind_param('is', $course_id, $program_id);
					$insert_program->execute();
					$insert_program->close();
				}
			}
		}
	
		return 1;
	}
	
	
	
	
	
	
	function delete_course(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM courses where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_subject(){
		extract($_POST);
		$data = " subject = '$subject' ";
		$data .= ", description = '$description' ";
			if(empty($id)){
				$save = $this->db->query("INSERT INTO subjects set $data");
			}else{
				$save = $this->db->query("UPDATE subjects set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_subject(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM subjects where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_faculty() {
		extract($_POST);
		$data = '';
		foreach ($_POST as $k => $v) {
			if (!empty($v)) {
				if ($k != 'id' && $k != 'course_ids') { // Ignore 'id' and 'course_ids' for now
					if (empty($data))
						$data .= " $k='{$v}' ";
					else
						$data .= ", $k='{$v}' ";
				}
			}
		}
	
		// Generate ID No if not provided
		if (empty($id_no)) {
			$i = 1;
			while ($i == 1) {
				$rand = mt_rand(1, 99999999);
				$rand = sprintf("%'08d", $rand);
				$chk = $this->db->query("SELECT * FROM faculty WHERE id_no = '$rand'")->num_rows;
				if ($chk <= 0) {
					$data .= ", id_no='$rand' ";
					$i = 0;
				}
			}
		}
	
		// Check for duplicates if ID No is provided
		if (empty($id)) {
			if (!empty($id_no)) {
				$chk = $this->db->query("SELECT * FROM faculty WHERE id_no = '$id_no'")->num_rows;
				if ($chk > 0) {
					return 2;
					exit;
				}
			}
			$save = $this->db->query("INSERT INTO faculty SET $data");
			$faculty_id = $this->db->insert_id;
		} else {
			if (!empty($id_no)) {
				$chk = $this->db->query("SELECT * FROM faculty WHERE id_no = '$id_no' AND id != $id")->num_rows;
				if ($chk > 0) {
					return 2;
					exit;
				}
			}
			$save = $this->db->query("UPDATE faculty SET $data WHERE id=" . $id);
			$faculty_id = $id;
		}
	
		if ($save) {
			// Manage course assignments if course_ids are provided
			$this->db->query("DELETE FROM faculty_courses WHERE faculty_id = $faculty_id"); // Remove old assignments
			if (isset($_POST['course_ids']) && is_array($_POST['course_ids'])) {
				foreach ($_POST['course_ids'] as $course_id) {
					$this->db->query("INSERT INTO faculty_courses (faculty_id, course_id) VALUES ($faculty_id, $course_id)");
				}
			}
			return 1;
		} else {
			return 0;
		}
	}
	
	function delete_faculty(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM faculty where id = ".$id);
		if($delete){
			return 1;
		}
	}
	public function save_schedule() {
		include 'db_connect.php';
		
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$course_id = $_POST['course_id'];
		$faculty_id = $_POST['faculty_id'];
		$room_id = $_POST['room_id'];
		$start_time = $_POST['time_from'];
		$end_time = $_POST['time_to'];
		$day_of_week = $_POST['dow'];
		$semester = $_POST['semester'];
		$academic_year = $_POST['academic_year'];
	
		// Query to check for scheduling conflicts
		$conflict_query = $conn->query("SELECT * FROM schedules WHERE room_id = '$room_id' AND day_of_week = '$day_of_week' AND semester = '$semester' AND academic_year = '$academic_year' AND ('$start_time' < end_time AND '$end_time' > start_time)");
	
		if ($conflict_query->num_rows > 0) {
			return json_encode(array("status" => "error", "message" => "Time conflict detected! The room or faculty is already booked for this time."));
		}
	
		// If no conflicts, proceed with saving
		if (empty($id)) {
			$save_query = "INSERT INTO schedules (course_id, faculty_id, room_id, start_time, end_time, day_of_week, semester, academic_year) VALUES ('$course_id', '$faculty_id', '$room_id', '$start_time', '$end_time', '$day_of_week', '$semester', '$academic_year')";
		} else {
			$save_query = "UPDATE schedules SET course_id = '$course_id', faculty_id = '$faculty_id', room_id = '$room_id', start_time = '$start_time', end_time = '$end_time', day_of_week = '$day_of_week', semester = '$semester', academic_year = '$academic_year' WHERE id = '$id'";
		}
	
		$result = $conn->query($save_query);
		if ($result) {
			return json_encode(array("status" => "success", "message" => "Schedule saved successfully."));
		} else {
			return json_encode(array("status" => "error", "message" => "Failed to save schedule."));
		}
	}
	
	public function delete_section_schedules()
	{
		include 'db_connect.php';
		$section_id = $_POST['section_id'];
	
		if (!$section_id) {
			return json_encode(['success' => false, 'message' => 'Section ID is required.']);
		}
	
		$stmt = $conn->prepare("DELETE FROM schedules WHERE section_id = ?");
		$stmt->bind_param("i", $section_id);
	
		if ($stmt->execute()) {
			return json_encode(['success' => true]);
		} else {
			return json_encode(['success' => false, 'message' => $conn->error]);
		}
	}
	

	public function generate_auto_schedule($conn, $selected_section_id = null) {
		// Query to fetch sections and their academic details
		$sections_query = "SELECT sections.*, generated_reports.semester, generated_reports.academic_year 
						   FROM sections 
						   LEFT JOIN generated_reports ON sections.section_id = generated_reports.section_id";
		if ($selected_section_id) {
			$sections_query .= " WHERE sections.section_id = '$selected_section_id'";
		}
		$sections = $conn->query($sections_query)->fetch_all(MYSQLI_ASSOC);
		
		// Fetch other necessary data
		$rooms = $conn->query("SELECT * FROM rooms")->fetch_all(MYSQLI_ASSOC);
		$professors = $conn->query("SELECT * FROM faculty")->fetch_all(MYSQLI_ASSOC);
		$time_slots = $this->generate_time_slots("07:00 AM", "07:00 PM", 90);
		$days_of_week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
		$section_courses = $this->fetch_section_courses($conn);
		$courses = $this->fetch_courses($conn);
		$course_components = $this->fetch_course_components($conn);
		$qualified_professors = $this->fetch_qualified_professors($conn);
	
		// Fetch all existing schedules to check for conflicts
		$existing_schedules = $conn->query("SELECT * FROM schedules")->fetch_all(MYSQLI_ASSOC);
	
		// Initialize an array to hold the generated schedule for the current section
		foreach ($sections as $section) {
			$schedule = [];  // Reset schedule for each section
			$section_id = $section['section_id'];
	
			// Check if this section has assigned courses
			if (isset($section_courses[$section_id])) {
				foreach ($section_courses[$section_id] as $course_id) {
					if (!isset($course_components[$course_id])) continue;
	
					foreach ($course_components[$course_id] as $component) {
						$component_id = $component['component_id'] ?? null;
						if (is_null($component_id)) continue;
	
						$component_type = $component['component_type'];
						$duration = $component['hours'] * 60;
	
						// Filter professors who are qualified to teach this course
						$available_professors = array_filter($professors, function($prof) use ($course_id, $qualified_professors) {
							return in_array($prof['id'], $qualified_professors[$course_id] ?? []);
						});
	
						// Schedule each component while avoiding conflicts with existing schedules
						$this->schedule_component($schedule, $section, $courses[$course_id], $rooms, $available_professors, $time_slots, $days_of_week, $component_id, $component_type, $duration, $existing_schedules, $course_components);
					}
				}
			}
	
			// Insert the generated schedule entries into the database
			foreach ($schedule as $entry) {
				$stmt = $conn->prepare(
					"INSERT INTO schedules (section_id, course_id, room_id, faculty_id, start_time, end_time, day_of_week, semester, academic_year, component_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
				);
				$stmt->bind_param(
					"iisisssssi", 
					$entry['section_id'],
					$entry['course_id'],
					$entry['room_id'],
					$entry['professor_id'],
					$entry['start_time'],
					$entry['end_time'],
					$entry['day'],
					$entry['semester'],
					$entry['academic_year'],
					$entry['component_id']
				);
	
				if (!$stmt->execute()) {
					error_log("Database Insert Error: " . $stmt->error);
				}
			}
		}
	
		return "success";
	}
	
	
	

	private function fetch_section_courses($conn) {
		$section_courses = [];
		$result = $conn->query("SELECT * FROM section_courses");
		while ($row = $result->fetch_assoc()) {
			$section_courses[$row['section_id']][] = $row['course_id'];
		}
		return $section_courses;
	}

	private function fetch_courses($conn) {
		$courses = [];
		$result = $conn->query("SELECT * FROM courses");
		while ($row = $result->fetch_assoc()) {
			$courses[$row['id']] = $row;
		}
		return $courses;
	}

	private function fetch_course_components($conn) {
		$course_components = [];
		$result = $conn->query("SELECT * FROM course_components");
		while ($row = $result->fetch_assoc()) {
			$course_components[$row['course_id']][] = $row;
		}
		return $course_components;
	}

	private function fetch_qualified_professors($conn) {
		$qualified_professors = [];
		$result = $conn->query("SELECT * FROM faculty_courses");
		while ($row = $result->fetch_assoc()) {
			$qualified_professors[$row['course_id']][] = $row['faculty_id'];
		}
		return $qualified_professors;
	}
	
	
	private function schedule_component(&$schedule, $section, $course, $rooms, $professors, $time_slots, $days_of_week, $component_id, $component_type, $duration, $existing_schedules, $course_components) {
		// Dynamically adjust max scheduled days based on the number of components
		$max_scheduled_days = count($course_components[$course['id']]) > 2 ? 3 : 2;
		$scheduled_days = 0;
	
		$filtered_rooms = $this->get_filtered_rooms($rooms, $component_type);
	
		// Sort days by the number of conflicts (fewer conflicts are prioritized)
		usort($days_of_week, function($a, $b) use ($existing_schedules, $time_slots) {
			$a_count = $this->count_day_conflicts($existing_schedules, $a, $time_slots);
			$b_count = $this->count_day_conflicts($existing_schedules, $b, $time_slots);
			return $a_count - $b_count; // Prioritize days with fewer conflicts
		});
	
		foreach ($days_of_week as $day) {
			if ($scheduled_days >= $max_scheduled_days) break;
	
			foreach ($time_slots as $time_slot) {
				list($start_time, $end_time) = $this->calculate_time_range($time_slot, $duration);
	
				// Sort rooms and professors by conflict count for the specific time slot and day
				usort($filtered_rooms, function($a, $b) use ($existing_schedules, $day, $start_time, $end_time) {
					$a_count = $this->count_conflicts($existing_schedules, $a['room_id'], $day, $start_time, $end_time);
					$b_count = $this->count_conflicts($existing_schedules, $b['room_id'], $day, $start_time, $end_time);
					return $a_count - $b_count;
				});
	
				usort($professors, function($a, $b) use ($existing_schedules, $day, $start_time, $end_time) {
					$a_count = $this->count_conflicts($existing_schedules, $a['id'], $day, $start_time, $end_time, 'professor');
					$b_count = $this->count_conflicts($existing_schedules, $b['id'], $day, $start_time, $end_time, 'professor');
					return $a_count - $b_count;
				});
	
				foreach ($filtered_rooms as $room) {
					foreach ($professors as $professor) {
						if ($this->satisfies_constraints($schedule, $existing_schedules, $section, $course, $start_time, $end_time, $day, $room['room_id'], $professor['id'])) {
							$this->add_schedule_entry($schedule, $section, $course, $room, $professor, $start_time, $end_time, $day, $component_id, $component_type);
							$scheduled_days++;
							break 4;
						}
					}
				}
			}
		}
	
		if ($scheduled_days < $max_scheduled_days) {
			error_log("Could not fully schedule component (ID: $component_id) for section {$section['section_id']}.");
		}
	}

	private function count_day_conflicts($existing_schedules, $day, $time_slots) {
		$count = 0;
		foreach ($existing_schedules as $entry) {
			if ($entry['day_of_week'] === $day) {
				foreach ($time_slots as $time_slot) {
					list($start_time, $end_time) = explode(" - ", $time_slot);
					$start_ts = strtotime($start_time);
					$end_ts = strtotime($end_time);
					$entry_start_ts = strtotime($entry['start_time']);
					$entry_end_ts = strtotime($entry['end_time']);
					if ($start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
						$count++;
					}
				}
			}
		}
		return $count;
	}
	

	private function count_conflicts($existing_schedules, $resource_id, $day, $start_time, $end_time, $type = 'room') {
		$count = 0;
		foreach ($existing_schedules as $entry) {
			if ($entry['day_of_week'] === $day) {
				$entry_start_ts = strtotime($entry['start_time']);
				$entry_end_ts = strtotime($entry['end_time']);
				$start_ts = strtotime($start_time);
				$end_ts = strtotime($end_time);
				
				if ($start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
					if ($type === 'room' && $entry['room_id'] === $resource_id) {
						$count++;
					}
					if ($type === 'professor' && $entry['faculty_id'] === $resource_id) {
						$count++;
					}
				}
			}
		}
		return $count;
	}
	
	// Helper function to filter rooms based on component type
	private function get_filtered_rooms($rooms, $component_type) {
		if ($component_type == 'Laboratory') {
			return array_filter($rooms, fn($room) => stripos($room['room_name'], 'lab') !== false);
		}
		return $rooms;
	}
	
	// Helper function to calculate start and end times based on a time slot and duration
	private function calculate_time_range($time_slot, $duration) {
		$start_time = explode(" - ", $time_slot)[0];
		$end_time = date("H:i", strtotime("+$duration minutes", strtotime($start_time))); // 24-hour format
		return [$start_time, $end_time];
	}

	
// Helper function to add a schedule entry
private function add_schedule_entry(&$schedule, $section, $course, $room, $professor, $start_time, $end_time, $day, $component_id, $component_type) {
    $start_time = date("H:i", strtotime($start_time));
    $end_time = date("H:i", strtotime($end_time));
    
    // Add the entry to the schedule
    $schedule[] = [
        'section_id' => $section['section_id'],
        'course_id' => $course['id'],
        'room_id' => $room['room_id'],
        'professor_id' => $professor['id'],
        'start_time' => $start_time,
        'end_time' => $end_time,
        'day' => $day,
        'semester' => $section['semester'],
        'academic_year' => $section['academic_year'],
        'component_id' => $component_id,
        'component_type' => $component_type
    ];

    // Detailed logging
    error_log("Added schedule entry: Section ID - {$section['section_id']}, Course ID - {$course['id']}, Room ID - {$room['room_id']}, Professor ID - {$professor['id']}, Start Time - $start_time, End Time - $end_time, Day - $day, Semester - {$section['semester']}, Academic Year - {$section['academic_year']}, Component ID - $component_id, Component Type - $component_type");
}

	
	
function satisfies_constraints($schedule, $existing_schedules, $section, $course, $start_time, $end_time, $day, $room, $professor) {
    $start_ts = strtotime($start_time);
    $end_ts = strtotime($end_time);

    // Check in-memory schedule
    foreach ($schedule as $entry) {
        if ($entry['day'] === $day) {
            $entry_start_ts = strtotime($entry['start_time']);
            $entry_end_ts = strtotime($entry['end_time']);

            // Allow adjacent times without conflict
            if ($end_ts == $entry_start_ts || $start_ts == $entry_end_ts) {
                continue; // No conflict if times are adjacent
            }

            // Prevent multiple courses in the same section and time slot
            if ($entry['section_id'] === $section['section_id'] && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Section {$section['section_id']} already has a course from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} overlaps with an existing schedule for the section.");
                return false;
            }

            // Room conflict
            if ($entry['room_id'] === $room && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Room {$room} is already booked from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} cannot be scheduled in Room {$room} due to a conflict.");
                return false;
            }

            // Professor conflict
            if ($entry['professor_id'] === $professor && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Professor {$professor} is already teaching from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} cannot be assigned to Professor {$professor} due to a conflict.");
                return false;
            }
        }
    }

    // Check existing schedules in the database
    foreach ($existing_schedules as $entry) {
        if ($entry['day_of_week'] === $day) {
            $entry_start_ts = strtotime($entry['start_time']);
            $entry_end_ts = strtotime($entry['end_time']);

            // Allow adjacent times without conflict
            if ($end_ts == $entry_start_ts || $start_ts == $entry_end_ts) {
                continue; // No conflict if times are adjacent
            }

            // Section conflict
            if ($entry['section_id'] === $section['section_id'] && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Section {$section['section_id']} in database already has a course from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} overlaps with an existing schedule for the section in the database.");
                return false;
            }

            // Room conflict
            if ($entry['room_id'] === $room && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Room {$room} in database is already booked from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} cannot be scheduled in Room {$room} due to a conflict in the database.");
                return false;
            }

            // Professor conflict
            if ($entry['faculty_id'] === $professor && $start_ts < $entry_end_ts && $end_ts > $entry_start_ts) {
                error_log("Conflict: Professor {$professor} in database is already teaching from {$entry['start_time']} to {$entry['end_time']} on $day.");
                error_log("Scheduling Conflict: Component ID {$course['id']} cannot be assigned to Professor {$professor} due to a conflict in the database.");
                return false;
            }
        }
    }

    // Log successful check
    error_log("No conflicts detected for Component ID {$course['id']} on $day from {$start_time} to {$end_time} in Room {$room} with Professor {$professor}.");
    return true;
}




	
	// Helper function to create time slots
	private function generate_time_slots($start = "07:00", $end = "19:00", $interval = 90) {
		$time_slots = [];
		$current_time = strtotime($start);
		$end_time = strtotime($end);

		while ($current_time + ($interval * 60) <= $end_time) {
			$next_time = strtotime("+$interval minutes", $current_time);
			$time_slots[] = date("H:i", $current_time) . " - " . date("H:i", $next_time); // 24-hour format
			$current_time = $next_time;
		}

		return $time_slots;
	}

	
	// get_schedule function in ajax.php
	public function get_schedule() {
		include 'db_connect.php';
		extract($_POST);
	
		if (!isset($section_id) || empty($section_id)) {
			echo json_encode(['status' => 'error', 'message' => 'Section ID is not provided.']);
			exit();
		}
	
		// Initialize the query with the section filter
		$query = "
			SELECT schedules.*, 
				courses.course_name AS title, 
				CONCAT(faculty.firstname, ' ', faculty.lastname) AS professor, 
				rooms.room_name AS room, 
				course_components.component_type,
				TIME_FORMAT(schedules.start_time, '%H:%i:%s') AS start_time, 
				TIME_FORMAT(schedules.end_time, '%H:%i:%s') AS end_time    
			FROM schedules 
			LEFT JOIN courses ON schedules.course_id = courses.id
			LEFT JOIN faculty ON schedules.faculty_id = faculty.id
			LEFT JOIN rooms ON schedules.room_id = rooms.room_id
			LEFT JOIN course_components ON schedules.component_id = course_components.component_id
			WHERE schedules.section_id = '$section_id'
		";

	
		// If a faculty_id is provided, add it to the WHERE clause to filter by faculty
		if (isset($faculty_id) && !empty($faculty_id)) {
			$query .= " AND schedules.faculty_id = '$faculty_id'";
		}
	
		$qry = $conn->query($query);
	
		if ($qry->num_rows === 0) {
			echo json_encode(["status" => "no_data", "message" => "No schedule available for this section."]);
			exit();
		}
	
		$data = [];
		while ($row = $qry->fetch_assoc()) {
			$data[] = [
				'id' => $row['id'],
				'start_time' => $row['start_time'],
				'end_time' => $row['end_time'],
				'day_of_week' => $row['day_of_week'],
				'component_type' => $row['component_type'],
				'title' => $row['title'],
				'professor' => $row['professor'],
				'room' => $row['room']
			];
		}
	
		echo json_encode(["status" => "success", "data" => $data]);
		exit();
	}
	
	
	
	public function get_schedule_data($section_id) {
		global $conn;
	
		$data = [];
	
		$qry = $conn->query("
			SELECT s.*, 
				c.course_name AS title, 
				cc.component_type,
				TIME_FORMAT(s.start_time, '%h:%i %p') AS start_time,
				TIME_FORMAT(s.end_time, '%h:%i %p') AS end_time,
				s.day_of_week,
				r.room_name AS room, 
				CONCAT(f.firstname, ' ', f.lastname) AS professor
			FROM schedules s
			LEFT JOIN courses c ON s.course_id = c.id
			LEFT JOIN course_components cc ON s.component_id = cc.component_id
			LEFT JOIN rooms r ON s.room_id = r.room_id
			LEFT JOIN faculty f ON s.faculty_id = f.id
			WHERE s.section_id = '$section_id'
		");
	
		if (!$qry) {
			error_log("MySQL Error: " . $conn->error);
			return [];
		}
	
		while ($row = $qry->fetch_assoc()) {
			$data[] = [
				"title" => $row['title'],
				"component_type" => $row['component_type'],
				"start_time" => $row['start_time'],
				"end_time" => $row['end_time'],
				"day_of_week" => $row['day_of_week'],
				"professor" => $row['professor'],
				"room" => $row['room'] ?? 'TBA'
			];
		}
	
		return $data;
	}
	
	
	// Converts 12-hour time format to 24-hour format (e.g., "07:00 PM" to "19:00:00")
	public function convertTo24HourFormat($time12h) {
		return date("H:i:s", strtotime($time12h));
	}

	// Converts 24-hour time format to 12-hour format (e.g., "19:00:00" to "07:00 PM")
	public function convertTo12HourFormat($time24h) {
		return date("h:i A", strtotime($time24h));
	}

	public function update_schedule_details($id, $title, $start, $end, $room_name, $professor) {
		include 'db_connect.php';
	
		error_log("Updating Event ID: $id | Title: $title | Start: $start | End: $end | Room: $room_name | Professor: $professor");
	
		// Extract day of the week, start time, and end time
		$day_of_week = date('l', strtotime($start)); // Extract day from the 'start' parameter
		$start_time = date('H:i', strtotime($start));
		$end_time = date('H:i', strtotime($end));
	
		// Clean the course title
		$clean_title = preg_replace('/\n.*/', '', $title); // Remove everything after a newline
		$clean_title = preg_replace('/\s*\-.*$/', '', $clean_title); // Remove everything after a dash
	
		// Fetch all professors' names dynamically from the database
		$professorsQuery = "SELECT CONCAT(firstname, ' ', lastname) AS full_name FROM faculty";
		$professorsResult = $conn->query($professorsQuery);
	
		if ($professorsResult) {
			while ($professorRow = $professorsResult->fetch_assoc()) {
				$professorName = $professorRow['full_name'];
				// Dynamically remove any professor name found in the title
				$clean_title = str_replace($professorName, '', $clean_title);
			}
		}
	
		// Remove any remaining parentheses (e.g., "(Lecture)")
		$clean_title = preg_replace('/\s*\(.*?\)/', '', $clean_title);
	
		// Trim any leading or trailing spaces
		$clean_title = trim($clean_title);
	
		error_log("Cleaned course name: '$clean_title'");
	
		// Fetch course ID
		$courseQuery = "SELECT id FROM courses WHERE course_name = ?";
		$courseStmt = $conn->prepare($courseQuery);
		$courseStmt->bind_param('s', $clean_title);
		$courseStmt->execute();
		$courseStmt->store_result();
	
		if ($courseStmt->num_rows === 0) {
			error_log("Course '$clean_title' not found in courses table.");
			return false;
		}
		$courseStmt->bind_result($course_id);
		$courseStmt->fetch();
	
		// Fetch professor ID
		$professorQuery = "SELECT id FROM faculty WHERE CONCAT(firstname, ' ', lastname) = ?";
		$professorStmt = $conn->prepare($professorQuery);
		$professorStmt->bind_param('s', $professor);
		$professorStmt->execute();
		$professorStmt->store_result();
	
		if ($professorStmt->num_rows === 0) {
			error_log("Professor '$professor' not found in faculty table.");
			return false;
		}
		$professorStmt->bind_result($professor_id);
		$professorStmt->fetch();

		// Fetch room ID using room_name
		$roomQuery = "SELECT room_id FROM rooms WHERE room_name = ?";
		$roomStmt = $conn->prepare($roomQuery);
		$roomStmt->bind_param('s', $room_name); // Use room_name as the input parameter
		$roomStmt->execute();
		$roomStmt->store_result();

		if ($roomStmt->num_rows === 0) {
			error_log("Room '$room_name' not found in rooms table.");
			return false;
		}
		$roomStmt->bind_result($room_id);
		$roomStmt->fetch();
	
		// Update the schedule in the database
		$updateQuery = "UPDATE schedules SET 
			course_id = ?, 
			start_time = ?, 
			end_time = ?, 
			day_of_week = ?, 
			room_id = ?, 
			faculty_id = ? 
		WHERE id = ?";
		$updateStmt = $conn->prepare($updateQuery);
		$updateStmt->bind_param('issssii', $course_id, $start_time, $end_time, $day_of_week, $room_id, $professor_id, $id);
	
	
		if ($updateStmt->execute()) {
			if ($updateStmt->affected_rows > 0) {
				error_log("Schedule updated successfully for ID $id.");
				return true;
			} else {
				error_log("No rows affected while updating schedule for ID $id.");
				return false;
			}
		} else {
			error_log("SQL Error: " . $conn->error);
			return false;
		}
	}
	
	
	
	
}