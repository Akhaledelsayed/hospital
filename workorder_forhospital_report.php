<?php
// الاتصال بقاعدة البيانات
include "DB_connection.php";

// التحقق من البيانات المرسلة
if (!isset($_GET['start_date']) || !isset($_GET['end_date']) || !isset($_GET['hospital_code'])) {
    die("Please provide start date, end date, and hospital code.");
}

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$hospital_code = $_GET['hospital_code'];

// تنفيذ الاستعلام باستخدام PDO
$query = "
SELECT 
  COUNT(*) AS total,
  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
  SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS Pending,
  SUM(CASE WHEN  status = 'completed' AND contacted_manufacturer = 'No' AND inhouse_fixed_by <> '' THEN 1 ELSE 0 END) AS inhouse_repairs,
  SUM(CASE WHEN  status = 'completed' AND contacted_manufacturer = 'Yes' THEN 1 ELSE 0 END) AS contacted_manufacturer
FROM workorders w
JOIN devices d ON w.device_serial = d.serial_number
WHERE d.hospital_code = :hospital_code 
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

$data = $stmt->fetch(PDO::FETCH_ASSOC);
$completedPercentage = ($data['total'] ?? 0) > 0 ? round(($data['completed'] / $data['total']) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Orders Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 40px;
            color: #343a40;
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
    <h2>Work Orders Report</h2>
    <p><strong>Period:</strong> <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?></p>
    <p><strong>Hospital Code:</strong> <?= htmlspecialchars($hospital_code) ?></p>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Faults</td>
                <td><?= $data['total'] ?? 0 ?></td>
            </tr>
            <tr>
                <td>Completed</td>
                <td><?= $data['completed'] ?? 0 ?></td>
            </tr>
            <tr>
                <td>Pending</td>
                <td><?= $data['Pending'] ?? 0 ?></td>
            </tr>
            <tr>
                <td>In-house Repairs</td>
                <td><?= $data['inhouse_repairs'] ?? 0 ?></td>
            </tr>
            <tr>
                <td>Contacted Manufacturer</td>
                <td><?= $data['contacted_manufacturer'] ?? 0 ?></td>
            </tr>
            <tr>
                <td><strong>Completion Percentage</strong></td>
                <td><strong><?= $completedPercentage ?>%</strong></td>
            </tr>
        </tbody>
    </table>
</div>

<canvas id="reportChart" width="600" height="300"></canvas>

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

const ctx = document.getElementById('reportChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Total Faults', 'Completed', 'Pending', 'In-house Repairs', 'Contacted Manufacturer'],
        datasets: [{
            label: 'Work Orders Summary',
            data: [
                <?= $data['total'] ?? 0 ?>,
                <?= $data['completed'] ?? 0 ?>,
                <?= $data['Pending'] ?? 0 ?>,
                <?= $data['inhouse_repairs'] ?? 0 ?>,
                <?= $data['contacted_manufacturer'] ?? 0 ?>
            ],
            backgroundColor: [
                '#0E2148',
                '#483AA0',
                '#7965C1',
                '#C8ACD6',
                '#E3D095'
            ],
            borderColor: '#dee2e6',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Work Orders Breakdown',
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

</body>
</html>
