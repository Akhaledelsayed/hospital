<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id']) || !$hospital_code) {
    echo "<h2 style='text-align:center; color:red;'>❌ Access denied. Please log in.</h2>";
    exit;
}

include "DB_connection.php";
include "app/Model/Devices.php";

// Access control logic
$role = $_SESSION['role'];
$user_id = $_SESSION['id'];
$hasAccess = false;

if ($role === "admin") {
    $hasAccess = true;
} else {
    // Check if user is assigned to this hospital
    $stmt = $conn->prepare("SELECT hospital_code FROM user_hospitals WHERE username = (SELECT username FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $user_hospitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array($hospital_code, $user_hospitals)) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    echo "<h2 style='text-align:center; color:red;'>❌ You are not assigned to this hospital.</h2>";
    exit;
}
    $current_month = 12;

    // دالة لجلب خطط الصيانة لهذا الشهر
    

    $plans = get_current_month_pm_plans($conn, $hospital_code, $current_month);
    $count = count($plans);

    $total_quantity = 0;
    $total_calibrated = 0;
    $total_remaining = 0;

    // حساب عدد المعايرات لكل خطة
    foreach ($plans as &$plan) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM calibration WHERE plan_id = ?");
        $stmt->execute([$plan['id']]);
        $done = (int)$stmt->fetchColumn();

        $plan['calibrated'] = $done;
        $plan['remaining'] = max(0, $plan['quantity'] - $done);

        // إجماليات
        $total_quantity += $plan['quantity'];
        $total_calibrated += $plan['calibrated'];
        $total_remaining += $plan['remaining'];
    }
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Monthly Calibrations Plans  </title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        body { font-family: 'Arial'; background: #f9f9f9; direction: ltr; }
        .container { background: white; padding: 20px; border-radius: 10px; max-width: 1200px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #4CAF50; color: white; }
        .title { font-size: 24px; margin-bottom: 10px; text-align: center; }
        .success { color: green; font-weight: bold; }
        a.button {
            background-color: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
        }
        .summary-box {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>       
     <?php include "inc/header.php"; ?>

    <div class="body">
        <?php include "inc/nav.php"; ?>
        
<div class="container">
    <h2 class="title">Monthly Calibrations Plans   - Month <?= date('F') ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="success"> Add New Calibration Is Done ✅</p>
    <?php endif; ?>

    <div class="summary-box">
        The Number Of Devices: <?= $total_quantity ?> |
        Performed Calibration: <?= $total_calibrated ?> |
        The Remaining: <?= $total_remaining ?>
    </div>

    <?php if ($count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th> Device Name</th>
                    <th> Quantity</th>
                    <th> Performed Calibrations</th>
                    <th>The Remaining</th>
                    <th>Total PM</th>
                    <th>Calibration Month </th>
                    <th>Procedure</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; foreach ($plans as $plan): ?>
                <tr>
                    <td><?= ++$i ?></td>
                    <td><?= htmlspecialchars($plan['device_name']) ?></td>
                    <td><?= $plan['quantity'] ?></td>
                    <td><?= $plan['calibrated'] ?></td>
                    <td><?= $plan['remaining'] ?></td>
                    <td><?= $plan['total_pm'] ?></td>
                    <td><?= $plan['calibration_month'] ?></td>
                    <td>
                        <?php if ($plan['remaining'] > 0): ?>
                            <!-- زر لفتح النافذة -->
                            <button class="button" onclick="openCalibrationModal(<?= $plan['id'] ?>)">Add Calibration </button>
                        <?php endif; ?>

                        <!-- النافذة المنبثقة (Popup Modal) -->
                        <div id="calibrationModal" class="modal">
                            <div class="modal-content">
                                <span class="close" onclick="closeCalibrationModal()">&times;</span>
                                <h2>Selecte Type Of Calibration </h2>
                                <p>Please Select If You Nedd Internal Calibtation Or External Calibration</p>
                                <div class="modal-buttons">
                                    <button id="internalBtn" class="modal-btn">📌 Internal Calibration</button>
                                    <button id="externalBtn" class="modal-btn">🌐 External Calibration </button>
                                </div>
                            </div>
                        </div>

                        <style>
                        /* مظهر النافذة المنبثقة */
                        .modal {
                            display: none;
                            position: fixed;
                            z-index: 9999;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            overflow: auto;
                            background-color: rgba(0,0,0,0.5);
                        }

                        .modal-content {
                            background-color: #fff;
                            margin: 10% auto;
                            padding: 30px;
                            border-radius: 15px;
                            width: 90%;
                            max-width: 400px;
                            text-align: center;
                            box-shadow: 0px 0px 15px #333;
                        }

                        .close {
                            color: #aaa;
                            float: right;
                            font-size: 28px;
                            font-weight: bold;
                            cursor: pointer;
                        }

                        .modal-buttons {
                            margin-top: 20px;
                        }

                        .modal-btn {
                            padding: 10px 20px;
                            margin: 10px;
                            border: none;
                            background-color: #007bff;
                            color: white;
                            border-radius: 10px;
                            cursor: pointer;
                            font-size: 16px;
                            transition: background-color 0.3s;
                        }

                        .modal-btn:hover {
                            background-color: #0056b3;
                        }
                        </style>

                        <script>
                        let currentPlanId = null;

                        function openCalibrationModal(planId) {
                            currentPlanId = planId;
                            document.getElementById("calibrationModal").style.display = "block";
                        }

                        function closeCalibrationModal() {
                            document.getElementById("calibrationModal").style.display = "none";
                        }

                        document.getElementById("internalBtn").addEventListener("click", function() {
                            window.location.href = "add-calibration.php?plan_id=" + currentPlanId;
                        });

                        document.getElementById("externalBtn").addEventListener("click", function() {
                            window.open("external-calibration.php?plan_id=" + currentPlanId, "_blank");
                        });
                        </script>


                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#888;">There is No Devices Need To calibrated This Month.</p>
    <?php endif; ?>
</div>
<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(7)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
<?php
