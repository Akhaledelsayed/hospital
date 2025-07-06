<?php
session_start();
include "DB_connection.php";

$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (!$hospital_code) {
    echo "<h2 style='color:red;text-align:center;'>❌ Access denied.</h2>";
    exit;
}

$query = "SELECT device_name, total_pm FROM preventive_maintenance_plan WHERE hospital_code = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$hospital_code]);
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

$devices_pool = [];
foreach ($plans as $plan) {
    for ($i = 0; $i < $plan['total_pm']; $i++) {
        $devices_pool[] = $plan['device_name'];
    }
}

shuffle($devices_pool);
$monthly_devices = array_fill(1, 12, []);

$index = 0;
foreach ($devices_pool as $device) {
    $month = ($index % 12) + 1;
    $monthly_devices[$month][] = $device;
    $index++;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Monthly Preventive Maintenance Summary</title>
    <style>
        table { border-collapse: collapse; width: 70%; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; vertical-align: top; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Monthly Preventive Maintenance Summary</h2>
    <table>
        <tr>
            <th>Month</th>
            <th>Devices</th>
            <th>Count</th>
        </tr>
        <?php for ($m = 1; $m <= 12; $m++): ?>
        <tr>
            <td><?php echo $m; ?></td>
            <td><?php echo implode("<br>", $monthly_devices[$m]); ?></td>
            <td><?php echo count($monthly_devices[$m]); ?></td>
        </tr>
        <?php endfor; ?>
    </table>
</body>
</html>
