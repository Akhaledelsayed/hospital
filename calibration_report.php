<?php
session_start();
include "DB_connection.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit;
}

$hospital_code = $_SESSION['current_hospital_code'] ?? null;
$current_month = date('n');

// Get all PM plans for this hospital and month
$sql = "SELECT * FROM preventive_maintenance_plan 
        WHERE hospital_code = ? AND month_$current_month = 1";
$stmt = $conn->prepare($sql);
$stmt->execute([$hospital_code]);
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Calculate total devices by summing all quantities
$total_devices = array_sum(array_column($plans, 'quantity'));

$calibrated_count = 0;

foreach ($plans as $plan) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM calibration WHERE plan_id = ?");
    $stmt->execute([$plan['id']]);
    $count = $stmt->fetchColumn();

    // Add the number of calibrations (capped at quantity)
    $calibrated_count += min($count, $plan['quantity']);
}

$remaining = $total_devices - $calibrated_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calibration Report</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 20px;
        }
        .report-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            padding: 15px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .chart-container {
            margin: 50px auto;
            max-width: 700px;
        }
    </style>
</head>
<body>

<div class="report-box">
    <h2>Calibration Summary - <?= date('F') ?></h2>

    <table>
        <thead>
            <tr>
                <th>Total Scheduled Devices</th>
                <th>Calibrated Devices</th>
                <th>Remaining Devices</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $total_devices ?></td>
                <td><?= $calibrated_count ?></td>
                <td><?= $remaining ?></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Line Chart Section -->
<div class="chart-container">
    <canvas id="calibrationChart"></canvas>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('calibrationChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Scheduled', 'Calibrated', 'Remaining'],
            datasets: [{
                label: 'Calibration Progress',
                data: [<?= $total_devices ?>, <?= $calibrated_count ?>, <?= $remaining ?>],
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                tension: 0.3,
                fill: true,
                pointRadius: 6,
                pointBorderColor: '#388E3C',
                pointBackgroundColor: '#388E3C'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    stepSize: 1
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
