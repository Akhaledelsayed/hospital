<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Header</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            color: white;
              position: sticky;

        }

        .u-name img {
            height: 60px;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin-right: 1100px;
            
        }

        .notification {
            position: relative;
            margin-right: 20px;
            cursor: pointer;
        }

        .notification #notificationNum {
            position: absolute;
            top: -5px;
            right: -10px;
            background: none;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 50%;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
          font-size: 16px;  
  border: none;
  outline: none;
  color: white;
  padding: 14px 16px;
  background-color: inherit;
  font-family: inherit;
  margin: 0;
        }

        .dropdown-content {
            display: none;
  position: absolute;
  background-color: #f9f9f9;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
        }

        .dropdown-content a {
    float: none;
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .notification-bar {
            display: none;
            position: absolute;
            top: 88px;
            right: 10px;
            background: #fff;
            border: 1px solid #ccc;
            width: 300px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            
        }

        .open-notification {
            display: block !important;
        }
        .profile-img {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 5px;
  border: 2px solid white;
}

    </style>
</head>

<body style="background-color:white;">

<header class="header">
    <h2 class="u-name">
        <img src="img/logo2.png" alt="Task Pro">
    </h2>

    <div style="display: flex; align-items: center;">
        <a href="index2.php">
            <i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard
        </a>

        <span class="notification" id="notificationBtn">
            <i class="fa fa-bell" aria-hidden="true"></i>
            <span id="notificationNum"></span>
        </span>

    <div class="dropdown">
  <button class="dropbtn">
    <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-img">
    <i class="fa fa-caret-down"></i>
  </button>
  <div class="dropdown-content">
    <a href="inc/profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

    </div>
</header>

<div class="notification-bar" id="notificationBar">
    <ul id="notifications" style="list-style: none; padding: 10px;"></ul>
</div>

<!-- JS -->
<script>
    var openNotification = false;
    const notification = () => {
        let notificationBar = document.querySelector("#notificationBar");
        if (openNotification) {
            notificationBar.classList.remove('open-notification');
            openNotification = false;
        } else {
            notificationBar.classList.add('open-notification');
            openNotification = true;
        }
    }

    let notificationBtn = document.querySelector("#notificationBtn");
    notificationBtn.addEventListener("click", notification);
</script>

<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $("#notificationNum").load("app/notification-count.php");
        $("#notifications").load("app/notification.php");
    });
</script>

</body>
</html>
