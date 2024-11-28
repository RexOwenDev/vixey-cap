<style>
    /* Base Styles for Sidebar */
    nav#sidebar {
        width: 240px;
        min-height: 100vh;
        background: rgba(255, 255, 255, 0.7); /* Light Mode Glassmorphism Effect */
        backdrop-filter: blur(10px);
        border-right: 1px solid rgba(0, 0, 0, 0.1);
        padding-top: 20px;
        color: #333;
        font-family: Arial, sans-serif;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Dark Mode Styles */
    .dark-mode nav#sidebar {
        background: rgba(30, 30, 47, 0.8);
        color: #f0f0f5;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Sidebar Items */
    .sidebar-list {
        padding: 0;
    }

    .sidebar-list a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        margin: 8px 15px;
        font-size: 15px;
        color: inherit;
        border-radius: 12px;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
    }

    .sidebar-list a .icon-field {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(76, 95, 215, 0.2);
        border-radius: 50%;
        margin-right: 12px;
        font-size: 18px;
        color: #4c5fd7;
    }

    /* Active and Hover Styles */
    .sidebar-list a.active,
    .sidebar-list a:hover {
        background: #4c5fd7;
        color: #fff;
    }

    .sidebar-list a.active .icon-field,
    .sidebar-list a:hover .icon-field {
        background: #fff;
        color: #4c5fd7;
    }

    /* Dark Mode Active and Hover */
    .dark-mode .sidebar-list a.active,
    .dark-mode .sidebar-list a:hover {
        background: #3a3a52;
        color: #f0f0f5;
    }

    .dark-mode .sidebar-list a.active .icon-field,
    .dark-mode .sidebar-list a:hover .icon-field {
        background: #f0f0f5;
        color: #3a3a52;
    }

    /* Dark/Light Mode Toggle */
    .toggle-theme {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px 0;
        padding: 10px;
        cursor: pointer;
        border-radius: 12px;
        transition: background 0.2s;
        color: inherit;
        text-align: center;
    }

    .toggle-theme:hover {
        background-color: rgba(76, 95, 215, 0.1);
    }

    .dark-mode .toggle-theme:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>

<nav id="sidebar">
   <div class="sidebar-list">
       <a href="index.php?page=home" class="nav-item nav-home"><span class="icon-field"><i class="fa fa-home"></i></span> Home</a>
       <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'program_head') : ?>
           <a href="index.php?page=faculty" class="nav-item nav-faculty"><span class="icon-field"><i class="fa fa-user-tie"></i></span> Faculty List</a>
       <?php endif; ?>
       <a href="index.php?page=schedule" class="nav-item nav-schedule"><span class="icon-field"><i class="fa fa-calendar-day"></i></span> Schedule</a>
       <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'program_head') : ?>
           <a href="index.php?page=manage_courses" class="nav-item nav-manage_courses"><span class="icon-field"><i class="fa fa-chalkboard-teacher"></i></span> Manage Courses</a>
       <?php endif; ?>
       <?php if ($_SESSION['role'] == 'admin') : ?>
           <a href="index.php?page=users" class="nav-item nav-users"><span class="icon-field"><i class="fa fa-users"></i></span> Users</a>
           <a href="index.php?page=manage_rooms" class="nav-item nav-manage_rooms"><span class="icon-field"><i class="fa fa-door-closed"></i></span> Manage Rooms</a>
       <?php endif; ?>
       <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'program_head') : ?>
           <a href="index.php?page=generated_reports" class="nav-item nav-generated_reports"><span class="icon-field"><i class="fa fa-file-alt"></i></span> Generated Reports</a>
       <?php endif; ?>
   </div>
</nav>
