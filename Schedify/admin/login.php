<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include('./db_connect.php');
ob_start();
ob_end_flush();
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Schedify</title>
  <?php include('./header.php'); ?>
  <?php 
  if(isset($_SESSION['login_id']))
  header("location:index.php?page=home");
  ?>
  <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }


    html, body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100%;
      width: 100%;
      background: #f2f2f2;
    }

    .container {
      display: flex;
      width: 900px;
      max-width: 100%;
      background: #fff;
      box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1);
      border-radius: 10px;
      overflow: hidden;
    }

    /* Left Section */
    .left-section {
      background: linear-gradient(-135deg, #4158d0, #c850c0);
      color: #fff;
      padding: 40px;
      width: 40%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      position: relative;
    }

    .left-section h1 {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .left-section p {
      font-size: 16px;
      line-height: 1.6;
    }

    /* Login Form Section */
    .login-section {
      padding: 40px 20px;
      width: 60%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .login-section h2 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    .login-section .field {
      width: 100%;
      margin-bottom: 20px;
      position: relative;
    }

    .login-section .field input {
      width: 100%;
      padding: 15px 20px 15px 45px;
      font-size: 16px;
      border: 1px solid lightgrey;
      border-radius: 5px;
      transition: all 0.3s ease;
      outline: none;
    }

    .login-section .field .icon {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #4158d0;
    }

    .login-section button {
      width: 100%;
      padding: 15px;
      font-size: 18px;
      color: #fff;
      background: linear-gradient(-135deg, #4158d0, #c850c0);
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .login-section button:hover {
      background: linear-gradient(-135deg, #354dbf, #a845a8);
    }

    /* Checkbox Styling */
    .content {
      display: flex;
      width: 100%;
      align-items: center;
      font-size: 16px;
      justify-content: space-between;
      margin-top: 10px;
    }

    .content .checkbox {
      display: flex;
      align-items: center;
    }

    .content input[type="checkbox"] {
      width: 15px;
      height: 15px;
      margin-right: 5px;
    }

    .left-section {
      background: linear-gradient(-135deg, #4158d0, #c850c0);
      color: #fff;
      padding: 40px;
      width: 40%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center; /* Center align all content horizontally */
      text-align: center; /* Center text */
    }

    .left-section .logo {
      width: 200px; /* Adjust size as needed */
      height: auto;
      margin-bottom: 20px; /* Space between the logo and text */
    }
  </style>
</head>

<body>

<div class="container">
  <!-- Left Section with Logo -->
  <div class="left-section">
    <img src="assets/img/logo.png" alt="Schedify Logo" class="logo">
    <h1>Welcome to Schedify</h1>
    <p>Your efficient scheduling and time management tool. Login to access your dashboard and stay on top of your schedule!</p>
  </div>

  <!-- Login Section -->
  <div class="login-section">
    <h2>Login</h2>
    <form id="login-form">
      <div class="field">
        <span class="icon">👤</span> <!-- User icon -->
        <input type="text" id="username" name="username" placeholder="Username" required>
      </div>
      <div class="field">
        <span class="icon">🔒</span> <!-- Lock icon -->
        <input type="password" id="password" name="password" placeholder="Password" required>
      </div>
      <div class="content">
        <div class="checkbox">
          <input type="checkbox" id="remember-me">
          <label for="remember-me">Remember me</label>
        </div>
      </div>
      <button type="submit">Login</button>
    </form>
  </div>
</div>



<script>
  $('#login-form').submit(function(e){
    e.preventDefault();
    $('#login-form button').attr('disabled', true).text('Logging in...');
    if ($(this).find('.alert-danger').length > 0)
      $(this).find('.alert-danger').remove();
    
    $.ajax({
      url: 'ajax.php?action=login',
      method: 'POST',
      data: $(this).serialize(),
      error: err => {
        console.log(err);
        $('#login-form button').removeAttr('disabled').text('Login');
      },
      success: function(resp) {
        console.log("Response from server:", resp); // Debugging response
        if (resp == 1) {
          location.href = 'index.php?page=home';
        } else if (resp == 4 || resp == 5 || resp == 6) {
          location.href = 'index.php?page=schedule'; // Redirect faculty, program_head, and student to schedule page
        } else {
          $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
          $('#login-form button').removeAttr('disabled').text('Login');
        }
      }
    });
  });
</script>

</body>
</html>
