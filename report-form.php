<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id']) || !$hospital_code) {
    echo "<h2 style='text-align:center; color:red;'>❌ Access denied. Please log in.</h2>";
    exit;
}

include "DB_connection.php";
include "app/Model/Devices.php";

// Access control logic
$role = $_SESSION['role'];
$user_id = $_SESSION['id'];
$hasAccess = false;

if ($role === "admin") {
    $hasAccess = true;
} else {
    // Check if user is assigned to this hospital
    $stmt = $conn->prepare("SELECT hospital_code FROM user_hospitals WHERE username = (SELECT username FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $user_hospitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array($hospital_code, $user_hospitals)) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    echo "<h2 style='text-align:center; color:red;'>❌ You are not assigned to this hospital.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Biomedical Reports Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />  

  <style>
    body {
      font-family: Arial, sans-serif;
      direction: ltr;
    }
    .form-box {
      background: #fff;
      padding: 20px;
      width: 80%;
      margin: auto;
      border-radius: 10px;
      box-shadow: 0 0 10px #aaa;
    }
    .form-box h2 {
      text-align: center;
      color: green;
    }
    .form-group {
      margin-bottom: 10px;
    }
    label {
      display: block;
      font-weight: bold;
    }
    input, select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .buttons {
      text-align: center;
      margin-top: 20px;
    }
    .buttons button {
      padding: 10px 15px;
      margin: 5px;
      background-color: #127b8e;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .buttons button:hover {
      background-color: #127b8e;
    }
  </style>
</head>
<body>
    <?php include "inc/header.php"; ?>
    <div class="body">
        <?php include "inc/nav.php"; ?>
  <div class="form-box">
    <h2>Report Form</h2>
    <form action="report-result.php" method="POST" target="_blank">
      <div class="form-group"><label>Eq Def #</label><input type="text" name="eq_def"></div>
      <div class="form-group"><label>Resolved</label><input type="text" name="resolved"></div>
      <div class="form-group"><label>Department</label><input type="text" name="dep"></div>
      <div class="form-group"><label>Employee</label><input type="text" name="employee1"></div>
      <div class="form-group"><label>Received By</label><input type="text" name="received_by"></div>
      <div class="form-group"><label>Equipment Name</label><input type="text" name="eq_name"></div>
      <div class="form-group"><label>Received Within</label><input type="date" name="received_within"></div>
      <div class="form-group"><label>Name</label><input type="text" name="name"></div>
      <div class="form-group"><label>Calibration Month</label><input type="text" name="cal_month"></div>
      <div class="form-group"><label>Employee</label><input type="text" name="employee2"></div>
      <div class="form-group"><label>Calibration</label><input type="text" name="calibration"></div>
      <div class="form-group"><label>Duration</label><input type="text" name="duration"></div>

      <div class="buttons">
        <button type="submit" name="report_type" value="malfunction_history">Malfunction History Report</button>
        <button type="submit" name="report_type" value="malfunction_analysis">Malfunction Analysis Report</button>
        <button type="submit" name="report_type" value="malfunction_report">Malfunction Report</button>
        <button type="submit" name="report_type" value="repair_type">Repair Type Report</button>
        <button type="submit" name="report_type" value="wostatus">W/O Status Report</button>
      </div>
    </form>
  </div>

</body>
</html>
