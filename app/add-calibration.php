<?php
session_start();

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id'], $_SESSION['username']) || !$hospital_code) {
    $em = "❌ Access denied. Please log in.";
    header("Location: ../login.php?error=$em");
    exit;
}

include "../DB_connection.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id        = $_POST['plan_id'] ?? null;
    $device_serial  = $_POST['device_serial'] ?? null;
    $device_name    = $_POST['device_name'] ?? null;
    $model          = $_POST['model'] ?? null;
    $department     = $_POST['department'] ?? null;
    $engineer_name  = $_POST['em_name'] ?? null;
    $month          = $_POST['month'] ?? null;

    $calibration_date = date('Y-m-d');

    // تأكد أن الحقول المهمة موجودة
    if ($plan_id && $device_serial && $device_name && $engineer_name && $month) {
        try {
            // 1. إدخال سجل المعايرة
            $sql = "INSERT INTO calibration 
                    (plan_id, device_serial, device_name, model, department, calibration_date, en_name) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $plan_id,
                $device_serial,
                $device_name,
                $model ?: null,
                $department ?: null,
                $calibration_date,
                $engineer_name
            ]);

            // 3. إعادة التوجيه بنجاح
            header("Location: ../calibration-user.php?success=1");
            exit;

        } catch (PDOException $e) {
            echo "<h3 style='color:red; text-align:center;'>❌ فشل إدخال المعايرة: " . $e->getMessage() . "</h3>";
        }
    } else {
        echo "<h3 style='color:red; text-align:center;'>❌ يجب إدخال الرقم التسلسلي واسم الجهاز واسم المهندس والشهر.</h3>";
    }
} else {
    header("Location: ../calibration-user.php");
    exit;
}
