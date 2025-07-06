<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Responsive Sidebar Navigation</title>
  
  <!-- FontAwesome for icons -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
  />

  <style>
    /* Sidebar styles */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f2f5;
      direction: ltr;
    }

    .side-bar {
      width: 250px;
      background-color: #1e293b;
      color: #f1f5f9;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      overflow-y: auto;
      transition: transform 0.3s ease;
      z-index: 1030;
      box-shadow: 2px 0 12px rgba(0, 0, 0, 0.15);
      padding-bottom: 40px;
    }

    .side-bar .user-p {
      text-align: center;
      padding: 25px 15px;
      border-bottom: 1px solid #334155;
    }

    .side-bar .user-p h4 {
      margin: 0;
      font-weight: 600;
      font-size: 20px;
      color: #e2e8f0;
    }

    .nav-list {
      list-style: none;
      padding-left: 0;
      margin-top: 20px;
    }

    .nav-list li {
      margin-bottom: 8px;
    }

    .nav-list li a {
      display: flex;
      align-items: center;
      color: #f1f5f9;
      padding: 12px 25px;
      text-decoration: none;
      border-radius: 0 8px 8px 0;
      font-weight: 500;
      font-size: 16px;
      transition: background-color 0.3s ease;
      user-select: none;
    }

    .nav-list li a i {
      margin-right: 12px;
      font-size: 18px;
      width: 25px;
      text-align: center;
      flex-shrink: 0;
    }

    .nav-list li a:hover,
    .nav-list li a.active {
      background-color: #3b82f6;
      color: white;
    }

    /* Toggle button for mobile */
    #toggleSidebar {
      display: none;
      position: absolute;
      top: 18px;
      left: 18px;
      background: transparent;
      border: none;
      font-size: 24px;
      color: #f1f5f9;
      cursor: pointer;
      z-index: 1100;
      user-select: none;
    }

    /* Main content spacing */
    .main-content {
      margin-left: 250px;
      padding: 25px;
      transition: margin-left 0.3s ease;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
      .side-bar {
        transform: translateX(-260px);
        box-shadow: 3px 0 12px rgba(0, 0, 0, 0.3);
      }

      .side-bar.active {
        transform: translateX(0);
      }

      #toggleSidebar {
        display: block;
      }

      .main-content {
        margin-left: 0;
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>

  <!-- Toggle sidebar button -->
  <button id="toggleSidebar" aria-label="Toggle sidebar menu">
    <i class="fa fa-bars"></i>
  </button>

  <!-- Sidebar navigation -->
  <nav class="side-bar" role="navigation" aria-label="Main sidebar">
    <div class="user-p">
      <!-- User image can go here -->
       <img src="img/user.png" alt="User Image" class="rounded-circle" style="width:80px; height:80px;"> 
      <h4>@<?php echo htmlspecialchars($_SESSION['username']); ?></h4>
    </div>

    <?php if ($_SESSION['role'] == "employee") { ?>
      <ul id="navList" class="nav-list">
        <li><a href="index.php"><i class="fa fa-tachometer"></i> Dashboard</a></li>
        <li><a href="my_task.php"><i class="fa fa-tasks"></i> My Tasks</a></li>
        <li><a href="profile.php"><i class="fa fa-user"></i> Profile</a></li>
        <li><a href="notifications.php"><i class="fa fa-bell"></i> Notifications</a></li>
        <li><a href="time-sheet.php"><i class="fa fa-clock-o"></i> Time Sheet</a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
      </ul>
    <?php } else { ?>
      <ul id="navList" class="nav-list">
        <li><a href="index.php"><i class="fa fa-tachometer"></i> Dashboard</a></li>
        <li><a href="user.php"><i class="fa fa-users"></i> Manage Users</a></li>
        <li><a href="projects.php"><i class="fa fa-folder"></i> Projects</a></li>
        <li><a href="create_task.php"><i class="fa fa-plus"></i> Create Task</a></li>
        <li><a href="tasks.php"><i class="fa fa-tasks"></i> All Tasks</a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
      </ul>
    <?php } ?>
  </nav>

  <!-- Main content area -->
  <div class="main-content">
    <h1>Welcome to the Dashboard</h1>
    <p>Place your main content here.</p>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const toggleBtn = document.getElementById("toggleSidebar");
      const sidebar = document.querySelector(".side-bar");

      toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("active");
      });
    });
  </script>
</body>
</html>
