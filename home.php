<?php include 'db_connect.php' ?>

<style>
   .dashboard-title {
       text-align: center;
       font-size: 2.2rem;
       font-weight: bold;
       color: #333;
       margin-bottom: 15px;
   }
   .dashboard-description {
       text-align: center;
       font-size: 1.1rem;
       color: #555;
       margin-bottom: 30px;
   }
   .dashboard-tiles {
       display: flex;
       justify-content: center;
       flex-wrap: wrap;
       gap: 20px;
   }
   .dashboard-tile {
       width: 220px;
       background: #ffffff;
       border-radius: 10px;
       padding: 20px 15px;
       text-align: center;
       box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
       transition: transform 0.2s, box-shadow 0.2s;
   }
   .dashboard-tile:hover {
       transform: translateY(-5px);
       box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
   }
   .dashboard-tile i {
       font-size: 2.5rem;
       color: #007bff;
       margin-bottom: 10px;
   }
   .dashboard-tile h3 {
       font-size: 1.2rem;
       font-weight: bold;
       color: #333;
   }
</style>

<div class="container-fluid">
   <h1 class="dashboard-title">Welcome to Schedify</h1>
   <p class="dashboard-description">Manage and streamline the scheduling of courses, faculty, and resources efficiently. Start by selecting a module below.</p>
   <div class="dashboard-tiles">
       <a href="index.php?page=faculty" class="dashboard-tile">
           <i class="fa fa-user-tie"></i>
           <h3>Faculty List</h3>
       </a>
       <a href="index.php?page=schedule" class="dashboard-tile">
           <i class="fa fa-calendar-day"></i>
           <h3>Schedule</h3>
       </a>
       <a href="index.php?page=manage_courses" class="dashboard-tile">
           <i class="fa fa-chalkboard-teacher"></i>
           <h3>Manage Courses</h3>
       </a>
       <a href="index.php?page=users" class="dashboard-tile">
           <i class="fa fa-users"></i>
           <h3>Users</h3>
       </a>
       <a href="index.php?page=generated_reports" class="dashboard-tile">
           <i class="fa fa-file-alt"></i>
           <h3>Generated Reports</h3>
       </a>
       <a href="index.php?page=manage_rooms" class="dashboard-tile">
           <i class="fa fa-door-closed"></i>
           <h3>Manage Rooms</h3>
       </a>
   </div>
</div>
