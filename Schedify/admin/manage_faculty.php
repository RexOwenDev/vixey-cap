<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
    // Fetch existing faculty data
    $qry = $conn->query("SELECT * FROM faculty WHERE id=" . $_GET['id'])->fetch_array();
    foreach ($qry as $k => $v) {
        $$k = $v;
    }
    // Fetch courses assigned to this faculty member
    $assigned_courses_qry = $conn->query("SELECT course_id FROM faculty_courses WHERE faculty_id=" . $_GET['id']);
    $assigned_courses = [];
    while ($row = $assigned_courses_qry->fetch_assoc()) {
        $assigned_courses[] = $row['course_id'];
    }
}

// Fetch all courses for selection
$courses = $conn->query("SELECT * FROM courses");
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div class="container-fluid">
    <form action="" id="manage-faculty">
        <div id="msg"></div>
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>" class="form-control">
        
        <!-- Faculty Information -->
        <div class="row form-group">
            <div class="col-md-4">
                <label class="control-label">ID No.</label>
                <input type="text" name="id_no" class="form-control" value="<?php echo isset($id_no) ? $id_no : '' ?>">
                <small><i>Leave this blank if you want to auto-generate ID no.</i></small>
            </div>
        </div>
        
        <div class="row form-group">
            <div class="col-md-4">
                <label class="control-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" value="<?php echo isset($lastname) ? $lastname : '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="control-label">First Name</label>
                <input type="text" name="firstname" class="form-control" value="<?php echo isset($firstname) ? $firstname : '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="control-label">Middle Name</label>
                <input type="text" name="middlename" class="form-control" value="<?php echo isset($middlename) ? $middlename : '' ?>">
            </div>
        </div>
        
        <div class="row form-group">
            <div class="col-md-4">
                <label class="control-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo isset($email) ? $email : '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="control-label">Contact #</label>
                <input type="text" name="contact" class="form-control" value="<?php echo isset($contact) ? $contact : '' ?>" required>
            </div>
            <div class="col-md-4">
                <label class="control-label">Gender</label>
                <select name="gender" required="" class="custom-select">
                    <option value="Male" <?php echo isset($gender) && $gender == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?php echo isset($gender) && $gender == 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
        </div>
        
        <div class="row form-group">
            <div class="col-md-12">
                <label class="control-label">Address</label>
                <textarea name="address" class="form-control"><?php echo isset($address) ? $address : '' ?></textarea>
            </div>
        </div>
        
        <!-- Assign Courses -->
        <div class="row form-group">
            <div class="col-md-12">
                <label class="control-label">Assign Courses:</label>
                <select name="course_ids[]" class="form-control select2" multiple style="width: 100%;">
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['id'] ?>" <?php echo isset($assigned_courses) && in_array($course['id'], $assigned_courses) ? 'selected' : '' ?>>
                            <?php echo $course['course_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>

<script>
    // Initialize Select2 on the dropdown with full width
    $('.select2').select2({
        placeholder: "Select courses",
        width: '100%'  // Set width to 100% for proper display
    });

    $('#manage-faculty').submit(function(e) {
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_faculty',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully saved.", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else if (resp == 2) {
                    $('#msg').html('<div class="alert alert-danger">ID No already exists.</div>');
                    end_load();
                } else {
                    $('#msg').html('<div class="alert alert-danger">An error occurred while saving data.</div>');
                    end_load();
                }
            },
            error: function() {
                $('#msg').html('<div class="alert alert-danger">An error occurred while communicating with the server.</div>');
                end_load();
            }
        });
    });
</script>
