<?php
session_start();
include 'DB_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = $_POST['plan_id'];
    $set_id = $_POST['set_id'];
    $device_serial = $_POST['device_serial'];
    $status = $_POST['status'];
    $performed_by = $_POST['performed_by'];
    $approved_by = $_POST['approved_by'];
    $checked_by = $_POST['checked_by'];
    $date = date('Y-m-d H:i:s');


    try {
        $conn->beginTransaction();

        // Insert into calibration_records
        $stmt = $conn->prepare("INSERT INTO calibration_records (device_serial, set_id, performed_by, approved_by, checked_by, date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $device_serial, $set_id, $performed_by, $approved_by, $checked_by, $date, $status
        ]);

        $record_id = $conn->lastInsertId();

        // Save results
        if (isset($_POST['result']) && is_array($_POST['result'])) {
            $stmt = $conn->prepare("INSERT INTO calibration_results (record_id, item_id, actual_result) VALUES (?, ?, ?)");
            foreach ($_POST['result'] as $item_id => $actual_result) {
                $stmt->execute([$record_id, $item_id, $actual_result]);
            }
        }

        // Link to calibration table
        $stmt = $conn->prepare("INSERT INTO calibration (plan_id, device_serial, device_name, model, department, calibration_date, en_name) 
                                SELECT ?, Serial_number, device_name, Model, Department, ?, ? FROM Devices WHERE Serial_number = ?");
        $stmt->execute([$plan_id, $date, $performed_by, $device_serial]);

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "<h2 style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
        exit;
    }

    // Now display and print the certificate inline

    // Fetch record info
    $stmt = $conn->prepare("SELECT cr.*, d.device_name, d.Model, d.Department, d.Manufacturer, d.BMD_Code, d.Floor 
                            FROM calibration_records cr
                            JOIN Devices d ON d.Serial_number = cr.device_serial
                            WHERE cr.id = ?");
    $stmt->execute([$record_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch items
    $stmt = $conn->prepare("SELECT ti.item_name, cr.actual_result
                            FROM calibration_results cr
                            JOIN template_items ti ON ti.id = cr.item_id
                            WHERE cr.record_id = ?");
    $stmt->execute([$record_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch PM frequency from the plan
    $stmt = $conn->prepare("SELECT num_of_pm FROM preventive_maintenance_plan WHERE id = ?");
    $stmt->execute([$plan_id]);
    $pm = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_of_pm = $pm['num_of_pm'] ?? 1;

    $months_interval = 12 / max($num_of_pm, 1); // لحساب الفرق الشهري
    $freq_text = "Every " . $months_interval . " month" . ($months_interval > 1 ? "s" : "");


    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>PM/Checked Certificate</title>
        <style>
            @page {
                size: A4;
                margin: 20mm;
            }
            body { font-family: Arial, sans-serif; padding: 0; margin: 0; direction: ltr; }
            .certificate { width: 100%; max-width: 800px; margin: auto; border: 2px solid #000; padding: 20px; page-break-inside: avoid; }
            .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
            .title { text-align: center; font-weight: bold; font-size: 22px; width: 100%; margin: 10px 0; }
            .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 14px; }
            .info-grid p { margin: 4px 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
            th, td { border: 1px solid #000; padding: 6px; text-align: center; }
            th { background-color: #f0f0f0; }
            .footer { display: flex; justify-content: space-between; margin-top: 25px; font-size: 14px; }
            @media print {
    body {
        margin: 0;
        padding: 0;
    }

    @page {
        size: A4;
        margin: 20mm;
    }

    .certificate {
        margin: auto;
        page-break-inside: avoid;
        width: 100%;
        max-width: 800px;
        box-sizing: border-box;
    }
}

        </style>
    </head>
    <body>
    <div class="certificate">
        <div class="header">
            <img src="img/welcare.png" alt="Welcare Logo" height="50">
            <div class="title">PM/Checked Certificate</div>
            <img src="img/logo.png" alt="MPC Logo" height="50">
        </div>

        <div class="info-grid">
            <p><strong>Device:</strong> <?= htmlspecialchars($record['device_name']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($record['Department']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($record['date']) ?></p>

            <p><strong>Model:</strong> <?= htmlspecialchars($record['Model']) ?></p>
            <p><strong>Floor:</strong> <?= htmlspecialchars($record['Floor']) ?></p>
            <p><strong>Due:</strong> <?= date('Y-m-d', strtotime($record['date'] . ' +12 months')) ?></p>

            <p><strong>Serial:</strong> <?= htmlspecialchars($record['device_serial']) ?></p>
            <p><strong>Code:</strong> <?= htmlspecialchars($record['BMD_Code']) ?></p>
            <p><strong>Time:</strong> <?= date('H:i', strtotime($record['date'])) ?></p>


            <p><strong>Manufacturer:</strong> <?= htmlspecialchars($record['Manufacturer']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($record['status']) ?></p>
            <p><strong>Freq:</strong> <?= $freq_text ?></p>

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
    <script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(9)");
		if (active) active.classList.add("active");
	</script>
    </body>
    </html>

    <?php
    exit;
} else {
    echo "<h2 style='color:red'>Invalid request method.</h2>";
}
