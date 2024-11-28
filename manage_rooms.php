<?php
include('db_connect.php');

// Fetch the rooms from the database
$rooms = $conn->query("SELECT * FROM rooms");
?>

<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
                <button class="btn btn-primary btn-block btn-sm col-sm-2 float-right" type="button" id="new_room">
                    <i class="fa fa-plus"></i> New Room
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>Manage Rooms</b>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Room ID</th>
                                    <th class="text-center">Room Name</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                while($row = $rooms->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td class="text-center"><?php echo $row['room_id'] ?></td>
                                    <td class="text-center"><?php echo $row['room_name'] ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary edit_room" type="button" data-id="<?php echo $row['room_id'] ?>" data-room_name="<?php echo $row['room_name'] ?>">Edit</button>
                                        <button class="btn btn-sm btn-danger delete_room" type="button" data-id="<?php echo $row['room_id'] ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>

<!-- Modal for Room Form -->
<div class="modal fade" id="roomModal" tabindex="-1" role="dialog" aria-labelledby="roomModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalLabel">New Room</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="manage-room">
                    <input type="hidden" name="id">
                    <div class="form-group">
                        <label class="control-label">Room ID</label>
                        <input type="text" class="form-control" name="room_id" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Room Name</label>
                        <input type="text" class="form-control" name="room_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save_room">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    function _reset(){
        $('#manage-room').get(0).reset();
        $('#manage-room input').val('');
    }

    $('#new_room').click(function(){
        $('#roomModal').modal('show');
        $('#roomModalLabel').text('New Room');
        _reset();
    });

    $('#save_room').click(function(){
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_room',
            data: new FormData($('#manage-room')[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Room successfully added", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else if (resp == 2) {
                    alert_toast("Room successfully updated", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            }
        });
    });

    $('.edit_room').click(function(){
        start_load();
        $('#roomModal').modal('show');
        $('#roomModalLabel').text('Edit Room');

        var form = $('#manage-room');
        form.get(0).reset();
        form.find("[name='room_id']").val($(this).attr('data-id'));
        form.find("[name='room_name']").val($(this).attr('data-room_name'));
        end_load();
    });

    $('.delete_room').click(function(){
    _conf("Are you sure to delete this room?", "delete_room", [$(this).attr('data-id')]);
    });

    function delete_room(id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_room',
            method: 'POST',
            data: {id: id},
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Room successfully deleted", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                } else {
                    alert_toast("Failed to delete room", 'danger');
                }
            }
        });
    }

    $('table').dataTable();
</script>
