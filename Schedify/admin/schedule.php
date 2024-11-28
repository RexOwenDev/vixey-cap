<?php include('db_connect.php'); ?>
<!-- CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css" rel="stylesheet" />

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script> <!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.min.js"></script> <!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js"></script>


<div class="container-fluid">
    <div class="row mb-4 mt-4">
        <div class="col-md-12">
            <h4>Weekly Schedule</h4>
            <div class="row">
                <label for="section_id" class="control-label col-md-2">View Schedule of:</label>
                <div class="col-md-4">
                    <select name="section_id" id="section_id" class="custom-select select2">
                        <option value="" disabled selected>Choose Section</option>
                        <?php 
                            $sections = $conn->query("SELECT * FROM sections ORDER BY section_name ASC");
                            while ($row = $sections->fetch_array()):
                        ?>
                            <option value="<?php echo $row['section_id']; ?>"><?php echo $row['section_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="toggle_view" class="btn btn-secondary">Switch to Card View</button>
                    <button id="delete_section_schedules" class="btn btn-danger">Delete All Schedules</button>
                    <button id="saveChanges" class="btn btn-primary">Save Changes</button>
                    <button id="export_to_pdf" class="btn btn-success">Export to PDF</button>

                </div>
            </div>
        </div>
    </div>
    <div id='calendar_view'>
        <div id='calendar'></div>
    </div>
    <div id='card_view' style='display:none;'>
        <div class="container-fluid">
            <div class="row" id="card-container">
                <!-- Cards will be dynamically generated here -->
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <button id="auto_generate_schedule" class="btn btn-primary">Generate Schedule Automatically</button>
    </div>
</div>

<!-- Event Editing Modal -->
<div class="modal fade" id="eventEditModal" tabindex="-1" role="dialog" aria-labelledby="eventEditModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventEditModalLabel">Edit Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editEventForm">
                    <input type="hidden" id="eventId" name="id" />
                    <div class="form-group">
                        <label for="eventTitle">Title</label>
                        <input type="text" class="form-control" id="eventTitle" name="title" required />
                    </div>
                    <div class="form-group">
                        <label for="eventStart">Start Time</label>
                        <input type="time" class="form-control" id="eventStart" name="start" readonly />
                    </div>
                    <div class="form-group">
                        <label for="eventEnd">End Time</label>
                        <input type="time" class="form-control" id="eventEnd" name="end" readonly />
                    </div>
                    <div class="form-group">
                        <label for="eventRoom">Room</label>
                        <select class="form-control" id="eventRoom" name="room">
                            <option value="" disabled selected>Select a room</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventProfessor">Professor</label>
                        <select class="form-control" id="eventProfessor" name="professor">
                            <option value="" disabled selected>Select a professor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eventDayOfWeek">Day of the Week</label>
                        <input type="text" class="form-control" id="eventDayOfWeek" readonly />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="saveEventChanges" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>




<style>
    #calendar {
        max-width: 90%;
        margin: auto;
    }
    .schedule-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px;
        margin: 10px;
        width: calc(33.333% - 20px);
    }
    .schedule-card h5 {
        background-color: #343a40;
        color: #fff;
        padding: 10px;
        border-radius: 5px;
    }
    .schedule-card small {
        display: block;
        margin-top: 5px;
    }

    
</style>

