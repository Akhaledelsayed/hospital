<?php
include "DB_connection.php";

if (!isset($_GET['start_date']) || !isset($_GET['end_date']) || !isset($_GET['hospital_code'])) {
    die("Please provide start date, end date, and hospital code.");
}

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$hospital_code = $_GET['hospital_code'];

// استعلام لإظهار كل الأقسام حتى لو مفيش أعطال
$query = "
SELECT 
    TRIM(LOWER(d.department_now)) AS raw_department,
    COUNT(w.id) AS fault_count
FROM devices d
LEFT JOIN workorders w 
    ON d.serial_number = w.device_serial
    AND (
        w.start_date BETWEEN :start_date AND :end_date
     OR w.end_date BETWEEN :start_date AND :end_date
     OR (w.start_date <= :start_date AND (w.end_date >= :end_date OR w.end_date IS NULL))
    )
WHERE d.hospital_code = :hospital_code
GROUP BY raw_department
ORDER BY fault_count DESC
";

$stmt = $conn->prepare($query);
$stmt->execute([
    ':hospital_code' => $hospital_code,
    ':start_date' => $start_date,
    ':end_date' => $end_date
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$departments = [];
$faultCounts = [];

foreach ($data as $row) {
    $dept = ucwords($row['raw_department']); // أول حرف كابيتال
    if (!empty($dept)) {
        $departments[] = $dept;}
    $faultCounts[] = $row['fault_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faults by Department</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 40px;
            color: #333;
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

        .info-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 25px;
            max-width: 900px;
            margin: auto;
            margin-bottom: 40px;
        }

        h2 {
            color: #4e73df;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #e3eafc;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .chart-container {
            width: 500px;
            height: 500px;
            margin: auto;
        }

        canvas {
            width: 100% !important;
            height: auto !important;
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
    <h2>Faults by Department</h2>
    <p><strong>Period:</strong> <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?></p>
    <p><strong>Hospital Code:</strong> <?= htmlspecialchars($hospital_code) ?></p>

    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Fault Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($departments as $index => $dept): ?>
                <tr>
                    <td><?= htmlspecialchars($dept) ?></td>
                    <td><?= $faultCounts[$index] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="chart-container">
    <canvas id="pieChart"></canvas>
</div>

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

const ctx = document.getElementById('pieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($departments) ?>,
        datasets: [{
            data: <?= json_encode($faultCounts) ?>,
        backgroundColor: [
                '#4e79a7', '#f28e2b', '#e15759', '#76b7b2',
                '#59a14f', '#edc948', '#b07aa1', '#ff9da7',
                '#9c755f', '#bab0ab', '#1f77b4', '#ff7f0e',
                '#2ca02c', '#d62728', '#9467bd', '#8c564b',
                '#e377c2', '#7f7f7f', '#bcbd22', '#17becf',
                '#6baed6', '#fd8d3c', '#74c476', '#9e9ac8',
                '#fcbba1', '#fdd0a2', '#a1d99b', '#bcbddc',
                '#c7e9c0', '#f0f0f0', '#c49c94', '#aec7e8'
                ],


            borderWidth: 1,
            borderColor: '#fff'
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Fault Distribution by Department',
                font: { size: 18 }
            },
            legend: {
                position: 'bottom',
                labels: {
                    color: '#333',
                    boxWidth: 20,
                    padding: 15
                }
            }
        },
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(9)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
