<?php
include 'DB_connection.php';

$record_id = $_GET['record_id'] ?? null;

if (!$record_id) {
    echo "<h2 style='color:red;'>No record selected</h2>";
    exit;
}

// بيانات المعايرة
$stmt = $conn->prepare("SELECT cr.*, d.device_name, d.Model, d.Department, d.Manufacturer, d.BMD_Code, d.Floor 
                        FROM calibration_records cr
                        JOIN Devices d ON d.Serial_number = cr.device_serial
                        WHERE cr.id = ?");
$stmt->execute([$record_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    echo "<h2 style='color:red;'>Record not found</h2>";
    exit;
}

// بنود المعايرة
$stmt = $conn->prepare("SELECT ti.item_name, cr.actual_result
                        FROM calibration_results cr
                        JOIN template_items ti ON ti.id = cr.item_id
                        WHERE cr.record_id = ?");
$stmt->execute([$record_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PM/Checked Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; direction: ltr; }
        .certificate { width: 100%; max-width: 1000px; margin: auto; border: 2px solid #000; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .title { text-align: center; font-weight: bold; font-size: 24px; width: 100%; margin: 10px 0; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 16px; }
        .info-grid p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 16px; }
        th { background-color: #f0f0f0; }
        .footer { display: flex; justify-content: space-between; margin-top: 30px; font-size: 16px; }
        @media print {
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
<div class="certificate">
    <div class="header">
        <img src="welcare_logo.png" alt="Welcare Logo" height="50">
        <div class="title">PM/Checked Certificate</div>
        <img src="mpc_logo.png" alt="MPC Logo" height="50">
    </div>

    <div class="info-grid">
        <p><strong>Device:</strong> <?= htmlspecialchars($record['device_name']) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($record['Department']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($record['date']) ?></p>

        <p><strong>Model:</strong> <?= htmlspecialchars($record['Model']) ?></p>
        <p><strong>Floor:</strong> <?= htmlspecialchars($record['Floor']) ?></p>
        <p><strong>Due:</strong> <?= date('Y-m-d', strtotime($record['date'] . ' +6 months')) ?></p>

        <p><strong>Serial:</strong> <?= htmlspecialchars($record['device_serial']) ?></p>
        <p><strong>Code:</strong> <?= htmlspecialchars($record['BMD_Code']) ?></p>
        <p><strong>Time:</strong> <?= date('H:i', strtotime($record['date'])) ?></p>

        <p><strong>Manufacturer:</strong> <?= htmlspecialchars($record['Manufacturer']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($record['status']) ?></p>
        <p><strong>Freq:</strong> Every 6 months</p>
    </div>

    <table>
        <thead>
            <tr><th colspan="6">QUALITATIVE TASKS</th></tr>
            <tr>
                <th>✔ Pass</th><th>❌ Fail</th><th>Task</th>
                <th>✔ Pass</th><th>❌ Fail</th><th>Task</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < count($items); $i += 2): ?>
                <tr>
                    <?php for ($j = 0; $j < 2; $j++): ?>
                        <?php if (isset($items[$i + $j])): 
                            $item = $items[$i + $j]; ?>
                            <td><?= $item['actual_result'] === 'Pass' ? '✔️' : '' ?></td>
                            <td><?= $item['actual_result'] === 'Fail' ? '❌' : '' ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <?php else: ?>
                            <td></td><td></td><td></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="footer">
        <div><strong>Performed By:</strong> <?= htmlspecialchars($record['performed_by']) ?></div>
        <div><strong>Approved By:</strong> <?= htmlspecialchars($record['approved_by']) ?></div>
        <div><strong>Checked By:</strong> <?= htmlspecialchars($record['checked_by']) ?></div>
    </div>
</div>
<script>
    window.onload = () => window.print();
</script>
</body>
</html>