<!-- report.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report: <?= htmlspecialchars($_POST['report_type']) ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      direction: ltr;
      padding: 20px;
      background-color: #f9f9f9;
    }
    .report-box {
      border: 1px solid #000;
      padding: 20px;
      background: #fff;
      max-width: 800px;
      margin: auto;
      border-radius: 10px;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
    }
    .line {
      margin: 10px 0;
      border-bottom: 1px solid #eee;
      padding-bottom: 5px;
    }
    .print-btn {
      margin-top: 20px;
      text-align: center;
    }
    button {
      padding: 10px 25px;
      font-size: 16px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

<div class="report-box">
  <h2>Report: <?= ucwords(str_replace("_", " ", htmlspecialchars($_POST['report_type']))) ?></h2>
  <?php
    foreach ($_POST as $key => $value) {
      if ($key != "report_type") {
        echo "<div class='line'><strong>" . ucwords(str_replace("_", " ", htmlspecialchars($key))) . ":</strong> " . htmlspecialchars($value) . "</div>";
      }
    }
  ?>
</div>

<div class="print-btn">
  <button onclick="window.print()">Print Report</button>
</div>

</body>
</html>
