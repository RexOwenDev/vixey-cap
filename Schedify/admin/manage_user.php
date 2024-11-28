<?php 
include('db_connect.php');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied. Admins only.</div>";
    exit;
}

// Fetch programs for the dropdown
$programs = $conn->query("SELECT program_id, program_name FROM programs");

// Fetch faculty members for the dropdown
$faculty_list = $conn->query("SELECT id, concat(firstname, ' ', lastname) as name FROM faculty");

// If editing an existing user, fetch their details
if(isset($_GET['id'])){
    $user = $conn->query("SELECT * FROM users WHERE id =".$_GET['id']);
    foreach($user->fetch_array() as $k =>$v){
        $meta[$k] = $v;
    }
}
?>
<div class="container-fluid">
    <div id="msg"></div>
    
    <form action="" id="manage-user">    
        <input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id']: '' ?>">
        
        <!-- Faculty Member (Used as Name) -->
        <div class="form-group">
            <label for="faculty_id">Faculty Member</label>
            <select name="faculty_id" id="faculty_id" class="custom-select" required>
                <option value="">-- Select Faculty Member --</option>
                <?php while($row = $faculty_list->fetch_assoc()): ?>
                    <option value="<?php echo $row['id'] ?>" <?php echo isset($meta['faculty_id']) && $meta['faculty_id'] == $row['id'] ? 'selected' : '' ?>>
                        <?php echo $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Username -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo isset($meta['username']) ? $meta['username']: '' ?>" required autocomplete="off">
        </div>
        
        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" value="" autocomplete="off">
            <?php if(isset($meta['id'])): ?>
            <small><i>Leave this blank if you don't want to change the password.</i></small>
            <?php endif; ?>
        </div>

        <!-- Role Selection -->
        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="custom-select">
                <option value="admin" <?php echo isset($meta['role']) && $meta['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="program_head" <?php echo isset($meta['role']) && $meta['role'] == 'program_head' ? 'selected' : '' ?>>Program Head</option>
                <option value="faculty" <?php echo isset($meta['role']) && $meta['role'] == 'faculty' ? 'selected' : '' ?>>Faculty</option>
                <option value="student" <?php echo isset($meta['role']) && $meta['role'] == 'student' ? 'selected' : '' ?>>Student</option>
            </select>
        </div>

        <!-- Program Selection (only for Program Head and Faculty roles) -->
        <div class="form-group" id="program-group" style="display: none;">
            <label for="program_id">Program</label>
            <select name="program_id" id="program_id" class="custom-select">
                <option value="">-- Select Program --</option>
                <?php while($row = $programs->fetch_assoc()): ?>
                    <option value="<?php echo $row['program_id'] ?>" <?php echo isset($meta['program_id']) && $meta['program_id'] == $row['program_id'] ? 'selected' : '' ?>>
                        <?php echo $row['program_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>

<script>
    // Show/Hide program selection based on role
    $('#role').change(function(){
        if($(this).val() == 'program_head' || $(this).val() == 'faculty'){
            $('#program-group').show();
        } else {
            $('#program-group').hide();
        }
    }).trigger('change'); // Trigger change to set initial visibility on page load
    
    // AJAX submission for the form
    $('#manage-user').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_user',
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp){
                if(resp == 1){
                    alert_toast("Data successfully saved", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    $('#msg').html('<div class="alert alert-danger">Username already exists</div>');
                    end_load();
                }
            }
        });
    });
</script>
