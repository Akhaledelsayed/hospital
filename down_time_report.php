<?php
// الاتصال بقاعدة البيانات
include "DB_connection.php";

if (!isset($_GET['start_date']) || !isset($_GET['end_date']) || !isset($_GET['hospital_code'])) {
    die("Please provide start date, end date, and hospital code.");
}

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$hospital_code = $_GET['hospital_code'];

// استعلام لحساب إجمالي الداون تايم وعدد الأجهزة والمتوسط (داخلي وخارجي فقط)
$query = "
SELECT 
  'Internal' AS repair_type,
  ROUND(SUM(w.downtime_duration)/60, 2) AS total_downtime_hours,
  COUNT(DISTINCT w.device_serial) AS device_count,
  ROUND(SUM(w.downtime_duration)/COUNT(DISTINCT w.device_serial)/60, 2) AS avg_downtime_per_device_hours
FROM workorders w
JOIN devices d ON w.device_serial = d.serial_number
WHERE w.downtime_duration IS NOT NULL
  AND d.hospital_code = :hospital_code
  AND LOWER(TRIM(w.contacted_manufacturer)) = 'no'
  AND TRIM(w.inhouse_fixed_by) != ''
  AND (
        w.start_date BETWEEN :start_date AND :end_date
        OR w.end_date BETWEEN :start_date AND :end_date
        OR (w.start_date <= :start_date AND (w.end_date >= :end_date OR w.end_date IS NULL))
)

UNION ALL

SELECT 
  'External' AS repair_type,
  ROUND(SUM(w.downtime_duration)/60, 2) AS total_downtime_hours,
  COUNT(DISTINCT w.device_serial) AS device_count,
  ROUND(SUM(w.downtime_duration)/COUNT(DISTINCT w.device_serial)/60, 2) AS avg_downtime_per_device_hours
FROM workorders w
JOIN devices d ON w.device_serial = d.serial_number
WHERE w.downtime_duration IS NOT NULL
  AND d.hospital_code = :hospital_code
  AND LOWER(TRIM(w.contacted_manufacturer)) = 'yes'
  AND TRIM(w.outhouse_fixed_by) != ''
  AND (
        w.start_date BETWEEN :start_date AND :end_date
        OR w.end_date BETWEEN :start_date AND :end_date
        OR (w.start_date <= :start_date AND (w.end_date >= :end_date OR w.end_date IS NULL))
)
";

$stmt = $conn->prepare($query);
$stmt->execute([
    ':hospital_code' => $hospital_code,
    ':start_date' => $start_date,
    ':end_date' => $end_date
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Downtime Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fc;
      margin: 0;
      padding: 40px;
      color: #343a40;
    }
    
.header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            width: 150px;
            height: 70px;
            object-fit: contain;
        }
    h2 {
      color: #4e73df;
      margin-bottom: 10px;
    }
    .info-box {
      background: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.08);
      padding: 20px;
      max-width: 900px;
      margin: 0 auto 30px auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    table, th, td {
      border: 1px solid #dee2e6;
    }
    th, td {
      padding: 12px;
      text-align: center;
    }
    th {
      background-color: #e9ecef;
      font-weight: bold;
    }
    tr:nth-child(even) {
      background-color: #f1f3f5;
    }
    canvas {
      margin-top: 40px;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div class="info-box">
  
<div class="header">
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo1')">
        <img src="logo1.png" id="logo1" class="logo" alt="Logo 1">
    </div>
    <h2>PM Checked Certificate</h2>
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo2')">
        <img src="logo2.png" id="logo2" class="logo" alt="Logo 2">
    </div>
</div>
  <h2>Average Downtime Report</h2>
  <p><strong>Period:</strong> <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?></p>
  <p><strong>Hospital Code:</strong> <?= htmlspecialchars($hospital_code) ?></p>

  <table>
    <thead>
      <tr>
        <th>Repair Type</th>
        <th>Total Downtime (Hours)</th>
        <th>Device Count</th>
        <th>Average Downtime / Device (Hours)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['repair_type']) ?></td>
          <td><?= $row['total_downtime_hours'] ?></td>
          <td><?= $row['device_count'] ?></td>
          <td><?= $row['avg_downtime_per_device_hours'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<canvas id="downtimeChart" width="600" height="300"></canvas>

<script>
  
    
    function previewLogo(input, logoId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(logoId).src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

const ctx = document.getElementById('downtimeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
          <?php foreach ($data as $row) echo "'" . $row['repair_type'] . "',"; ?>
        ],
        datasets: [
          {
            label: 'Average Downtime (Hours)',
            data: [
              <?php foreach ($data as $row) echo $row['avg_downtime_per_device_hours'] . ","; ?>
            ],
            backgroundColor: ['#0077b6', '#ff6f61'],
            borderColor: '#dee2e6',
            borderWidth: 1
          }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Avg Downtime per Device by Repair Type',
                font: {
                    size: 18
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(9)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>