<script>
    $(document).ready(function() {
        // Disable generate button initially
        $('#auto_generate_schedule').prop('disabled', true);

        const colorPool = [
        "#FF5733", "#33FF57", "#3357FF", "#FF33A1", "#A1FF33", "#33A1FF",
        "#F0A500", "#00F0A5", "#A500F0", "#F08080", "#20B2AA", "#778899"
    ];
    let colorIndex = 0;
    const courseColors = {};

    // Check if a selected section is stored in localStorage
    const savedSection = localStorage.getItem('selectedSection');
    const reloadSchedule = localStorage.getItem('reloadSchedule');

    if (savedSection) {
        // Set the saved section as the selected value
        $('#section_id').val(savedSection).change();

        // If reloadSchedule is true, load the schedule automatically
        if (reloadSchedule === 'true') {
            loadWeeklySchedule(savedSection);

            // Clear the reload flag to avoid unnecessary reloads
            localStorage.removeItem('reloadSchedule');
        }

        // Clear the stored section ID to avoid retaining it unnecessarily
        localStorage.removeItem('selectedSection');
    }

    $('#saveChanges').click(function () {
    if (modifiedEvents.length === 0) {
        alert('No changes to save.');
        return;
    }

    $.ajax({
        url: 'ajax.php?action=update_schedule_bulk',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ events: modifiedEvents }),
        success: function (response) {
            console.log('Raw Response:', response); // Log raw response for debugging
            console.log('Response Type:', typeof response); // Log the type of response

            try {
                // Handle both parsed and stringified JSON
                const result = typeof response === 'string' ? JSON.parse(response) : response;

                if (result.success) {
                    alert('Schedules updated successfully!');
                    modifiedEvents.length = 0; // Clear the modified events array
                    calendar.refetchEvents(); // Refresh the calendar
                } else {
                    alert('Error: ' + (result.message || 'Unknown error occurred.'));
                }
            } catch (e) {
                console.error('Error parsing response:', e); // Log parsing errors
                alert('Invalid server response. Please check your backend.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error); // Log AJAX errors
            alert('An error occurred while saving changes.');
        }
    });
});





    function getColorForCourse(courseTitle) {
        // Check if the course already has an assigned color
        if (!courseColors[courseTitle]) {
            // Assign a new color from the pool
            courseColors[courseTitle] = colorPool[colorIndex % colorPool.length];
            colorIndex++;
        }
        return courseColors[courseTitle];
    }

    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error("calendarEl is not defined or missing. Ensure there is a <div id='calendar'></div> element in your HTML.");
        return; // Stop initialization if the element is missing
    }

        // Initialize FullCalendar with a restricted time range
        const modifiedEvents = []; // Array to store modified events

var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    slotMinTime: '07:00:00',
    slotMaxTime: '20:00:00',
    editable: true, // Enable drag-and-drop
    eventResizableFromStart: true, // Allow resizing events

    // Event Drop (Drag-and-Drop) Handler
    eventDrop: function (info) {
    const eventData = {
        id: parseInt(info.event.id, 10),
        title: info.event.title,
        start: moment(info.event.start).format("YYYY-MM-DD HH:mm:ss"),
        end: moment(info.event.end).format("YYYY-MM-DD HH:mm:ss"),
        room: info.event.extendedProps.room,
        professor: info.event.extendedProps.professor.trim(),
        day_of_week: moment(info.event.start).format('dddd') // Send day_of_week
    };

    const existingIndex = modifiedEvents.findIndex(event => event.id === info.event.id);
    if (existingIndex !== -1) {
        modifiedEvents[existingIndex] = eventData;
    } else {
        modifiedEvents.push(eventData);
    }

    console.log("Modified Events:", modifiedEvents);
    alert("Event moved! Click 'Save Changes' to confirm.");
},

eventResize: function (info) {
    const eventData = {
        id: parseInt(info.event.id, 10),
        title: info.event.title,
        start: moment(info.event.start).format("YYYY-MM-DD HH:mm:ss"),
        end: moment(info.event.end).format("YYYY-MM-DD HH:mm:ss"),
        room: info.event.extendedProps.room, // Use room instead of room_id
        professor: info.event.extendedProps.professor.trim(),
    };

    const existingIndex = modifiedEvents.findIndex(event => event.id === info.event.id);
    if (existingIndex !== -1) {
        modifiedEvents[existingIndex] = eventData;
    } else {
        modifiedEvents.push(eventData);
    }

    console.log("Modified Events (After Resize):", modifiedEvents);
    alert("Event resized! Click 'Save Changes' to confirm.");
},




    // Event Click Handler for Editing
    eventClick: function(info) {
    const startTime = moment(info.event.start).format('HH:mm');
    const endTime = moment(info.event.end).format('HH:mm');
    const dayOfWeek = moment(info.event.start).format('dddd'); // Extract day of week

    $('#eventId').val(info.event.id);
    $('#eventTitle').val(info.event.title);
    $('#eventRoom').val(info.event.extendedProps.room);
    $('#eventProfessor').val(info.event.extendedProps.professor);
    $('#eventStart').val(startTime);
    $('#eventEnd').val(endTime);
    $('#eventDayOfWeek').val(dayOfWeek); // Populate day_of_week field

    $('#eventEditModal').modal('show');
}

});

        // Render the calendar
        calendar.render();

        // Save Changes Button - Event Handler
