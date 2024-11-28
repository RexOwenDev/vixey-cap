<!DOCTYPE html>
<html>
<head>
    <title>Generated Reports</title>
    <!-- Include DataTables CSS and jQuery -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    
    <style>
        /* Custom modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .btn {
            padding: 5px 10px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Manage Generated Reports</b>
                    </div>
                    <div class="card-body">
                        <table id="reports-list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">Program</th>
                                    <th class="text-center">Section</th>
                                    <th class="text-center">Academic Year</th>
                                    <th class="text-center">Semester</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Reference Number</th>
                                    <th class="text-center">Total Units</th>
                                    <th class="text-center">Created At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data dynamically fetched here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Report Details</h2>
        <div id="reportContent">
            <!-- Report details will be injected here -->
        </div>
        <h3>Courses in this Report</h3>
        <table id="courses-table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Units</th>
                </tr>
            </thead>
            <tbody id="courses-list">
                <!-- Rows dynamically appended -->
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize main reports table
        $('#reports-list').DataTable();

        // Fetch the reports via AJAX
        $.ajax({
            url: 'get_reports.php',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                data.forEach(report => {
                    $('#reports-list tbody').append(`
                        <tr>
                            <td class="text-center">${report.program_name}</td>
                            <td class="text-center">${report.section_name}</td>
                            <td class="text-center">${report.academic_year}</td>
                            <td class="text-center">${report.semester}</td>
                            <td class="text-center">${report.year}</td>
                            <td class="text-center">${report.reference_number}</td>
                            <td class="text-center">${report.total_units}</td>
                            <td class="text-center">${report.created_at}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary view-report" type="button" data-id="${report.report_id}">View</button>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error fetching reports:", error);
            }
        });

        // Handle the View button click
        $(document).on('click', '.view-report', function () {
            var reportId = $(this).data('id');

            // Fetch the report details
            $.ajax({
                url: 'view_report.php',
                method: 'GET',
                data: { id: reportId },
                success: function (response) {
                    var reportData = JSON.parse(response);

                    if (reportData.error) {
                        alert(reportData.error);
                        return;
                    }

                    // Populate modal with report details
                    $('#reportContent').html(`
                        <p><strong>Program:</strong> ${reportData.program_name}</p>
                        <p><strong>Academic Year:</strong> ${reportData.academic_year}</p>
                        <p><strong>Semester:</strong> ${reportData.semester}</p>
                        <p><strong>Year:</strong> ${reportData.year}</p>
                        <p><strong>Total Units:</strong> ${reportData.total_units}</p>
                        <p><strong>Section:</strong> ${reportData.section_name}</p>
                    `);

                    // Populate courses in the modal table
                    $('#courses-list').empty();
                    reportData.courses.forEach(course => {
                        $('#courses-list').append(`
                            <tr>
                                <td>${course.course_code}</td>
                                <td>${course.course_name}</td>
                                <td>${course.units}</td>
                            </tr>
                        `);
                    });

                    // Reinitialize DataTable for the modal table with features disabled
                    $('#courses-table').DataTable({
                        paging: false, // Disable pagination
                        searching: false, // Disable search
                        info: false, // Disable info
                        ordering: false, // Disable ordering
                        destroy: true, // Allow reinitialization
                    });

                    // Show modal
                    $('#reportModal').fadeIn();
                },
                error: function (xhr, status, error) {
                    alert("Error fetching report details: " + error);
                }
            });
        });

        // Close modal functionality
        $('.close').click(function () {
            $('#reportModal').fadeOut();
        });

        $(window).click(function (event) {
            if ($(event.target).is('#reportModal')) {
                $('#reportModal').fadeOut();
            }
        });
    });
</script>
</body>
</html>
