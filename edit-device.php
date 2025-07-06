<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    include "DB_connection.php";
    include "app/Model/Devices.php";

    if (!isset($_GET['serial_number'])) {
        header("Location: device.php");
        exit;
    }

    $serial = $_GET['serial_number'];
    $device = get_device_by_serial($conn, $serial); // ✅ تأكد أن هذه الدالة موجودة

    if (!$device) {
        echo "Device not found.";
        exit;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Device</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        form {
            max-width: 900px;
            margin: auto;
            background: #f4f6f9;
            padding: 25px;
            border-radius: 10px;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .btn {
            margin-top: 20px;
            padding: 10px 16px;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        h2 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>

<?php include "inc/header.php"; ?>
<div class="body">
<?php include "inc/nav.php"; ?>
<section class="section-1">
    <h2>Edit Device</h2>
    <form action="app/update_device.php" method="post">
        <input type="hidden" name="original_serial" value="<?= htmlspecialchars($device['Serial_number']) ?>">

        <div class="form-group">
            <label>Serial Number</label>
            <input type="text" name="serial_number" value="<?= htmlspecialchars($device['Serial_number']) ?>" required>
        </div>

        <div class="form-group">
            <label>Floor</label>
            <input type="text" name="floor" value="<?= htmlspecialchars($device['Floor']) ?>">
        </div>

        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" value="<?= htmlspecialchars($device['Department']) ?>">
        </div>

        <div class="form-group">
            <label>Department Now</label>
            <input type="text" name="department_now" value="<?= htmlspecialchars($device['Department_now']) ?>">
        </div>

        <div class="form-group">
            <label>Room</label>
            <input type="text" name="room" value="<?= htmlspecialchars($device['Room']) ?>">
        </div>

        <div class="form-group">
            <label>Device Name</label>
            <input type="text" name="device_name" value="<?= htmlspecialchars($device['device_name']) ?>">
        </div>

        <div class="form-group">
            <label>Accessories</label>
            <input type="text" name="accessories" value="<?= htmlspecialchars($device['Accessories']) ?>">
        </div>

        <div class="form-group">
            <label>Manufacturer</label>
            <input type="text" name="manufacturer" value="<?= htmlspecialchars($device['Manufacturer']) ?>">
        </div>

        <div class="form-group">
            <label>Origin</label>
            <input type="text" name="origin" value="<?= htmlspecialchars($device['Origin']) ?>">
        </div>

        <div class="form-group">
            <label>Company</label>
            <input type="text" name="company" value="<?= htmlspecialchars($device['Company']) ?>">
        </div>

        <div class="form-group">
            <label>Model</label>
            <input type="text" name="model" value="<?= htmlspecialchars($device['Model']) ?>">
        </div>

        <div class="form-group">
            <label>QT</label>
            <input type="text" name="qt" value="<?= htmlspecialchars($device['QT']) ?>">
        </div>

        <div class="form-group">
            <label>BMD Code</label>
            <input type="text" name="bmd_code" value="<?= htmlspecialchars($device['BMD_Code']) ?>">
        </div>

        <div class="form-group">
            <label>Arrival Date</label>
            <input type="date" name="arrival_date" value="<?= htmlspecialchars($device['Arrival_Date']) ?>">
        </div>

        <div class="form-group">
            <label>Installation Date</label>
            <input type="date" name="installation_date" value="<?= htmlspecialchars($device['Installation_Date']) ?>">
        </div>

        <div class="form-group">
            <label>Purchasing Order Date</label>
            <input type="date" name="purchaseorder_date" value="<?= htmlspecialchars($device['purchaseorder_date']) ?>">
        </div>

        <div class="form-group">
            <label>Price</label>
            <input type="number" name="price" value="<?= htmlspecialchars($device['Price']) ?>">
        </div>

        <div class="form-group">
            <label>Warranty Period</label>
            <input type="text" name="warranty_period" value="<?= htmlspecialchars($device['Warranty_Period']) ?>">
        </div>

        <div class="form-group">
            <label>Warranty Start</label>
            <input type="date" name="warranty_start" value="<?= htmlspecialchars($device['warranty_start']) ?>">
        </div>

        <div class="form-group">
            <label>Warranty End</label>
            <input type="date" name="warranty_end" value="<?= htmlspecialchars($device['warranty_end']) ?>">
        </div>

        <div class="form-group">
            <label>Company Contact</label>
            <input type="text" name="company_contact" value="<?= htmlspecialchars($device['company_contact']) ?>">
        </div>

        <div class="form-group">
            <label>Company Telephone</label>
            <input type="text" name="company_telephone" value="<?= htmlspecialchars($device['company_Tel']) ?>">
        </div>

        <div class="form-group">
            <label>Device Safety Test</label>
            <input type="text" name="device_safety_test" value="<?= htmlspecialchars($device['device_safety_test']) ?>">
        </div>

        <button type="submit" class="btn">Update Device</button>
    </form>
</section>
</div>
</body>
</html>

<?php
} else {
    header("Location: login.php");
    exit;
}
?>
