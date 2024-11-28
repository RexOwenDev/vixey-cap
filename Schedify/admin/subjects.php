<?php include('db_connect.php');?> 

<div class="container-fluid">
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<button class="btn btn-primary btn-block btn-sm col-sm-2 float-right" type="button" id="new_program">
					<i class="fa fa-plus"></i> New
				</button>
			</div>
		</div>
		<div class="row">
			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Program List</b>
					</div>
					<div class="card-body">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Program</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$i = 1;
								$program = $conn->query("SELECT * FROM programs ORDER BY id ASC");
								while($row = $program->fetch_assoc()):
								?>
								<tr>
									<td class="text-center"><?php echo $i++ ?></td>
									<td class="">
										<p>Program: <b><?php echo $row['program'] ?></b></p>
										<p>Program Code: <small><b><?php echo $row['description'] ?></b></small></p>
									</td>
									<td class="text-center">
										<button class="btn btn-sm btn-primary edit_program" type="button" data-id="<?php echo $row['id'] ?>" data-program="<?php echo $row['program'] ?>" data-description="<?php echo $row['description'] ?>">Edit</button>
										<button class="btn btn-sm btn-danger delete_program" type="button" data-id="<?php echo $row['id'] ?>">Delete</button>
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

<!-- Modal for Program Form -->
<div class="modal fade" id="programModal" tabindex="-1" role="dialog" aria-labelledby="programModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="programModalLabel">New Program</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="manage-program">
					<input type="hidden" name="id">
					<div class="form-group">
						<label class="control-label">Program</label>
						<input type="text" class="form-control" name="program" required>
					</div>
					<div class="form-group">
						<label class="control-label">Program Code</label>
						<textarea class="form-control" cols="30" rows='3' name="description" required></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="save_program">Save</button>
			</div>
		</div>
	</div>
</div>

<script>
	function _reset(){
		$('#manage-program').get(0).reset()
		$('#manage-program input,#manage-program textarea').val('')
	}

	// Trigger modal for new program
	$('#new_program').click(function(){
		$('#programModal').modal('show')
		$('#programModalLabel').text('New Program')
		_reset()
	})

	// Save program when Save button is clicked
	$('#save_program').click(function(){
		start_load()
		$.ajax({
			url: 'ajax.php?action=save_program',
			data: new FormData($('#manage-program')[0]),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			type: 'POST',
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Data successfully added", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)
				} else if (resp == 2) {
					alert_toast("Data successfully updated", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)
				}
			}
		})
	})

	// Edit program
	$('.edit_program').click(function(){
		start_load()
		$('#programModal').modal('show')
		$('#programModalLabel').text('Edit Program')

		var form = $('#manage-program')
		form.get(0).reset()
		form.find("[name='id']").val($(this).attr('data-id'))
		form.find("[name='program']").val($(this).attr('data-program'))
		form.find("[name='description']").val($(this).attr('data-description'))
		end_load()
	})

	// Delete program
	$('.delete_program').click(function(){
		_conf("Are you sure to delete this program?", "delete_program", [$(this).attr('data-id')])
	})

	function delete_program($id) {
		start_load()
		$.ajax({
			url: 'ajax.php?action=delete_program',
			method: 'POST',
			data: {id: $id},
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Data successfully deleted", 'success')
					setTimeout(function() {
						location.reload()
					}, 1500)
				}
			}
		})
	}

	$('table').dataTable()
</script>
