<?php
session_start();
include "DB_connection.php";

function get_new_devices_between_dates($conn, $hospital_code, $from, $to) {
    $sql = "SELECT * FROM devices 
            WHERE hospital_code = ?
              AND Arrival_Date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code, $from, $to]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!$hospital_code) {
    echo "<h3 style='color:red;text-align:center;'>❌ غير مصرح بالدخول</h3>";
    exit;
}

$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');

$devices = get_new_devices_between_dates($conn, $hospital_code, $from_date, $to_date);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير الأجهزة الجديدة</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px 15px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #667eea;
            color: white;
        }
        h2 {
            text-align: center;
            margin-top: 40px;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            padding: 8px 16px;
            background-color: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a67d8;
        }
    </style>
</head>
<body>
    <?php include "inc/header.php"; ?>
    <div class="body">
        <?php include "inc/nav.php"; ?>

        <h2>تقرير الأجهزة الجديدة</h2>

        <form method="get">
            <label>من: <input type="date" name="from" value="<?= htmlspecialchars($from_date) ?>" required></label>
            <label>إلى: <input type="date" name="to" value="<?= htmlspecialchars($to_date) ?>" required></label>
            <button type="submit">عرض التقرير</button>
        </form>

        <?php if (count($devices) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الجهاز</th>
                        <th>الموديل</th>
                        <th>تاريخ الوصول</th>
                        <th>تاريخ التركيب</th>
                        <th>الشركة</th>
                        <th>created_by</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $index => $d): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($d['device_name']) ?></td>
                            <td><?= htmlspecialchars($d['Model']) ?></td>
                            <td><?= htmlspecialchars($d['Arrival_Date']) ?></td>
                            <td><?= htmlspecialchars($d['Installation_Date']) ?></td>
                            <td><?= htmlspecialchars($d['Company']) ?></td>
                            <td><?= htmlspecialchars($d['assigned_user']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:red;">لا توجد أجهزة مضافة في الفترة المحددة.</p>
        <?php endif; ?>
    </div>
    <script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(9)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
