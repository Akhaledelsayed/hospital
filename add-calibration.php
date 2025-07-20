<?php
session_start();
include 'DB_connection.php';

$plan_id = $_GET['plan_id'] ?? null;
if (!$plan_id) die("Plan ID is required.");

// Fetch device name from plan
$stmt = $conn->prepare("SELECT device_name FROM preventive_maintenance_plan WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);
$device_name = $plan['device_name'] ?? '';

// Fetch serials for this device NOT already calibrated
$stmt = $conn->prepare("SELECT Serial_number, Model, Department, Manufacturer, BMD_Code, Floor 
    FROM Devices 
    WHERE device_name = ? AND Serial_number NOT IN (SELECT device_serial FROM calibration WHERE plan_id = ?)");
$stmt->execute([$device_name, $plan_id]);
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch template set
$stmt = $conn->prepare("SELECT * FROM template_sets WHERE device_type = ? AND category = 'PM' LIMIT 1");
$stmt->execute([$device_name]);
$templateSet = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch template items
$stmt = $conn->prepare("SELECT * FROM template_items WHERE set_id = ? ORDER BY id");
$stmt->execute([$templateSet['id']]);
$templateItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate qualitative and quantitative
$qualitativeItems = [];
$quantitativeItems = [];
foreach ($templateItems as $item) {
    if (strtolower($item['type']) === 'qualitative') $qualitativeItems[] = $item;
    else if (strtolower($item['type']) === 'quantitative') $quantitativeItems[] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PM/Checked Certificate</title>
<style>
    body { font-family: Arial, sans-serif; padding: 30px; background-color: #fff; }
    .certificate { width: 100%; max-width: 900px; margin: auto; border: 2px solid #000; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header img { height: 60px; }
    .title { text-align: center; font-size: 24px; font-weight: bold; margin-top: 10px; width: 100%; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
    .info-grid p { margin: 3px 0; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: center; }
    th { background-color: #f8f8f8; }
    .section-title { margin-top: 20px; font-weight: bold; text-align: center; font-size: 16px; }
    .footer { display: flex; justify-content: space-between; margin-top: 30px; font-size: 14px; }
    button { margin-top: 20px; padding: 10px 20px; font-size: 16px; }
    @media print {
        button, select, input[type="text"], input[type="radio"] { display: none !important; }
    }
</style>
<script>
function fillDeviceDetails() {
    const data = <?= json_encode($devices) ?>;
    const sn = document.getElementById('serial_select').value;
    const selected = data.find(d => d.Serial_number === sn);
    if (selected) {
        document.getElementById('model').innerText = selected.Model;
        document.getElementById('department').innerText = selected.Department;
        document.getElementById('manufacturer').innerText = selected.Manufacturer;
        document.getElementById('bmd_code').innerText = selected.BMD_Code;
        document.getElementById('floor').innerText = selected.Floor;

        const now = new Date();
        document.getElementById('time').innerText = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    }
}
</script>
</head>
<body>
<div class="certificate">
    <div class="header">
        <img src="img/welcare.png" alt="Welcare Logo">
        <div class="title">PM / Checked Certificate</div>
        <img src="img/logo.png" alt="MPC Logo">
    </div>

    <form action="save_template_results.php" method="post">
        <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan_id) ?>">
        <input type="hidden" name="set_id" value="<?= $templateSet['id'] ?>">

        <div class="info-grid">
            <p><strong>Device:</strong> <?= htmlspecialchars($device_name) ?></p>
            <p><strong>PM Date:</strong> <?= date('Y-m-d') ?></p>
            <p><strong>Department:</strong> <span id="department"></span></p>
            <p><strong>PM Due:</strong> <?= date('Y-m-d', strtotime('+12 months')) ?></p>
            <p><strong>Model:</strong> <span id="model"></span></p>
            <p><strong>Floor:</strong> <span id="floor"></span></p>
            <p>
                <label for="serial_select"><strong>S.N.:</strong></label>
                <select name="device_serial" id="serial_select" onchange="fillDeviceDetails()" required>
                    <option value="">-- Select Serial --</option>
                    <?php foreach ($devices as $dev): ?>
                        <option value="<?= $dev['Serial_number'] ?>"><?= $dev['Serial_number'] ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p><strong>Code:</strong> <span id="bmd_code"></span></p>
            <p><strong>Time:</strong> <span id="time"></span></p>
            <p><strong>Mfr:</strong> <span id="manufacturer"></span></p>
            <p><strong>Risk Level:</strong> <?= $templateSet['risk_level'] ?></p>
            <p><strong>PM Frequency:</strong> <?= $templateSet['frequency'] ?></p>
        </div>

        <div class="section-title">QUALITATIVE TASKS</div>
        <table>
            <thead>
                <tr><th>Pass</th><th>Fail</th><th>Task</th><th>Pass</th><th>Fail</th><th>Task</th></tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < count($qualitativeItems); $i += 2): ?>
                    <tr>
                        <?php for ($j = 0; $j < 2; $j++): ?>
                            <?php if (isset($qualitativeItems[$i + $j])): $item = $qualitativeItems[$i + $j]; ?>
                                <td><input type="radio" name="result[<?= $item['id'] ?>]" value="Pass" required></td>
                                <td><input type="radio" name="result[<?= $item['id'] ?>]" value="Fail"></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <?php else: ?><td colspan="3"></td><?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        
        <?php if (!empty($quantitativeItems)): ?>
    <div class="section-title">QUANTITATIVE TASKS</div>
    <table>
        <thead>
            <tr><th>Parameter</th><th>Set</th><th>Meas</th><th>Pass</th><th>Fail</th></tr>
        </thead>
        <tbody>
            <?php foreach ($quantitativeItems as $item): ?>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <tr>
                        <?php if ($i === 0): ?>
                            <td rowspan="3"><?= htmlspecialchars($item['item_name']) ?></td>
                        <?php endif; ?>
                        <td><input type="text" name="quantitative[<?= $item['id'] ?>][<?= $i ?>][set]"></td>
                        <td><input type="text" name="quantitative[<?= $item['id'] ?>][<?= $i ?>][meas]"></td>
                        <td><input type="radio" name="quantitative[<?= $item['id'] ?>][<?= $i ?>][result]" value="Pass" required></td>
                        <td><input type="radio" name="quantitative[<?= $item['id'] ?>][<?= $i ?>][result]" value="Fail"></td>
                    </tr>
                <?php endfor; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


        <p><strong>Status:</strong><br>
            <label><input type="radio" name="status" value="Passed" required> Passed</label>
            <label><input type="radio" name="status" value="Service required"> Service Required</label>
            <label><input type="radio" name="status" value="Removed"> Removed</label>
        </p>

        <div class="footer">
            <div><strong>In Charge:</strong> <input type="text" name="performed_by" required></div>
            <div><strong>Approved By:</strong> <input type="text" name="approved_by" required></div>
            <div><strong>Checked By:</strong> <input type="text" name="checked_by" required></div>
        </div>

        <button type="submit">💾 Save & Print</button>
    </form>
</div>
<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(7)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
