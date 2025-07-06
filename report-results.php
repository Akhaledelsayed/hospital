<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report - <?= htmlspecialchars($_POST['report_type']) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f4f4;
      padding: 30px;
      margin: 0;
    }

    .report-container {
      background-color: #fff;
      max-width: 1000px;
      margin: auto;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    .info {
      margin-bottom: 20px;
    }

    .info div {
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .info strong {
      color: #00796b;
    }

    .chart-section {
      margin-top: 40px;
    }

    .chart-section h2 {
      text-align: center;
      color: #26a69a;
      margin-bottom: 20px;
    }

    .print-btn {
      text-align: center;
      margin-top: 40px;
    }

    .print-btn button {
      padding: 12px 25px;
      background-color: #4caf50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    .print-btn button:hover {
      background-color: #388e3c;
    }
  </style>
</head>
<body>

<div class="report-container">
  <h1><?= ucwords(str_replace("_", " ", htmlspecialchars($_POST['report_type']))) ?> Report</h1>

  <div class="info">
    <?php
    foreach ($_POST as $key => $value) {
      if ($key !== "report_type") {
        echo "<div><strong>" . ucwords(str_replace("_", " ", htmlspecialchars($key))) . ":</strong> " . htmlspecialchars($value) . "</div>";
      }
    }
    ?>
  </div>

  <div class="chart-section">
    <h2>Data Visualization</h2>
    <canvas id="reportChart" width="400" height="200"></canvas>
  </div>

  <div class="print-btn">
    <button onclick="window.print()">🖨️ Print Report</button>
  </div>
</div>

<script>
  const ctx = document.getElementById('reportChart').getContext('2d');

  // Example chart data — you can customize this dynamically later
  const reportType = "<?= $_POST['report_type'] ?>";
  let chartData = {
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [{
      label: "Sample Data",
      data: [12, 19, 3, 5, 2, 3],
      backgroundColor: "#26a69a"
    }]
  };

  if (reportType === 'closed_pending') {
    chartData = {
      labels: ["Closed", "Pending"],
      datasets: [{
        label: "Work Orders",
        data: [80, 20],
        backgroundColor: ["#4caf50", "#f44336"]
      }]
    };
  } else if (reportType === 'external_internal') {
    chartData = {
      labels: ["Internal", "External"],
      datasets: [{
        label: "WO Distribution",
        data: [60, 40],
        backgroundColor: ["#2196f3", "#ff9800"]
      }]
    };
  }
  // Add more conditions for other report types as needed...

  new Chart(ctx, {
    type: 'bar', // Change to 'pie', 'line' etc. depending on the report
    data: chartData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        },
        title: {
          display: true,
          text: 'Report Data Overview'
        }
      }
    }
  });
</script>

</body>
</html>
