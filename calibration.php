<?php
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;
include "DB_connection.php";

// التأكد من الدخول
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// جلب أسماء الأجهزة وعدد كل جهاز حسب الـ device_name
$query = "SELECT device_name, COUNT(serial_number) AS serial_count
          FROM devices
          WHERE hospital_code = $hospital_code
          GROUP BY device_name
          ORDER BY device_name ASC";


$stmt = $conn->prepare($query);
$stmt->execute();
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            text-align: center;
            border: 1px solid #ccc;
        }
        th {
            background: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Device Summary by Name</h2>
    <table>
        <thead>
            <tr>
                <th>Device Name</th>
                <th>Count of Devices</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($devices) > 0): ?>
                <?php foreach ($devices as $device): ?>
                    <tr>
                        <td><?= htmlspecialchars($device['device_name']) ?></td>
                        <td><?= $device['serial_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2">No devices found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