$('#saveEventChanges').click(function () {
    const eventData = {
        id: $('#eventId').val(),
        title: $('#eventTitle').val(),
        start: $('#eventStart').val(),
        end: $('#eventEnd').val(),
        room: $('#eventRoom').val(), // Pass room_name
        professor: $('#eventProfessor').val(),
        day_of_week: $('#eventDayOfWeek').val() // Include day of the week
    };

    $.ajax({
        url: 'ajax.php?action=update_schedule',
        method: 'POST',
        data: eventData,
        success: function (response) {
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    alert('Event updated successfully!');
                    $('#eventEditModal').modal('hide');
                    calendar.refetchEvents();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating event:', error);
                alert('Invalid server response.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Failed to update the event.');
        }
    });
});







$('#eventRoom').one('click', function () {
    $.ajax({
        url: 'ajax.php?action=get_rooms',
        method: 'GET',
        success: function (response) {
            console.log('Raw Response:', response); // Debugging raw response
            try {
                const rooms = typeof response === 'string' ? JSON.parse(response) : response;

                const roomDropdown = $('#eventRoom');
                roomDropdown.empty().append('<option value="" disabled selected>Select a room</option>');

                // Use room_name as both the value and displayed text
                rooms.forEach(room => {
                    roomDropdown.append(`<option value="${room.room_name}">${room.room_name}</option>`);
                });
            } catch (error) {
                console.error('Error parsing room data:', error); // Debugging any parsing issues
                alert('Failed to load rooms.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error); // Debugging AJAX errors
            alert('Failed to load rooms.');
        }
    });
});




        function updateEventInDatabase(event) {
            const eventData = {
                    id: parseInt(info.event.id, 10),
                    title: info.event.extendedProps.course_name,
                    start: info.event.start.toISOString(),
                    end: info.event.end.toISOString(),
                    room: info.event.extendedProps.room,
                    professor: info.event.extendedProps.professor.trim() // Trim any unnecessary spaces
                };

            console.log("Event Data Sent to Server:", eventData); // Debugging

            $.ajax({
                url: 'ajax.php?action=update_schedule',
                method: 'POST',
                data: eventData,
                success: function (response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            alert('Schedule updated successfully!');
                            calendar.refetchEvents(); // Refresh calendar events
                        } else {
                            alert('Failed to update schedule: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('An error occurred while updating the schedule.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error: ', status, error);
                    alert('An error occurred while updating the schedule.');
                }
            });
        }

        $('#delete_section_schedules').click(function () {
            const sectionId = $('#section_id').val();
            if (!sectionId) {
                alert('Please select a section first.');
                return;
            }
            if (confirm('Are you sure you want to delete all schedules for this section?')) {
                // Save the selected section in localStorage
                localStorage.setItem('selectedSection', sectionId);

                $.ajax({
                    url: 'ajax.php?action=delete_section_schedules',
                    method: 'POST',
                    data: { section_id: sectionId },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert('All schedules for the selected section have been deleted successfully.');

                                // Re-enable the "Generate Schedule Automatically" button
                                $('#auto_generate_schedule')
                                    .prop('disabled', false)
                                    .text('Generate Schedule Automatically');

                                // Optionally, clear the calendar view
                                calendar.getEvents().forEach(event => event.remove());
                                $('#card-container').html("<p>No schedule available for this section.</p>");
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch (e) {
                            console.error('Parsing error:', e);
                            alert('An error occurred while processing the delete request.');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error: ', status, error);
                        alert('An error occurred while deleting the schedules.');
                    }
                });
            }
        });

        // Enable button and load schedule when a section is selected
        $('#section_id').change(function() {
            const sectionId = $(this).val();
            $('#auto_generate_schedule').prop('disabled', !sectionId);
            if (sectionId) {
                loadWeeklySchedule(sectionId); // Reload calendar with the new section
            }
        });

        // Toggle between Calendar and Card views
        $('#toggle_view').click(function() {
            if ($('#calendar_view').is(':visible')) {
                $('#calendar_view').hide();
                $('#card_view').show();
                $(this).text('Switch to Calendar View');
                loadCardView($('#section_id').val());
            } else {
                $('#card_view').hide();
                $('#calendar_view').show();
                $(this).text('Switch to Card View');
            }
        });

        
        // Utility function to parse 12-hour time format (e.g., "07:00 AM") to 24-hour format ("07:00:00")
        function loadWeeklySchedule(section_id) {
    start_load(); // Optional: Visual loader for user feedback
    $.ajax({
        url: 'ajax.php?action=get_schedule',
        method: 'POST',
        data: { section_id: section_id },
        success: function (response) {
            try {
                const scheduleData = JSON.parse(response);

                if (scheduleData.status === "no_data") {
                    $('#calendar').html("<p>No schedule available for this section.</p>");
                    $('#auto_generate_schedule').prop('disabled', false).text('Generate Schedule Automatically');
                    end_load(); // Stop loader
                    return;
                } else if (scheduleData.status === "error") {
                    alert("Error: " + scheduleData.message);
                    end_load();
                    return;
                }

                // Clear existing events in the calendar
                calendar.getEvents().forEach(event => event.remove());

                // Process and add each event
                scheduleData.data.forEach(event => {
                    const dayOfWeekIndex = getDayNumber(event.day_of_week); // Convert day_of_week to index
                    const weekStart = moment().startOf('week'); // Start of the current week

                    // Calculate start and end datetime for the event
                    const startDate = moment(weekStart)
                        .add(dayOfWeekIndex, 'days')
                        .set({
                            hour: parseInt(event.start_time.split(':')[0], 10),
                            minute: parseInt(event.start_time.split(':')[1], 10),
                            second: 0,
                        })
                        .toDate();

                    const endDate = moment(weekStart)
                        .add(dayOfWeekIndex, 'days')
                        .set({
                            hour: parseInt(event.end_time.split(':')[0], 10),
                            minute: parseInt(event.end_time.split(':')[1], 10),
                            second: 0,
                        })
                        .toDate();

                    // Add event to FullCalendar
                    calendar.addEvent({
                        id: event.id,
                        title: `${event.title} (${event.component_type})\n${event.professor} - ${event.room}`,
                        start: startDate,
                        end: endDate,
                        backgroundColor: getColorForCourse(event.title), // Optional: Color assignment for better UI
                        borderColor: getColorForCourse(event.title),
                        extendedProps: {
                            professor: event.professor,
                            room: event.room,
                        },
                    });
                });

                calendar.render();

                // Disable "Generate Schedule Automatically" button if events exist
                if (calendar.getEvents().length > 0) {
                    $('#auto_generate_schedule').prop('disabled', true).text('Schedule Already Generated');
                } else {
                    $('#auto_generate_schedule').prop('disabled', false).text('Generate Schedule Automatically');
                }
            } catch (e) {
                console.error("Error parsing schedule response:", e);
                alert("An error occurred while processing the schedule.");
            }
            end_load(); // Stop loader
        },
        error: function () {
            alert("An error occurred while loading the schedule.");
            end_load();
        },
    });
}

// Helper function to map `day_of_week` (e.g., "Monday") to FullCalendar's day index
function getDayNumber(day) {
    const days = {
        Sunday: 0,
        Monday: 1,
        Tuesday: 2,
        Wednesday: 3,
        Thursday: 4,
        Friday: 5,
        Saturday: 6,
    };
    return days[day] || 0; // Default to Sunday if invalid day
}

        // Convert 12-hour time to 24-hour time
        function parse12HourTimeTo24Hour(time12h) {
            const [time, modifier] = time12h.split(' ');
            let [hours, minutes] = time.split(':');

            if (hours === '12') hours = '00';
            if (modifier === 'PM') hours = parseInt(hours, 10) + 12;

            return `${hours.padStart(2, '0')}:${minutes}:00`;
        }
        // Helper Function for 12-hour to 24-hour Conversion
        function parse12HourTimeTo24Hour(time12h) {
            const [time, modifier] = time12h.split(' ');
            let [hours, minutes] = time.split(':');

            if (hours === '12') {
                hours = '00';
            }
            if (modifier === 'PM') {
                hours = parseInt(hours, 10) + 12;
            }

            return `${hours.padStart(2, '0')}:${minutes}:00`;
        }
        // Function to load the weekly schedule in Card view
        function loadCardView(section_id) {
            start_load();
            $.ajax({
                url: 'ajax.php?action=get_schedule',
                method: 'POST',
                data: { section_id: section_id },
                success: function(response) {
                    try {
                        const scheduleData = JSON.parse(response);

                        // Check if the response indicates no data
                        if (scheduleData.status === "no_data") {
                            $('#card-container').html("<p>No schedule available for this section.</p>");
                            end_load();
                            return;
                        } else if (scheduleData.status === "error") {
                            alert("Error: " + scheduleData.message);
                            end_load();
                            return;
                        }

                        // Clear existing card entries
                        $('#card-container').empty();

                        // Populate cards with schedule data
                        scheduleData.data.forEach(event => {
                            const card = `
                                <div class="schedule-card">
                                    <h5>${event.title} (${event.component_type})</h5>
                                    <small><strong>Day:</strong> ${event.day_of_week}</small>
                                    <small><strong>Time:</strong> ${event.start_time} - ${event.end_time}</small> <!-- Displaying 12-hour format -->
                                    <small><strong>Professor:</strong> ${event.professor}</small>
                                    <small><strong>Room:</strong> ${event.room}</small>
                                </div>
                            `;
                            $('#card-container').append(card);
                        });
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        alert("An error occurred while loading the schedule.");
                    }
                    end_load();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                    alert("An error occurred while loading the schedule.");
                    end_load();
                }
            });
        }



        // Helper function to get day number for FullCalendar
        function getDayNumber(day) {
            switch(day) {
                case 'Monday': return 1;
                case 'Tuesday': return 2;
                case 'Wednesday': return 3;
                case 'Thursday': return 4;
                case 'Friday': return 5;
                case 'Saturday': return 6;
                default: return 0;
            }
        }

        // Generate Schedule Button
        $('#auto_generate_schedule').click(function () {
            const sectionId = $('#section_id').val();
            if (!sectionId) {
                alert("Please select a section to generate the schedule.");
                return;
            }
            if (confirm("Are you sure you want to auto-generate the schedule for this section?")) {
                // Save the selected section and reload flag in localStorage
                localStorage.setItem('selectedSection', sectionId);
                localStorage.setItem('reloadSchedule', 'true'); // Add reload flag

                // Disable the button to prevent multiple clicks
                $(this).prop('disabled', true).text('Generating Schedule...');

                $.ajax({
                    url: 'ajax.php?action=generate_schedule',
                    method: 'POST',
                    data: { section_id: sectionId },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === "success") {
                                alert("Schedule generation complete.");
                                // Refresh the page to reload the calendar
                                location.reload();
                            } else {
                                alert("Error generating schedule: " + result.message);
                                // Re-enable the button if there was an error in generation
                                $('#auto_generate_schedule').prop('disabled', false).text('Generate Schedule Automatically');
                            }
                        } catch (e) {
                            console.error("Parsing error:", e);
                            alert("Unexpected response format.");
                            $('#auto_generate_schedule').prop('disabled', false).text('Generate Schedule Automatically');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: " + status + ": " + error);
                        alert("An error occurred while generating the schedule.");
                        $('#auto_generate_schedule').prop('disabled', false).text('Generate Schedule Automatically');
                    }
                });
            }
        });

        $('#eventEditModal').on('show.bs.modal', function (event) {
    const modal = $(this);
});

$('#eventRoom').one('click', function () {
    $.ajax({
        url: 'ajax.php?action=get_rooms',
        method: 'GET',
        success: function (response) {
            console.log('Raw Response:', response); // Debug
            try {
                const rooms = typeof response === 'string' ? JSON.parse(response) : response;
                const roomDropdown = $('#eventRoom');
                roomDropdown.empty().append('<option value="" disabled selected>Select a room</option>');
                
                // Ensure room_id is used as the value
                rooms.forEach(room => {
                    roomDropdown.append(`<option value="${room.room_name}">${room.room_name}</option>`);
                });
            } catch (error) {
                console.error('Error parsing room data:', error);
                alert('Failed to load rooms.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
            alert('Failed to load rooms.');
        }
    });
});




$('#eventProfessor').one('click', function () {
    $.ajax({
        url: 'ajax.php?action=get_professors',
        method: 'GET',
        success: function (response) {
            try {
                const parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                if (!parsedResponse.success || !Array.isArray(parsedResponse.data)) {
                    throw new Error('Invalid response structure.');
                }

                const professorDropdown = $('#eventProfessor');
                professorDropdown.empty().append('<option value="" disabled selected>Select a professor</option>');

                parsedResponse.data.forEach(professor => {
                    professorDropdown.append(
                        `<option value="${professor.full_name}">${professor.full_name}</option>`
                    );
                });
            } catch (error) {
                console.error('Error loading professors:', error);
                alert('Failed to load professors.');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Failed to fetch professors. Please try again.');
        }
    });
});





    })
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
<script>
document.getElementById('export_to_pdf').addEventListener('click', function () {
    const sectionId = $('#section_id').val();
    if (!sectionId) {
        alert('Please select a section to export.');
        return;
    }

    // Fetch schedule data for the selected section
    $.ajax({
        url: 'ajax.php?action=get_schedule',
        method: 'POST',
        data: { section_id: sectionId },
        success: function (response) {
            try {
                const scheduleData = JSON.parse(response);

                if (scheduleData.status === "no_data") {
                    alert("No schedule available for this section.");
                    return;
                } else if (scheduleData.status === "error") {
                    alert("Error: " + scheduleData.message);
                    return;
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Set document title
                doc.setFontSize(16);
                doc.text("Weekly Schedule", 105, 10, null, null, "center");

                // Add section name
                const sectionName = $('#section_id option:selected').text();
                doc.setFontSize(12);
                doc.text(`Section: ${sectionName}`, 10, 20);

                // Helper function to format time (remove milliseconds)
                function formatTime(timeString) {
                    const [hours, minutes] = timeString.split(':');
                    return `${hours}:${minutes}`;
                }

                // Prepare table data
                const tableData = scheduleData.data.map(event => [
                    event.title, // Course
                    event.component_type, // Type
                    event.day_of_week, // Day
                    `${formatTime(event.start_time)} - ${formatTime(event.end_time)}`, // Time without milliseconds
                    event.room, // Room
                    event.professor // Professor
                ]);

                // Define table headers
                const tableHeaders = ["Course", "Type", "Day", "Time", "Room", "Professor"];

                // Generate table in the PDF
                doc.autoTable({
                    startY: 30,
                    head: [tableHeaders],
                    body: tableData,
                    styles: { fontSize: 10, cellPadding: 5 },
                    headStyles: { fillColor: [0, 102, 204], textColor: [255, 255, 255] }, // Optional styling
                });

                // Save the document
                doc.save(`${sectionName}_Schedule.pdf`);
            } catch (e) {
                console.error("Error processing response:", e);
                alert("An error occurred while exporting the schedule.");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Failed to fetch schedule data.");
        }
    });
});


</script>
