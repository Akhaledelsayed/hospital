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
    $current_month = date('n');

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
    <title>خطط المعايرة الشهرية</title>
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
    <h2 class="title">خطط المعايرة الشهرية - شهر <?= date('F') ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="success">✅ تم إضافة المعايرة بنجاح!</p>
    <?php endif; ?>

    <div class="summary-box">
        عدد الأجهزة المجدولة: <?= $total_quantity ?> |
        عدد المعايرات المنفذة: <?= $total_calibrated ?> |
        المتبقي: <?= $total_remaining ?>
    </div>

    <?php if ($count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الجهاز</th>
                    <th>الكمية الأصلية</th>
                    <th>عدد المعايرات المنفذة</th>
                    <th>عدد المتبقي</th>
                    <th>المجموع الكلي (سنوي)</th>
                    <th>شهر المعايرة</th>
                    <th>إجراء</th>
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
                            <a class="button" href="add-calibration.php?plan_id=<?= $plan['id'] ?>">إضافة معايرة</a>
                        <?php else: ?>
                            <span style="color: gray;">تمت كل المعايرات</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#888;">لا توجد أجهزة بحاجة للمعايرة هذا الشهر.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php
