<?php
session_start();
include "DB_connection.php";

$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (!isset($_SESSION['role'], $_SESSION['id']) || !$hospital_code) {
    echo "<h2 style='text-align:center; color:red;'>❌ Access denied.</h2>";
    exit;
}

// جلب أسماء المستخدمين العاملين بالمستشفى
$stmt = $conn->prepare("SELECT username, full_name FROM users WHERE username IN 
    (SELECT username FROM user_hospitals WHERE hospital_code = ?)");
$stmt->execute([$hospital_code]);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عند إرسال النموذج
$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employee'], $_GET['start_date'], $_GET['end_date'])) {
    $employee = $_GET['employee'];
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];

    // عدد الأجهزة التي أضافها الموظف
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Devices 
        WHERE assigned_user = ? 
        AND Arrival_Date BETWEEN ? AND ? 
        AND hospital_code = ?");
    $stmt->execute([$employee, $start, $end, $hospital_code]);
    $results['devices_added'] = $stmt->fetchColumn();

    // عدد الأعطال التي أنشأها
    $stmt = $conn->prepare("SELECT COUNT(*) FROM workorders 
        WHERE created_by = ? 
        AND start_date BETWEEN ? AND ? 
        AND device_serial IN (SELECT serial_number FROM Devices WHERE hospital_code = ?)");
    $stmt->execute([$employee, $start, $end, $hospital_code]);
    $results['workorders'] = $stmt->fetchColumn();

    // عدد المعايرات التي قام بها
    $stmt = $conn->prepare("SELECT COUNT(*) FROM calibration 
        WHERE en_name = ? 
        AND calibration_date BETWEEN ? AND ? 
        AND device_serial IN (SELECT serial_number FROM Devices WHERE hospital_code = ?)");
    $stmt->execute([$employee, $start, $end, $hospital_code]);
    $results['calibrations'] = $stmt->fetchColumn();

    // عدد خطط الصيانة التي أعدها
   
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>تقرير الموظف</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; direction: rtl; }
    
.header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            width: 150px;
            height: 70px;
            object-fit: contain;
        }
    .container { background: #fff; padding: 30px; border-radius: 10px; max-width: 1000px; margin: auto; box-shadow: 0 8px 30px rgba(0,0,0,0.1);}
    h2 { text-align: center; margin-bottom: 20px; }
    form { margin-bottom: 30px; }
    label { display: block; margin: 10px 0 5px; font-weight: bold; }
    select, input[type="date"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
    button { padding: 10px 20px; background: #764ba2; color: white; border: none; border-radius: 6px; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
    th { background: #667eea; color: #fff; }
    .section-title { margin-top: 40px; font-size: 20px; color: #444; }
  </style>
</head>
<body>
  
<div class="header">
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo1')">
        <img src="logo1.png" id="logo1" class="logo" alt="Logo 1">
    </div>
    <h2>PM Checked Certificate</h2>
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo2')">
        <img src="logo2.png" id="logo2" class="logo" alt="Logo 2">
    </div>
</div>
  <div class="container">
    <h2>تقرير أداء الموظف داخل المستشفى</h2>
    <form method="GET">
      <label>اختر الموظف:</label>
      <select name="employee" required>
        <option value="">-- اختر --</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= htmlspecialchars($emp['username']) ?>" 
            <?= (isset($_GET['employee']) && $_GET['employee'] == $emp['username']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($emp['full_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>من تاريخ:</label>
      <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>" required>

      <label>إلى تاريخ:</label>
      <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>" required>

      <br><br>
      <button type="submit">عرض التقرير</button>
    </form>

    <?php if (!empty($results)): ?>
      <div class="section-title">ملخص أداء الموظف:</div>
      <table>
        <tr><th>العنصر</th><th>العدد</th></tr>
        <tr><td>الأجهزة التي أضافها</td><td><?= $results['devices_added'] ?></td></tr>
        <tr><td>أوامر الأعطال التي أنشأها</td><td><?= $results['workorders'] ?></td></tr>
        <tr><td>المعايرات التي قام بها</td><td><?= $results['calibrations'] ?></td></tr>
        
      </table>
    <?php endif; ?>
  </div>
  <script 
          type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(9)");
		if (active) active.classList.add("active");
	</script>

  
<script>
  
    
    function previewLogo(input, logoId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(logoId).src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

</body>
</html>
