<?php include 'db_connect.php' ?>
<?php
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT *, concat(lastname, ', ', firstname, ' ', middlename) as name FROM faculty WHERE id=" . $_GET['id']);
    
    if ($qry->num_rows > 0) {
        $faculty = $qry->fetch_array();
        foreach ($faculty as $k => $v) {
            $$k = $v;
        }

        // Fetch assigned courses
        $courses_qry = $conn->query("SELECT course_name FROM courses 
                                     INNER JOIN faculty_courses ON courses.id = faculty_courses.course_id 
                                     WHERE faculty_courses.faculty_id = " . $_GET['id']);
        $assigned_courses = [];
        while ($row = $courses_qry->fetch_assoc()) {
            $assigned_courses[] = $row['course_name'];
        }
    } else {
        echo "<p>Error: Faculty not found.</p>";
        exit;
    }
}
?>
<div class="container-fluid">
    <p>Name: <b><?php echo ucwords($name) ?></b></p>
    <p>Gender: <b><?php echo ucwords($gender) ?></b></p>
    <p>Email: <b><?php echo $email ?></b></p>
    <p>Contact: <b><?php echo $contact ?></b></p>
    <p>Address: <b><?php echo $address ?></b></p>
    <hr class="divider">
    <p><b>Assigned Courses:</b></p>
    <ul>
        <?php if (!empty($assigned_courses)): ?>
            <?php foreach ($assigned_courses as $course): ?>
                <li><?php echo $course; ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <p><i>No courses assigned.</i></p>
        <?php endif; ?>
    </ul>
</div>
<div class="modal-footer display">
    <div class="row">
        <div class="col-md-12">
            <button class="btn float-right btn-secondary" type="button" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
<style>
    p {
        margin: unset;
    }
    #uni_modal .modal-footer {
        display: none;
    }
    #uni_modal .modal-footer.display {
        display: block;
    }
    ul {
        margin-left: 20px;
    }
</style>
