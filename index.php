<!DOCTYPE html>
<html lang="en">
	
<?php session_start(); ?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>School Faculty Scheduling System</title>
 	

<?php
  if(!isset($_SESSION['login_id']))
    header('location:login.php');
 include('./header.php'); 
 // include('./auth.php'); 
 ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
      body {
          background: #e3eaf0;
      }

      .container {
          max-width: 1200px;
          margin-top: 50px;
      }

      .module-card {
          background-color: #fff;
          color: #333;
          border: 1px solid rgba(0, 0, 0, 0.1);
          border-radius: 12px;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          transition: all 0.3s ease;
          text-decoration: none;
      }

      .module-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
          background-color: #4c5fd7;
          color: #fff;
      }

      .module-card .card-body {
          padding: 20px;
      }

      .module-card .icon-style {
          color: #4c5fd7;
          transition: color 0.3s;
      }

      .module-card:hover .icon-style {
          color: #fff;
      }

      .module-card h5 {
          margin-top: 15px;
          font-size: 18px;
          font-weight: 600;
      }

      .module-card:hover h5 {
          color: #fff;
      }

      .row {
          display: flex;
          flex-wrap: wrap;
          gap: 20px;
      }

      .col-md-4 {
          flex: 1 1 30%;
          min-width: 250px;
      }
  </style>
</head>
<body>
    <?php include 'topbar.php'; ?>
    <?php include 'navbar.php'; ?>

    <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>

    <main id="view-panel">
        <?php 
            // Get the page from the URL, default is 'home'
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';

            if ($page == 'home') : ?>
                <!-- Display homepage with module cards -->
                <div class="container">
                    <h1 class="text-center">Welcome to Schedify</h1>
                    <p class="text-center mb-5">
                        Manage and streamline the scheduling of courses, faculty, and resources efficiently. Start by selecting a module below.
                    </p>
                    <div class="row">
                        <!-- Faculty List Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=faculty" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-user-tie fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Faculty List</h5>
                                </div>
                            </a>
                        </div>
                        <!-- Schedule Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=schedule" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-calendar-day fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Schedule</h5>
                                </div>
                            </a>
                        </div>
                        <!-- Manage Courses Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=manage_courses" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-chalkboard-teacher fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Manage Courses</h5>
                                </div>
                            </a>
                        </div>
                        <!-- Users Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=users" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-users fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Users</h5>
                                </div>
                            </a>
                        </div>
                        <!-- Generated Reports Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=generated_reports" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-file-alt fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Generated Reports</h5>
                                </div>
                            </a>
                        </div>
                        <!-- Manage Rooms Module -->
                        <div class="col-md-4">
                            <a href="index.php?page=manage_rooms" class="card module-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-door-closed fa-3x icon-style"></i>
                                    <h5 class="card-title mt-3">Manage Rooms</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
        <?php
            else:
                // Include the corresponding page based on the URL parameter
                switch ($page) {
                    case 'courses':
                        include 'courses.php';
                        break;
                    case 'subjects':
                        include 'subjects.php';
                        break;
                    case 'faculty':
                        include 'faculty.php';
                        break;
                    case 'schedule':
                        include 'schedule.php';
                        break;
                    case 'users':
                        include 'users.php';
                        break;
                    case 'manage_courses':
                        include 'manage_courses.php';
                        break;
                    case 'generated_reports':
                        include 'generated_reports.php';
                        break;
                    case 'manage_rooms':
                        include 'manage_rooms.php';
                        break;
                    default:
                        include '404.php'; // Optional: Add a 404 page if the page is not found
                        break;
                }
            endif;
        ?>
    </main>

    <div id="preloader"></div>
    <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

    <div class="modal fade" id="uni_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submit" onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirm_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirm" onclick="">Continue</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewer_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
                <img src="" alt="">
            </div>
        </div>
    </div>
</body>
<script>
	 window.start_load = function(){
    $('body').prepend('<di id="preloader2"></di>')
  }
  window.end_load = function(){
    $('#preloader2').fadeOut('fast', function() {
        $(this).remove();
      })
  }
 window.viewer_modal = function($src = ''){
    start_load()
    var t = $src.split('.')
    t = t[1]
    if(t =='mp4'){
      var view = $("<video src='"+$src+"' controls autoplay></video>")
    }else{
      var view = $("<img src='"+$src+"' />")
    }
    $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
    $('#viewer_modal .modal-content').append(view)
    $('#viewer_modal').modal({
            show:true,
            backdrop:'static',
            keyboard:false,
            focus:true
          })
          end_load()  

}
  window.uni_modal = function($title = '' , $url='',$size=""){
    start_load()
    $.ajax({
        url:$url,
        error:err=>{
            console.log()
            alert("An error occured")
        },
        success:function(resp){
            if(resp){
                $('#uni_modal .modal-title').html($title)
                $('#uni_modal .modal-body').html(resp)
                if($size != ''){
                    $('#uni_modal .modal-dialog').addClass($size)
                }else{
                    $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
                }
                $('#uni_modal').modal({
                  show:true,
                  backdrop:'static',
                  keyboard:false,
                  focus:true
                })
                end_load()
            }
        }
    })
}
window._conf = function($msg='',$func='',$params = []){
     $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
     $('#confirm_modal .modal-body').html($msg)
     $('#confirm_modal').modal('show')
  }
   window.alert_toast= function($msg = 'TEST',$bg = 'success'){
      $('#alert_toast').removeClass('bg-success')
      $('#alert_toast').removeClass('bg-danger')
      $('#alert_toast').removeClass('bg-info')
      $('#alert_toast').removeClass('bg-warning')

    if($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({delay:3000}).toast('show');
  }
  $(document).ready(function(){
    $('#preloader').fadeOut('fast', function() {
        $(this).remove();
      })
  })
  $('.datetimepicker').datetimepicker({
      format:'Y/m/d H:i',
      startDate: '+3d'
  })
  $('.select2').select2({
    placeholder:"Please select here",
    width: "100%"
  })
</script>	
</html>
