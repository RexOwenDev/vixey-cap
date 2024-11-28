<?php
include('db_connect.php');
$programs = $conn->query("SELECT program_id, program_name FROM programs");

// Function to format the academic year for display purposes
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
?>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
<!-- Select2 CSS (if used) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (load first) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<!-- Select2 JS (if used) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-selection--multiple {
        height: auto !important;
        padding: 6px 12px;  /* Adjust padding for alignment */
        border: 1px solid #ced4da;  /* Consistent with other input elements */
        border-radius: 0.25rem;
        min-height: 38px;  /* Ensure a minimum height */
    }

    .select2-selection__rendered {
        max-height: 150px;  /* Limit height to prevent overlapping */
        overflow-y: auto;   /* Scroll if too many items */
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .modal-body {
        overflow-y: auto;
    }

    /* Ensure that the Select2 container grows with the content */
    .select2-container .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
    }

    /* New CSS for aligning buttons */
    .button-container {
        display: flex;
        gap: 10px; /* Space between the buttons */
        justify-content: flex-end; /* Align buttons to the right */
    }

    .button-container .btn {
        width: auto; /* Ensure buttons have automatic width */
    }
</style>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
                <div class="button-container">
                    <button class="btn btn-secondary btn-sm" type="button" id="generate_report">
                        <i class="fa fa-file"></i> Generate Report
                    </button>
                    <button class="btn btn-primary btn-sm" type="button" id="new_course">
                        <i class="fa fa-plus"></i> New Course
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Table Panel -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Manage Courses</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="courses-table">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Course Code</th>
                                    <th class="text-center">Course Name</th>
                                    <th class="text-center">Units</th>
                                    <th class="text-center">Semester</th>
                                    <th class="text-center">Program</th>
                                    <th class="text-center">Academic Year</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                $course = $conn->query("
                                    SELECT c.*, 
                                           GROUP_CONCAT(p.program_name SEPARATOR ', ') as program_names,
                                           GROUP_CONCAT(p.program_id) as program_ids
                                    FROM courses c
                                    LEFT JOIN program_courses pc ON pc.course_id = c.id
                                    LEFT JOIN programs p ON p.program_id = pc.program_id
                                    GROUP BY c.id
                                    ORDER BY c.id ASC
                                ");
                                while($row = $course->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td class="text-center"><?php echo $row['course_code'] ?></td>
                                    <td class="text-center"><?php echo $row['course_name'] ?></td>
                                    <td class="text-center"><?php echo $row['units'] ?></td>
                                    <td class="text-center"><?php echo $row['semester'] ?></td>
                                    <td class="text-center"><?php echo $row['program_names'] ?></td>
                                    <td class="text-center"><?php echo formatAcademicYear($row['academic_year']) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit_course" 
                                            type="button" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-course_code="<?php echo $row['course_code']; ?>" 
                                            data-course_name="<?php echo $row['course_name']; ?>" 
                                            data-units="<?php echo $row['units']; ?>" 
                                            data-semester="<?php echo $row['semester']; ?>" 
                                            data-program="<?php echo $row['program_ids']; ?>"
                                            data-academic_year="<?php echo $row['academic_year']; ?>"
                                            data-is_lecture="<?php echo $row['is_lecture']; ?>" 
                                            data-is_lab="<?php echo $row['is_lab']; ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete_course" 
                                            type="button" 
                                            data-id="<?php echo $row['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Table Panel -->
        </div>
    </div>  
</div>


<!-- Modal for Course Form -->
<div class="modal fade" id="courseModal" tabindex="-1" role="dialog" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">New Course</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
    <form id="manage-course">
        <input type="hidden" name="id">
        <div class="form-group">
            <label class="control-label">Course Code</label>
            <input type="text" class="form-control" name="course_code" required>
        </div>
        <div class="form-group">
            <label class="control-label">Course Name</label>
            <input type="text" class="form-control" name="course_name" required>
        </div>
        <div class="form-group">
            <label class="control-label">Units</label>
            <input type="number" class="form-control" name="units" required>
        </div>
        <div class="form-group">
            <label class="control-label">Semester</label>
            <select class="form-control" name="semester" required>
                <option value="" disabled selected>Select Semester</option>
                <option value="First Semester">First Semester</option>
                <option value="Second Semester">Second Semester</option>
            </select>
        </div>
        <div class="form-group">
            <label for="program">Programs</label>
            <select class="form-control select2" id="program" name="program_ids[]" multiple>
                <option value="" disabled>Select Programs</option>
                <?php
                $programs = $conn->query("SELECT program_id, program_name FROM programs");
                while ($row = $programs->fetch_assoc()):
                ?>
                    <option value="<?php echo $row['program_id']; ?>"><?php echo $row['program_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="control-label">Academic Year</label>
            <select class="form-control" name="academic_year" required>
                <option value="" disabled selected>Select Year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
        </div>

        <!-- New Lecture and Lab Fields -->
        <div class="form-group">
            <label class="control-label">Lecture</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_lecture" id="is_lecture">
                <label class="form-check-label" for="is_lecture">Has Lecture Component</label>
            </div>
            <div id="lecture_details" class="mt-3" style="display: none;">
                <label class="control-label">Lecture Hours</label>
                <input type="number" class="form-control" name="lecture_hours">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">Lab</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_lab" id="is_lab">
                <label class="form-check-label" for="is_lab">Has Lab Component</label>
            </div>
            <div id="lab_details" class="mt-3" style="display: none;">
                <label class="control-label">Lab Hours</label>
                <input type="number" class="form-control" name="lab_hours">
            </div>
        </div>
    </form>
</div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save_course">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Report Filter -->
<div class="modal fade" id="reportFilterModal" tabindex="-1" role="dialog" aria-labelledby="reportFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportFilterModalLabel">Select Report Filters</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="report-filters">
                    <div class="form-group">
                        <label for="program_filter">Programs</label>
                        <select class="form-control" id="program_filter" name="program_id">
                            <option value="" disabled selected>Select Program</option>
                            <?php
                            $programs = $conn->query("SELECT program_id, program_name FROM programs");
                            while ($row = $programs->fetch_assoc()):
                            ?>
                                <option value="<?php echo $row['program_id']; ?>"><?php echo $row['program_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <select class="form-control" id="academic_year_filter" name="academic_year">
                            <option value="" disabled selected>Select Academic Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select class="form-control" id="semester_filter" name="semester">
                            <option value="" disabled selected>Select Semester</option>
                            <option value="First Semester">First Semester</option>
                            <option value="Second Semester">Second Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <select class="form-control" id="year_filter" name="year">
                            <option value="" disabled selected>Select Year</option>
                            <?php
                            $current_year = date('Y');
                            for ($y = $current_year - 10; $y <= $current_year + 10; $y++) {
                                echo "<option value='$y'>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="apply_filters">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Generated Course Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportContent">
                <!-- Report content will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {

            $('#courses-table').DataTable({
            pageLength: 10,
            lengthChange: true,
            searching: true,
        });
    // Initialize Select2 for the course creation form with multi-selection enabled
    $('#program').select2({
        placeholder: "Select Programs",
        allowClear: true,
        width: '100%'  // Ensure it fills the width of the container
    });

    // Initialize the report modal dropdown without Select2, making it a regular dropdown
    $('#program_filter').select2('destroy');  // Remove any existing Select2 initialization if it exists

    // Show/hide lecture details when checkbox is clicked
    $('#is_lecture').change(function() {
            if ($(this).is(':checked')) {
                $('#lecture_details').show();
            } else {
                $('#lecture_details').hide();
                $('#lecture_details input[name="lecture_hours"]').val(''); // Clear input if unchecked
            }
        });

        // Show/hide lab details when checkbox is clicked
        $('#is_lab').change(function() {
            if ($(this).is(':checked')) {
                $('#lab_details').show();
            } else {
                $('#lab_details').hide();
                $('#lab_details input[name="lab_hours"]').val(''); // Clear input if unchecked
            }
        });

        // Load the existing values in case of edit
        $('#courseModal').on('show.bs.modal', function() {
            if ($('#is_lecture').is(':checked')) {
                $('#lecture_details').show();
            }
            if ($('#is_lab').is(':checked')) {
                $('#lab_details').show();
            }
        });

    // Initialize DataTable for the courses table
    $('#courses-table').DataTable();
});


    function _reset(){
        $('#manage-course').get(0).reset();
        $('#manage-course input').val('');
    }

    $('#new_course').click(function() {
    $('#courseModal').modal('show');
    $('#courseModalLabel').text('New Course');
    _reset();
    // Set default values for lecture and lab hours
    $('#is_lecture').prop('checked', true);
    $('#lecture_details').show();
    $('#lecture_details input[name="lecture_hours"]').val(2);
    $('#is_lab').prop('checked', true);
    $('#lab_details').show();
    $('#lab_details input[name="lab_hours"]').val(3);
});


     // Save course data through AJAX
     $('#save_course').click(function() {
            let formData = new FormData($('#manage-course')[0]);
            
            // Add the unchecked checkboxes manually since FormData ignores them
            if (!$('#is_lecture').is(':checked')) {
                formData.append('is_lecture', 0);
            }
            if (!$('#is_lab').is(':checked')) {
                formData.append('is_lab', 0);
            }

            start_load();
            $.ajax({
                url: 'ajax.php?action=save_course',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                success: function(resp) {
                    if (resp == 1) {
                        alert_toast("Course successfully added", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else if (resp == 2) {
                        alert_toast("Course successfully updated", 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_toast("Error: " + resp, 'error');
                    }
                }
            });
        });

        $('.edit_course').click(function() {
    start_load();
    $('#courseModal').modal('show');
    $('#courseModalLabel').text('Edit Course');

    var form = $('#manage-course');
    form.get(0).reset();
    form.find("[name='id']").val($(this).attr('data-id'));
    form.find("[name='course_code']").val($(this).attr('data-course_code'));
    form.find("[name='course_name']").val($(this).attr('data-course_name'));
    form.find("[name='units']").val($(this).attr('data-units'));
    form.find("[name='semester']").val($(this).attr('data-semester'));
    form.find("[name='academic_year']").val($(this).attr('data-academic_year'));

    // Set the program IDs in the select2 field
    var program_ids = $(this).attr('data-program').split(',');
    form.find("#program").val(program_ids).trigger('change');

    // Set the lecture and lab checkboxes based on the data attributes
    var isLecture = $(this).attr('data-is_lecture');
    var isLab = $(this).attr('data-is_lab');
    
    form.find("[name='is_lecture']").prop('checked', isLecture == '1');
    form.find("[name='is_lab']").prop('checked', isLab == '1');

    end_load();
});


    $('#generate_report').click(function() {
        $('#reportFilterModal').modal('show');
    });

    $('#apply_filters').click(function() {
        var program = $('#program_filter').val();
        var academic_year = $('#academic_year_filter').val();
        var semester = $('#semester_filter').val();
        var year = $('#year_filter').val();

        if (program && academic_year && semester && year) {
            $('#reportFilterModal').modal('hide');

            $.ajax({
                url: 'generate_report_content.php',
                method: 'GET',
                data: {
                    program: program,
                    academic_year: academic_year,
                    semester: semester,
                    year: year
                },
                success: function(response) {
                    $('#reportContent').html(response);
                    $('#reportModal').modal('show');
                },
                error: function() {
                    alert('An error occurred while generating the report.');
                }
            });
        } else {
            alert('Please select all filters before generating the report.');
        }
    });

    $('.delete_course').click(function(){
        _conf("Are you sure to delete this course?", "delete_course", [$(this).attr('data-id')]);
    });

    function delete_course(id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_course',
            method: 'POST',
            data: {id: id},
            success: function(resp){
                if (resp == 1) {
                    alert_toast("Course successfully deleted", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            }
        });
    }

    
</script>