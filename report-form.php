<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report Form</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      padding: 20px;
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
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .buttons button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

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
